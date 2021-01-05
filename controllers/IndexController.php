<?php
require 'function.php';
use Facebook\Facebook;

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
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
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getUsers":
            http_response_code(200);

            $res->result = getUsers();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 5
         * API Name : 사용자 정보 수정 API
         * 마지막 수정 날짜 : 21.01.06
         */
        case "updateUserInfo":
            http_response_code(200);
            $userName = isset($req->userName) ? $req->userName : null;
            $profileImageUrl = isset($req->profileImageUrl) ? $req->profileImageUrl : null;
            $mobileNo = isset($req->mobileNo) ? $req->mobileNo : null;
            $email = isset($req->email) ? $req->email : null;
            $gender = isset($req->gender) ? $req->gender : null;
            $birthday = isset($req->birthday) ? $req->birthday : null;
            $userIdx = $vars["userIdx"];

            // 사용자 인덱스 Validation
            if(is_null($userIdx)) {
                $res->isSuccess = False;
                $res->code = 2001;
                $res->message = "userIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if (!isValidUserIdx($userIdx)){
                $res->isSuccess = False;
                $res->code = 2002;
                $res->message = "유효하지 않은 userIdx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            // 사용자 이름 Validation
            if(is_null($userName)) {
                $res->isSuccess = False;
                $res->code = 2003;
                $res->message = "userName이 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(strlen($userName) < 2) {
                $res->isSuccess = False;
                $res->code = 2004;
                $res->message = "userName은 최소 2글자 이상이어야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            // 전화번호 Validation
            if(!is_null($mobileNo) and !preg_match("/^01([016789]?)-?([0-9]{3,4})-?([0-9]{4})$/",$mobileNo)) {
                $res->isSuccess = False;
                $res->code = 2005;
                $res->message = "잘못된 형식의 mobileNo입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if (!is_null($mobileNo) and isDuplicateMobileNo($userIdx, $mobileNo)){
                $res->isSuccess = False;
                $res->code = 2006;
                $res->message = "중복된 mobileNo입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            // 이메일 Validation
            if(is_null($email)) {
                $res->isSuccess = False;
                $res->code = 2007;
                $res->message = "email이 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!preg_match("/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/",$email)) {
                $res->isSuccess = False;
                $res->code = 2008;
                $res->message = "잘못된 형식의 email입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if (isDuplicateEmail($userIdx, $email)){
                $res->isSuccess = False;
                $res->code = 2009;
                $res->message = "중복된 email입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            // 생일 Validation
            if (!is_null($birthday)){
            $today = (date('Y-m-d'));
            $dateDifference = strtotime($today) - strtotime($birthday);
            $years  = floor($dateDifference / (365 * 60 * 60 * 24));

            if($years < 14){
                $res->isSuccess = False;
                $res->code = 2010;
                $res->message = "만 14세 미만은 계정을 가질 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!(preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/',$birthday,$match) && checkdate($match[2],$match[3],$match[1])))
            {
                $res->isSuccess = False;
                $res->code = 2011;
                $res->message = "잘못된 형식의 birthday입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            }

            // 성별 Validation

            if ($gender != null and $gender != 'F' and $gender != 'M') {
                $res->isSuccess = False;
                $res->code = 2012;
                $res->message = "잘못된 형식의 gender입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            updateUserInfo($userName, $profileImageUrl, $mobileNo, $email, $gender, $birthday, $userIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "사용자 정보 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 6
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "createUser":
            http_response_code(200);

            // 사용자로부터 Facebook 로그인 이후 Access Token을 받습니다.
//            $access_token = $_SERVER["ACCESS_TOKEN"];
            $access_token = $req -> access_token;
//            $email = $req->email;
//            $mobileNo = $req->mobileNo;
            if ($access_token == "" or is_null($access_token)){
                $res->isSuccess = FALSE;
                $res->code = 2000;
                $res->message = "access-token이 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            require_once __DIR__. '/../vendor/facebook/graph-sdk/src/Facebook/autoload.php';
            $fb = new Facebook\Facebook([
                'app_id' => '{app-id}',
                'app_secret' => '{app-secret}',
                'default_graph_version' => 'v2.10',
            ]);

            try {
                // Returns a `Facebook\Response` object
                $response = $fb->get('/me?fields=id,name', $access_token);
            } catch(Facebook\Exception\ResponseException $e) {
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(Facebook\Exception\SDKException $e) {
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }

            $user = $response->getGraphUser();

            echo 'Name: ' . $user['name'];

//            if (isValidEmail($email)){
//                $res->isSuccess = FALSE;
//                $res->code = 2000;
//                $res->message = "이미 존재하는 email입니다";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }
//
//            if (isValidMobileNo($mobileNo)){
//                $res->isSuccess = FALSE;
//                $res->code = 2000;
//                $res->message = "이미 존재하는 mobileNo입니다";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                break;
//            }


            #$res->result = createUser($email);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

//        case "createUser":
//            http_response_code(200);
//
//            // Packet의 Body에서 데이터를 파싱합니다.
//            $userID = $req->userID;
//            $pwd_hash = password_hash($req->pwd, PASSWORD_DEFAULT); // Password Hash
//            $name = $req->name;
//
//            $res->result = createUser($userID, $pwd_hash, $name);
//            $res->isSuccess = TRUE;
//            $res->code = 100;
//            $res->message = "테스트 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;



            case "createTest":
            http_response_code(200);

            $userName = $req->userName;
            $pwd = $req->pwd;
            $res->result = createTest($userName,$pwd);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
