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
        * API Name : 좋아하는 작가 등록 / 해제 API
        * 마지막 수정 날짜 : 21.01.09
        */
        case "likeSeller":
            http_response_code(200);

            $sellerIdx = $vars["sellerIdx"];
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

            if(is_null($sellerIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "sellerIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidSellerIdx($sellerIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "유효하지 않은 sellerIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(hasEverLikedByMe($userIdx, $sellerIdx)){
                if(isLikedByMe($userIdx, $sellerIdx)){
                    unlikeSeller($userIdx, $sellerIdx);
                    $res->isSuccess = True;
                    $res->code = 1001;
                    $res->message = "좋아하는 작가 해제 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }
                else {
                    likeSellerAgain($userIdx, $sellerIdx);
                    $res->isSuccess = True;
                    $res->code = 1000;
                    $res->message = "좋아하는 작가 등록 성공";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;

                }
            }
            else {
                likeSeller($userIdx, $sellerIdx);
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "좋아하는 작가 등록 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            }

        /*
        * API No. 4
        * API Name : 인기 작가 목록 조회 API
        * 마지막 수정 날짜 : 21.01.12
        */
        case "getTopSellers":
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

            $res->result = getTopSellers($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "인기 작가 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 4
        * API Name : 작가 프로필 조회 API
        * 마지막 수정 날짜 : 21.01.12
        */
        case "getSellerInfo":
            http_response_code(200);
            $sellerIdx = $vars['sellerIdx'];

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

            if(is_null($sellerIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "sellerIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidSellerIdx($sellerIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "유효하지 않은 sellerIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            
            $res->result = getSellerInfo($userIdx,$sellerIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "작가 프로필 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
