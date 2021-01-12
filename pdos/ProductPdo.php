<?php

//READ 홈 화면 조회
function getHome($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select P.productIdx, P.productName, productImageUrl, S.sellerIdx, S.sellerName, S.profileImageUrl as sellerProfileImageUrl,
       reviewerIdx, reviewerName, reviewcontent, isReviewImageAttached, rate,
       case when Star.userIdx is null then 0 else 1 end as isStarredByMe
from Product as P
inner join (select sellerIdx, sellerName, profileImageUrl from Seller) S on P.sellerIdx = S.sellerIdx
left join (select P.productIdx, group_concat(productImageUrl) as productImageUrl
from Product as P
left join (SELECT * FROM ( SELECT productIdx, productImageUrl , ROW_NUMBER()
    OVER(PARTITION BY productIdx ORDER BY createdAt DESC) ITEM_RN FROM ProductImage ) TEST WHERE ITEM_RN = 1
) PI on PI.productIdx = P.productIdx
group by P.productIdx) PI on PI.productIdx = P.productIdx
inner join (SELECT productIdx, reviewIdx, reviewerIdx, reviewerName, reviewerProfileImageUrl, reviewcontent, isReviewImageAttached, rate

FROM (

SELECT reviewIdx, Product.productIdx
     , reviewerIdx, reviewerName, reviewerProfileImageUrl, reviewcontent, isReviewImageAttached, rate

, ROW_NUMBER() OVER(PARTITION BY Product.productIdx ORDER BY reviewIdx DESC) as RowIdx

From Product
left join(
select reviewIdx as reviewIdx, productIdx, U.userIdx as reviewerIdx, userName as reviewerName,
       profileImageUrl as reviewerProfileImageUrl, content as reviewcontent,
        case when imageUrl is null then 0 else 1 end as isReviewImageAttached, rate
from Review
inner join (select userIdx, userName, profileImageUrl from UserInfo) U on U.userIdx = Review.userIdx
order by reviewIdx desc) R on R.productIdx = Product.productIdx

) AS t

WHERE RowIdx = 1) R on P.productIdx = R.productIdx
left join (select productIdx, userIdx from StarredProduct where userIdx = ? and status = 'N') Star on P.productIdx = Star.productIdx
where P.status = 'N' 
order by productIdx desc
";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 작품 상세 페이지 조회
function getProductDetail($userIdx,$productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select P.productIdx, productName, P.sellerIdx, S.sellerName, S.sellerProfileImageUrl,
       price as originalPrice, discount, format(price*(100-discount)/100,0) as finalPrice, deliveryFee,
       freeDeliveryCondition, startDeliveryAfter, quantity, productInfo, category, productionPolicy, refundPolicy, format(price*(100-discount)/10000,0) as points,
       ifnull(rate,0) as rate, ifnull(reviewNum,0) as reviewNum, ifnull(viewNum,0) as viewNum, ifnull(orderNum,0) as orderNum, ifnull(starredNum,0) as starredNum,
       ifnull(Star.userIdx,0) as isStarredByMe

from Product as P
inner join (select sellerIdx, sellerName, profileImageUrl as sellerProfileImageUrl
            from Seller) S on S.sellerIdx = P.sellerIdx
left join (select productIdx, userIdx from StarredProduct where userIdx = ? and status = 'N')
           Star on P.productIdx = Star.productIdx
left join (select productIdx, count(userIdx) as starredNum from StarredProduct
            where status = 'N' group by productIdx) SN on SN.productIdx = P.productIdx
left join (select productIdx, count(userIdx) as orderNum from OrderLog
            where status = 'N' and (isRefunded = 'N' or isRefunded = 'P')
              and (isChanged = 'N' or isRefunded = 'P') group by productIdx) O on O.productIdx = P.productIdx
left join (select productIdx, avg(rate) as rate, count(reviewIdx) as reviewNum from Review
            where status = 'N' group by productIdx) R on R.productIdx = P.productIdx
left join (select productIdx, count(userIdx) as viewNum from ViewLog
            group by productIdx) V on V.productIdx = P.productIdx
where O.productIdx = ?";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// UPDATE 작품 수량 수정
function updateProductQuantity($quantity, $productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE Product
                        SET quantity = ?,
                            updatedAt = CURRENT_TIMESTAMP
                        WHERE productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$quantity, $productIdx]);
    $st = null;
    $pdo = null;
}

//READ
function getProductImageUrl($productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select productImageUrl as imageUrl from ProductImage where productIdx = ?";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getOptionDetail($productIdx){
    $pdo = pdoSqlConnect();
    $query = "select Distinct detailedOptionIdx, optionDetail, price
from ProductOption
where productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


// GET 작품 옵션 조회
function getOption($productIdx){
    $pdo = pdoSqlConnect();
    $query = "select DISTINCT optionIdx, optionName
from ProductOption
where productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// GET 작품 옵션 개수
function getNumOfOption($productIdx){
    $pdo = pdoSqlConnect();
    $query = "select count(productIdx) as numOfOptions
from (select distinct productIdx, optionIdx
from ProductOption
where productIdx = ?) C
group by productIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// GET 작품 옵션 선택지 개수
function getNumOfOptions($productIdx, $optionIdx){
    $pdo = pdoSqlConnect();
    $query = "select count(detailedOptionIdx) as numOfOptions
from ProductOption
where productIdx = ? and optionIdx = ?
group by optionIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx, $optionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// CREATE 구경 기록 추가
function createViewLog($userIdx,$productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into ViewLog (userIdx, productIdx) values (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$productIdx]);

    $st = null;
    $pdo = null;

}

// CREATE 작품 즐겨찾기 등록
function starProduct($userIdx,$productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into StarredProduct (userIdx, productIdx) values (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$productIdx]);

    $st = null;
    $pdo = null;

}

// DELETE 작품 즐겨찾기 등록 해제
function unstarProduct($userIdx, $productIdx){
    $pdo = pdoSqlConnect();
    $query = "UPDATE StarredProduct SET status = 'D',
            updatedAt = current_timestamp
            where userIdx = ? and productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$productIdx]);
    $st = null;
    $pdo = null;
}

// 작품 즐겨찾기 되어있는지 확인
function isStarredByMe($userIdx, $productIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from StarredProduct where userIdx = ? and 
                productIdx = ? and status != 'D') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// 한번이라도 작품 즐겨찾기 한 기록이 있는지 확인
function hasEverStarredByMe($userIdx, $postIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from StarredProduct where userIdx = ? and 
                productIdx = ?) as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$postIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// 다시 작품 즐겨찾기 등록
function starProductAgain($userIdx, $productIdx){
    $pdo = pdoSqlConnect();
    $query = "UPDATE StarredProduct SET status = 'N',
              updatedAt = current_timestamp 
              where userIdx = ? and productIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$productIdx]);
    $st = null;
    $pdo = null;
}


//READ 실시간 후기 작품 목록
function getLatestReview($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select Product.productIdx,productImageUrl, productName, S.sellerIdx, S.sellerName,
       ifnull(Star.userIdx,0) as isStarredByMe
from Product
left join (select P.productIdx, group_concat(productImageUrl) as productImageUrl
from Product as P
left join (SELECT * FROM ( SELECT productIdx, productImageUrl , ROW_NUMBER()
    OVER(PARTITION BY productIdx ORDER BY createdAt DESC) ITEM_RN FROM ProductImage ) TEST WHERE ITEM_RN = 1
) PI on PI.productIdx = P.productIdx
group by P.productIdx) PI on PI.productIdx = Product.productIdx
inner join(select sellerIdx, sellerName from Seller) S on S.sellerIdx = Product.productIdx
left join (select productIdx, userIdx from StarredProduct where userIdx = ? and status = 'N')
           Star on Product.productIdx = Star.productIdx
inner join (select reviewIdx, R.orderIdx, OL.productIdx
from Review as R
inner join (select productIdx, orderIdx from OrderLog) OL on OL.orderIdx = R.orderIdx)
    R on R.productIdx = Product.productIdx
group by productIdx
order by max(reviewIdx) desc;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//READ 실시간 후기 목록
function getLatestReviewContent($productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT T.reviewIdx, T.userIdx as reviewerIdx, U.userName as reviewerName,
       T.content as ReviewContent,
       case when T.imageUrl is null then 0 else 1 end as isReviewImageAttached,rate FROM (

   SELECT O.productIdx, R.reviewIdx, R.userIdx, R.content ,R.imageUrl, R.rate,
          RANK() OVER (PARTITION BY O.productIdx ORDER BY R.reviewIdx DESC) AS RN
   FROM Review AS R
   inner join (select orderIdx, productIdx from OrderLog) O on O.orderIdx = R.orderIdx
    where O.productIdx = ?
) AS T
inner join (select userIdx, userName from UserInfo) U on U.userIdx = T.userIdx
WHERE T.RN <= 3;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// GET 실시간 구매 목록 조회
function getLatestOrder($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select OrderLog.productIdx, productImageUrl, productName, sellerIdx, sellerName, reviewIdx, reviewerIdx, reviewerName, reviewContent,
       case when imageUrl is null then 0 else 1 end as isReviewImageAttached, rate,
       case when Star.userIdx is null then 0 else 1 end as isStarredByMe
from OrderLog
left join (select R2.productIdx, R2.reviewIdx, R1.userIdx as reviewerIdx, userName as reviewerName,
            content as reviewContent, imageUrl, rate
from Review as R1
inner join (select O.productIdx, max(reviewIdx) as reviewIdx
from Review
inner join (select orderIdx, productIdx from OrderLog) O on O.orderIdx = Review.orderIdx
group by O.productIdx) R2 on R1.reviewIdx = R2.reviewIdx
inner join (select userIdx, userName from UserInfo) U on U.userIdx = R1.userIdx) F on F.productIdx = OrderLog.productIdx
left join (SELECT * FROM ( SELECT productIdx, productImageUrl , ROW_NUMBER()
    OVER(PARTITION BY productIdx ORDER BY createdAt DESC) ITEM_RN FROM ProductImage ) TEST WHERE ITEM_RN = 1
) PI on PI.productIdx = OrderLog.productIdx
inner join (select productIdx, productName,
                   Seller.sellerIdx, Seller.sellerName from Product, Seller
    where Seller.sellerIdx = Product.sellerIdx) S on S.productIdx = OrderLog.productIdx
left join (select productIdx, userIdx from StarredProduct where userIdx = ? and status = 'N') Star on OrderLog.productIdx = Star.productIdx
group by productIdx
order by max(orderIdx) desc
;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// GET 인기 작품 목록 조회
function getTopProducts($userIdx,$category){
    $pdo = pdoSqlConnect();
    $query = "select P.productIdx, productImageUrl, productName, P.sellerIdx, sellerName, R.reviewIdx, R2.reviewerIdx,
       reviewerName, content as reviewContent,
       case when imageUrl is null then 0 else 1 end as isReviewImageAttached, rate,
        case when Star.userIdx is null then 0 else 1 end as isStarredByMe
from Product as P
inner join (select P.productIdx, count(orderIdx) as orderNum
from Product as P
inner join (select orderIdx, productIdx from OrderLog) O on O.productIdx = P.productIdx
group by productIdx) PA on PA.productIdx = P.productIdx
inner join (select O.productIdx, count(reviewIdx) as reviewNum, avg(rate) as avgRate
from Review as R
inner join (select orderIdx, productIdx from OrderLog) O on O.orderIdx = R.orderIdx
group by O.productIdx) RA on RA.productIdx = P.productIdx

left join (SELECT * FROM ( SELECT productIdx, productImageUrl , ROW_NUMBER()
    OVER(PARTITION BY productIdx ORDER BY createdAt DESC) ITEM_RN FROM ProductImage ) TEST WHERE ITEM_RN = 1
) PI on PI.productIdx = P.productIdx
inner join (select sellerIdx, sellerName from Seller) S on S.sellerIdx = P.sellerIdx
inner join (select O.productIdx, max(reviewIdx) as reviewIdx
from Review
inner join (select orderIdx, productIdx from OrderLog) O on O.orderIdx = Review.orderIdx
group by O.productIdx) R on R.productIdx = P.productIdx
inner join (select reviewIdx, userIdx as reviewerIdx, content, rate, imageUrl from Review) R2 on
R2.reviewIdx = R.reviewIdx
inner join (select userIdx, userName as reviewerName from UserInfo) U on U.userIdx = R2.reviewerIdx
left join (select productIdx, userIdx from StarredProduct where userIdx = ? and status = 'N')
           Star on P.productIdx = Star.productIdx
where category = ?
group by P.productIdx
order by (PA.orderNum+RA.reviewNum)*RA.avgRate desc;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$category]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


// GET 카테고리 목록 조회
function getCategory(){
    $pdo = pdoSqlConnect();
    $query = "select distinct category from Product;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}