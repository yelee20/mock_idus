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

//READ 인기 작가 목록 조회
function getTopSellers($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select S.sellerIdx, sellerName, profileImageUrl as sellerProfileImageUrl,
       backgroundImageUrl as sellerBackgroundImageUrl, bio, 
       case when FS.userIdx is null then 0 else 1 end as isLikedByMe
from Seller as S
inner join (select Seller.sellerIdx, sum(orderNum) as orderNum, reviewNum, avgRate
from Seller
inner join (select P.productIdx, sellerIdx, count(orderIdx) as orderNum
from Product as P
inner join (select orderIdx, productIdx from OrderLog) O on O.productIdx = P.productIdx
group by productIdx) O on O.sellerIdx = Seller.sellerIdx
inner join (select O.productIdx, count(reviewIdx) as reviewNum, avg(rate) as avgRate
from Review as R
inner join (select orderIdx, productIdx from OrderLog) O on O.orderIdx = R.orderIdx
group by O.productIdx) R on R.productIdx = O.productIdx
group by sellerIdx) T on S.sellerIdx = T.sellerIdx
left join (select sellerIdx, userIdx from FavoriteSeller where userIdx = ? and status = 'N')
           FS on FS.sellerIdx = S.sellerIdx
order by (T.orderNum+T.reviewNum)*T.avgRate desc
limit 5";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}