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
        * API Name : 후기 등록 API
        * 마지막 수정 날짜 : 21.01.09
        */
        case "createReview":
            http_response_code(200);

            $orderIdx = $vars["orderIdx"];
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $reviewContent = isset($req->reviewContent) ? $req->reviewContent : null;
            $rate = isset($req->rate) ? $req->rate : null;
            $imageUrl = isset($req->imageUrl) ? $req->imageUrl : null;

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

            if(is_null($orderIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "orderIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidOrderIdx($orderIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "유효하지 않은 orderIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(is_null($reviewContent)){
                $res->isSuccess = FALSE;
                $res->code = 2005;
                $res->message = "reviewContent가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(is_null($rate)){
                $res->isSuccess = FALSE;
                $res->code = 2006;
                $res->message = "rate가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($rate != 0 and $rate != 0.5 and $rate != 1 and $rate != 1.5 and $rate != 2 and $rate != 2.5 and
                $rate != 3 and $rate != 3.5 and $rate != 4 and $rate != 4.5 and $rate != 5){
                $res->isSuccess = FALSE;
                $res->code = 2007;
                $res->message = "잘못된 형식의 rate입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isDelivered($userIdx, $orderIdx) or doesReviewExist($orderIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "후기 등록 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            createReview($userIdx, $orderIdx, $rate, $reviewContent, $imageUrl);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "후기 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 4
        * API Name : 후기 수정 API
        * 마지막 수정 날짜 : 21.01.10
        */
        case "editReview":
            http_response_code(200);

            $reviewIdx = $vars["reviewIdx"];
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $reviewContent = isset($req->reviewContent) ? $req->reviewContent : null;
            $rate = isset($req->rate) ? $req->rate : null;
            $imageUrl = isset($req->imageUrl) ? $req->imageUrl : null;

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

            if(is_null($reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "reviewIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidReviewIdx($reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "유효하지 않은 reviewIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(is_null($reviewContent)){
                $res->isSuccess = FALSE;
                $res->code = 2005;
                $res->message = "reviewContent가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(is_null($rate)){
                $res->isSuccess = FALSE;
                $res->code = 2006;
                $res->message = "rate가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($rate != 0 and $rate != 0.5 and $rate != 1 and $rate != 1.5 and $rate != 2 and $rate != 2.5 and
                $rate != 3 and $rate != 3.5 and $rate != 4 and $rate != 4.5 and $rate != 5){
                $res->isSuccess = FALSE;
                $res->code = 2007;
                $res->message = "잘못된 형식의 rate입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isReviewWrittenByMe($userIdx, $reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2008;
                $res->message = "후기 수정 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(hasReviewEverEdited($reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2009;
                $res->message = "더이상 수정이 불가한 후기입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            editReview($rate, $reviewContent, $imageUrl, $reviewIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "후기 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 4
        * API Name : 후기 삭제 API
        * 마지막 수정 날짜 : 21.01.10
        */
        case "deleteReview":
            http_response_code(200);

            $reviewIdx = $vars["reviewIdx"];
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

            if(is_null($reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "reviewIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidReviewIdx($reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "유효하지 않은 reviewIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isReviewWrittenByMe($userIdx, $reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2005;
                $res->message = "후기 삭제 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            deleteReview($reviewIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "후기 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
        * API No. 4
        * API Name : 작품 후기 목록 조회 API
        * 마지막 수정 날짜 : 21.01.11
        */
        case "getReviews":
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

            $res->result = getReviews($productIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "작품 후기 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
        * API No. 4
        * API Name : 후기 댓글 등록 API
        * 마지막 수정 날짜 : 21.01.13
        */
        case "createReviewComment":
            http_response_code(200);

            $reviewIdx = $vars["reviewIdx"];
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $commentContent = isset($req->commentContent) ? $req->commentContent : null;

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

            if(is_null($reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "reviewIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidReviewIdx($reviewIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "유효하지 않은 reviewIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(is_null($commentContent)){
                $res->isSuccess = FALSE;
                $res->code = 2005;
                $res->message = "commentContent가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            createReviewComment($reviewIdx, $userIdx, $commentContent);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "후기 댓글 등록 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
        * API No. 4
        * API Name : 후기 댓글 삭제 API
        * 마지막 수정 날짜 : 21.01.13
        */
        case "deleteReviewComment":
            http_response_code(200);

            $commentIdx = $vars["commentIdx"];
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $commentContent = isset($req->commentContent) ? $req->commentContent : null;

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

            if(is_null($commentIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2003;
                $res->message = "commentIdx가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidReviewCommentIdx($commentIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2004;
                $res->message = "유효하지 않은 reviewIdx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isCommentWrittenByMe($commentIdx, $userIdx)){
                $res->isSuccess = FALSE;
                $res->code = 2005;
                $res->message = "삭제 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            deleteReviewComment($commentIdx);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "후기 댓글 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
