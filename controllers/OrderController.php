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
        * API Name : 배송지 정보 수정 API
        * 마지막 수정 날짜 : 20.01.07
        */
        case "updateAddressInfo":
            http_response_code(200);
            $addressIdx = $vars['addressIdx'];
            $receiverName = isset($req->receiverName) ? $req->receiverName : null;
            $mobileNo = isset($req->mobileNo) ? $req->mobileNo : null;
            $address = isset($req->address) ? $req->address : null;

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

            // 사용자 인덱스 Validation
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

            // 주소 인덱스 Validation
            if(is_null($addressIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "addressIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($addressIdx > 3){
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "유효하지 않은 addressIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            // 받는분 이름 Validation
            if(is_null($receiverName)){
                $res->isSuccess = FALSE;
                $res->code = 2005;
                $res->message = "receiverName가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            // 전화번호 Validation
            if(is_null($mobileNo)){
                $res->isSuccess = FALSE;
                $res->code = 2006;
                $res->message = "mobileNo가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!preg_match("/^01([016789]?)-?([0-9]{3,4})-?([0-9]{4})$/",$mobileNo)) {
                $res->isSuccess = False;
                $res->code = 2007;
                $res->message = "잘못된 형식의 mobileNo입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            // 주소 Validation
            if(is_null($address)){
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "address가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidAddressIdx($userIdx,$addressIdx)){
                createAddressInfo($userIdx, $addressIdx, $receiverName, $mobileNo, $address);
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "배송지 정보 수정 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            updateAddressInfo($receiverName, $mobileNo, $address, $userIdx, $addressIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "배송지 정보 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}