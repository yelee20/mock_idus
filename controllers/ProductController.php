<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;


        /*
        * API No. 4
        * API Name : 홈 화면 조회 API
        * 마지막 수정 날짜 : 21.01.04
        */
        case "getHome":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            // JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;
            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지 않은 userIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getHome($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "홈 화면 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 4
        * API Name : 작품 상세 페이지 조회 API
        * 마지막 수정 날짜 : 21.01.05
        */
        case "getProductDetail":
            http_response_code(200);

            $productIdx = $vars["productIdx"];
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            // JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;

            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지 않은 userIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidProductIdx($productIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "유효하지 않은 productIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $pdo = pdoSqlConnect();
            $pdo->beginTransaction();

            try {
                $productDetail = getProductDetail($userIdx,$productIdx);
                $productDetail['productImageUrl'] = getProductImageUrl($productIdx);
                $res -> result = $productDetail;
                createViewLog($userIdx,$productIdx);
            } catch(\Exception $e) {
                $pdo->rollBack();
                return getSQLErrorException($e);
            }

            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "작품 상세페이지 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 4
        * API Name : 작품 옵션 조회 API
        * 마지막 수정 날짜 : 21.01.05
        */
        case "getOption":
            http_response_code(200);

            $productIdx = $vars["productIdx"];
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            // JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;

            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지 않은 userIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidProductIdx($productIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "유효하지 않은 productIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            $option = getOption($productIdx);
            if(sizeof($option) == 0){
                $res->isSuccess = TRUE;
                $res->code = 1001;
                $res->message = "작품 옵션이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $optionDetail = getOptionDetail($productIdx);

            $tmp = [];
            $y = 0;
            $z = getNumOfOptions($productIdx,1)['numOfOptions'];
            for($x = 0; $x < sizeof($option); $x++) {
                for ($y = $y ;$y < $z; $y++) {
                    array_push($tmp, $optionDetail[$y]);
                    $option[$x]['option'] = $tmp;
                }
                if ($y == sizeof($optionDetail))
                    break;
                $z += getNumOfOptions($productIdx,$x+2)['numOfOptions'];
                $tmp = [];
            }

            $res->result = $option;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "작품 옵션 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 4
        * API Name : 작품 즐겨찾기 등록 / 해제 API
        * 마지막 수정 날짜 : 21.01.09
        */
        case "starProduct":
            http_response_code(200);

            $productIdx = $vars["productIdx"];
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            // JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;

            if(is_null($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "userIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "유효하지 않은 userIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(is_null($productIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "productIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidProductIdx($productIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "유효하지 않은 productIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(hasEverStarredByMe($userIdx, $productIdx)){
                if(isStarredByMe($userIdx, $productIdx)){
                    unstarProduct($userIdx, $productIdx);
                    $res->isSuccess = True;
                    $res->code = 1001;
                    $res->message = "작품 즐겨찾기 해제 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }
                else {
                    starProductAgain($userIdx, $productIdx);
                    $res->isSuccess = True;
                    $res->code = 1000;
                    $res->message = "작품 즐겨찾기 등록 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }
            }
            else {
                starProduct($userIdx, $productIdx);
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "작품 즐겨찾기 등록 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }

        /*
        * API No. 4
        * API Name : 작품 옵션 조회 API
        * 마지막 수정 날짜 : 21.01.05
        */
        case "getLatestReview":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            // JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;

            if(is_null($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "userIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "유효하지 않은 userIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            getLatestReviewContent($userIdx);

            $getLatestReview = getLatestReview($userIdx);
            for ($x=0; $x < sizeof($getLatestReview);$x++){
                $productIdx = $getLatestReview[$x]['productIdx'];
                $getLatestReview[$x]['review']=getLatestReviewContent($productIdx);
                }

            $res->result = $getLatestReview;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "실시간 후기 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 4
        * API Name : 실시간 구매 목록 조회 API
        * 마지막 수정 날짜 : 21.01.12
        */
        case "getLatestOrder":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            // JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;

            if(is_null($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "userIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "유효하지 않은 userIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getLatestOrder($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "실시간 구매 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 4
        * API Name : 인기 작품 목록 조회 API
        * 마지막 수정 날짜 : 21.01.12
        */
        case "getTopProducts":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            // JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;

            if(is_null($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "userIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "유효하지 않은 userIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $category = getCategory();

            for ($x=0; $x<sizeof($category);$x++){
                $category[$x]['productInfo']=getTopProducts($userIdx,$category[$x]['category']);
            }

            $res->result = $category;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "인기 작품 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 4
        * API Name : 최신 작품 목록 조회 API
        * 마지막 수정 날짜 : 21.01.12
        */
        case "getLatestProduct":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            // JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;

            if(is_null($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "userIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "유효하지 않은 userIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getLatestProduct($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "최신 작품 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 4
        * API Name : 사용자별 추천 작품 목록 조회 API
        * 마지막 수정 날짜 : 21.01.12
        */
        case "getRecommendation":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            // JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;

            if(is_null($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2001;
                $res->message = "userIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidUserIdx($userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2002;
                $res->message = "유효하지 않은 userIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getRecommendation($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "사용자별 추천 작품 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
