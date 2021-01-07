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