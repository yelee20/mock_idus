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
        * API Name : 작품 검색 API
        * 마지막 수정 날짜 : 21.01.08
        */
        case "searchKeyword":
            http_response_code(200);
            $keyword = $_GET["keyword"];
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

            createSearchLog($userIdx, $keyword);
            $res->result = searchKeyword($userIdx, $keyword);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "작품 검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 4
        * API Name : 최근 검색어 및 실시간 인기검색어 조회 API
        * 마지막 수정 날짜 : 20.01.07
        */
        case "getLatestSearch":
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

            if(!searchedIn24Hrs($userIdx)){
                $res->result = getTopSearch();
                $res->isSuccess = TRUE;
                $res->code = 1000;
                $res->message = "실시간 인기 검색어 조회 성공";
                $result = json_encode($res, JSON_NUMERIC_CHECK);
                echo str_replace('ROW_NUMBER() OVER (ORDER BY cnt desc)','rank',$result);
                break;
            }

            $res->result = array(getLatestSearch($userIdx),getTopSearch());
            $res->isSuccess = TRUE;
            $res->code = 1001;
            $res->message = "최근 검색어 및 실시간 인기검색어 조회 성공";
            $result = json_encode($res, JSON_NUMERIC_CHECK);
            echo str_replace('ROW_NUMBER() OVER (ORDER BY cnt desc)','rank',$result);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}