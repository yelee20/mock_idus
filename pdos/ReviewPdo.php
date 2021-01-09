<?php

// 내가 주문한게 맞는지 확인
function isOrderedByMe($userIdx, $orderIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from OrderLog where userIdx = ? and 
                orderIdx = ? and deliveryStatus = 2 and isRefunded = 'N' 
                and isChanged = 'N' and status = 'N') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $orderIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// 후기 작성한 이력이 있는지 확인
function doesReviewExist($orderIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from Review where orderIdx = ?) as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$orderIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// CREATE 후기 등록
function createReview($userIdx, $orderIdx, $rate, $content, $imageUrl)
{
    $pdo = pdoSqlConnect();
    $query = "insert into Review (userIdx, orderIdx, rate, content, imageUrl) 
                values (?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $orderIdx, $rate, $content, $imageUrl]);
    $st = null;
    $pdo = null;
}