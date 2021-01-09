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
            $productDetail = getProductDetail($userIdx,$productIdx);
            $productDetail['productImageUrl'] = getProductImageUrl($productIdx);

            $res->result = $productDetail;
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "작품 상세 페이지 조회 성공";
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
        
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
