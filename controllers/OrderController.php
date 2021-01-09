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

        /*
        * API No. 4
        * API Name : 작품 구매 API
        * 마지막 수정 날짜 : 20.01.07
        */
        case "createOrder":
            http_response_code(200);
            $productIdx = $vars['productIdx'];
            $addressIdx = isset($req->addressIdx) ? $req->addressIdx : null;
            $receiverName = isset($req->receiverName) ? $req->receiverName : null;
            $mobileNo = isset($req->mobileNo) ? $req->mobileNo : null;
            $address = isset($req->address) ? $req->address : null;
            $useTmpMobileNo = isset($req->useTmpMobileNo) ? $req->useTmpMobileNo : 0;
            $requestMessage = isset($req->requestMessage) ? $req->requestMessage : null;
            $quantity = isset($req->quantity) ? $req->quantity : 1;
            $paymentMethod = isset($req->paymentMethod) ? $req->paymentMethod : 0;
            $supportSeller = isset($req->supportSeller) ? $req->supportSeller : 0;
            $optionIdx = isset($req->optionIdx) ? $req->optionIdx : null;
            $detailedOptionIdx = isset($req->detailedOptionIdx) ? $req->detailedOptionIdx : null;

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

            // 결제 수단 Validation
            if(!is_int($paymentMethod) or 0 > $paymentMethod or $paymentMethod > 6){
                $res->isSuccess = FALSE;
                $res->code = 2009;
                $res->message = "유효하지 않은 paymentMethod입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            // 1회용 안심번호 사용 여부 Validation
            if(!is_int($useTmpMobileNo) or ($useTmpMobileNo!=1 and $useTmpMobileNo!=0)){
                $res->isSuccess = FALSE;
                $res->code = 2010;
                $res->message = "잘못된 형식의 useTmpMobileNo입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            // 수량 Validation
            if($quantity<-1 and !is_int($quantity)){
                $res->isSuccess = FALSE;
                $res->code = 2011;
                $res->message = "잘못된 형식의 quantity입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            // 후원 여부 Validation
            if(!is_int($useTmpMobileNo) or ($supportSeller!=1 and $supportSeller!=0)){
                $res->isSuccess = FALSE;
                $res->code = 2012;
                $res->message = "잘못된 형식의 supportSeller입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $productInfo = getProductInfoByProductIdx($productIdx);
            $price = $productInfo[0]['price'];
            $discount = $productInfo[0]['discount'];
            $deliveryFee = $productInfo[0]['deliveryFee'];
            $freeDeliveryCondition = $productInfo[0]['freeDeliveryCondition'];
            $availableQuantity = $productInfo[0]['quantity'];

            $finalPrice = $price*$quantity*(100-$discount)/100;
            $finalOption = '';
            // 옵션 Validation
            if(is_null($optionIdx)) {
                if (getNumOfOption($productIdx)!=[]) {
                    $res->isSuccess = FALSE;
                    $res->code = 2015;
                    $res->message = "선택된 option의 개수가 잘못되었습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            else {
                if (getNumOfOption($productIdx) == [] or sizeof($optionIdx) != getNumOfOption($productIdx)[0]['numOfOptions']){
                    $res->isSuccess = FALSE;
                    $res->code = 2015;
                    $res->message = "선택된 option의 개수가 잘못되었습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                if (sizeof($optionIdx)!=sizeof($detailedOptionIdx)){
                    $res->isSuccess = FALSE;
                    $res->code = 2016;
                    $res->message = "optionIdx와 detailedOptionIdx의 개수가 다릅니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                for ($x=0; $x < sizeof($optionIdx); $x++){
                    if (!isValidOptionIdx($productIdx,$optionIdx[$x])){
                        $res->isSuccess = FALSE;
                        $res->code = 2017;
                        $res->message = "유효하지 않은 optionIdx입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break 2;
                    }

                    if (!isValidDetailedOptionIdx($productIdx,$optionIdx[$x],$detailedOptionIdx[$x])){
                        $res->isSuccess = FALSE;
                        $res->code = 2018;
                        $res->message = "유효하지 않은 detailedOptionIdx입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break 2;
                    }

                    $optionInfo = getOptionInfoByIdx($productIdx, $optionIdx[$x], $detailedOptionIdx[$x])[0];
                    $finalOption = $finalOption.$optionInfo['optionIdx'].'. '.$optionInfo['optionName'].': '.$optionInfo['optionDetail'].'/ ';

                    if ($optionInfo['price']!=0){
                        $finalPrice += $optionInfo['price'];
                    }

                }
            }

            // 배송비 무료 조건 확인
            if ($finalPrice < $freeDeliveryCondition and !isVIPUser($userIdx)){
                $finalPrice += $deliveryFee;
            }

            // 작가 후원
            if ($supportSeller == 1){
                $finalPrice += 1000;
            }

            // 수량 확인
            if ($availableQuantity == 0){
                $res->isSuccess = FALSE;
                $res->code = 2013;
                $res->message = "구매가 불가능한 작품입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if ($availableQuantity!= -1){
                if ($availableQuantity < $quantity){
                $res->isSuccess = FALSE;
                $res->code = 2014;
                $res->message = "구매 가능한 수량을 초과하였습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
                }
                $availableQuantity -= $quantity;
                updateProductQuantity($availableQuantity, $productIdx);
            }

            if(!isValidAddressIdx($userIdx,$addressIdx)){
                createAddressInfo($userIdx, $addressIdx, $receiverName, $mobileNo, $address);
            }
            else {
                updateAddressInfo($receiverName, $mobileNo, $address, $userIdx, $addressIdx);
            }

            if($useTmpMobileNo == 1){
                $mobileNo = $mobileNo*2;
            }

            createOrder($userIdx, $productIdx, $quantity, $receiverName, $mobileNo, $address, $requestMessage, $finalPrice, $finalOption);
            $res->isSuccess = TRUE;
            $res->code = 1000;
            $res->message = "작품 구매 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}