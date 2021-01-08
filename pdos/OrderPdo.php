<?php

function isValidAddressIdx($userIdx, $addressIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from Address where userIdx = ? and 
                addressIdx = ? and status = 'N') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$addressIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// CREATE 배송지 정보 추가
function createAddressInfo($userIdx, $addressIdx, $receiverName, $mobileNo, $address)
{
    $pdo = pdoSqlConnect();
    $query = "insert into Address (userIdx, addressIdx, receiverName, mobileNo, address) values (?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $addressIdx, $receiverName, $mobileNo, $address]);
    $st = null;
    $pdo = null;
}

// UPDATE 배송지 정보 수정
function updateAddressInfo($receiverName, $mobileNo, $address, $userIdx, $addressIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE Address
                        SET receiverName = ?,
                            mobileNo  = ?,
                            address = ?,
                            updatedAt = CURRENT_TIMESTAMP
                        WHERE userIdx = ? and addressIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$receiverName, $mobileNo, $address, $userIdx, $addressIdx]);
    $st = null;
    $pdo = null;
}

// GET 작품 정보 가져오기
function getProductInfoByProductIdx($productIdx){
    $pdo = pdoSqlConnect();
    $query = "select price, discount, deliveryFee, freeDeliveryCondition, quantity from Product where productIdx = ? and status = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// GET VIP 회원인지 확인
function isVIPUser($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists() as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// CREATE 작품 구매
function createOrder($userIdx, $productIdx, $quantity, $receiverName, $mobileNo, $address, $requestMessage, $finalPrice)
{
    $pdo = pdoSqlConnect();
    $query = "insert into OrderLog (userIdx, productIdx, quantity, receiverName, mobileNo, address, requestMessage, price) 
                values (?,?,?,?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $productIdx, $quantity, $receiverName, $mobileNo, $address, $requestMessage, $finalPrice]);
    $st = null;
    $pdo = null;
}



