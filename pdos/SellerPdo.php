<?php
function isValidSellerIdx($sellerIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from Seller where sellerIdx = ? and status = 'N') exist;";

    $st = $pdo->prepare($query);
    $st->execute([$sellerIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}


// CREATE 좋아하는 작가 등록
function likeSeller($userIdx,$sellerIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into FavoriteSeller (userIdx, sellerIdx) values (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$sellerIdx]);

    $st = null;
    $pdo = null;

}

// DELETE 좋아하는 작가 해제
function unlikeSeller($userIdx, $sellerIdx){
    $pdo = pdoSqlConnect();
    $query = "UPDATE FavoriteSeller SET status = 'D',
            updatedAt = current_timestamp
            where userIdx = ? and sellerIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$sellerIdx]);
    $st = null;
    $pdo = null;
}

// 좋아하는 작가로 설정 되어있는지 확인
function isLikedByMe($userIdx, $sellerIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from FavoriteSeller where userIdx = ? and 
                sellerIdx = ? and status != 'D') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$sellerIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// 한번이라도 작품 즐겨찾기 한 기록이 있는지 확인
function hasEverLikedByMe($userIdx, $sellerIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from FavoriteSeller where userIdx = ? and 
                sellerIdx = ?) as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$sellerIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// 다시 작품 즐겨찾기 등록
function likeSellerAgain($userIdx, $sellerIdx){
    $pdo = pdoSqlConnect();
    $query = "UPDATE FavoriteSeller SET status = 'N',
              updatedAt = current_timestamp 
              where userIdx = ? and sellerIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$sellerIdx]);
    $st = null;
    $pdo = null;
}