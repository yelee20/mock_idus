<?php
// GET 작품 검색
function searchKeyword($userIdx, $keyword){
    $pdo = pdoSqlConnect();
    $query = "select P.productIdx, P.productName, PI.productImageUrl, S.sellerIdx, S.sellerName,
       R.rate, reviewNum, case when P.deliveryFee = 0 then 1 else 0 end as freeDelivery,
       P.price as originalPrice, P.discount, format(price*(100-discount)/100,0) as finalPrice,
       ifnull(T.createdAt,0) as isNew,
       reviewContent, case when Star.userIdx is null then 0 else 1 end as isStarredByMe
from Product as P
inner join(select sellerName, sellerIdx from Seller) S on S.sellerIdx = P.sellerIdx
left join (select P.productIdx, group_concat(productImageUrl) as productImageUrl
from Product as P
left join (SELECT * FROM ( SELECT productIdx, productImageUrl , ROW_NUMBER()
    OVER(PARTITION BY productIdx ORDER BY createdAt DESC) ITEM_RN FROM ProductImage ) TEST WHERE ITEM_RN = 1
) PI on PI.productIdx = P.productIdx
group by P.productIdx) PI on PI.productIdx = P.productIdx
left join (SELECT productIdx, (CASE WHEN createdAt is Null then '0'
                                  ELSE '1' End) as createdAt
FROM Product
WHERE HOUR(TIMEDIFF(createdAt, CURRENT_TIMESTAMP())) < 72) T on T.productIdx = P.productIdx
left join (select productIdx, avg(rate) as rate, count(reviewIdx) as reviewNum from Review
            where status = 'N' group by productIdx) R on R.productIdx = P.productIdx
left join (select productIdx, userIdx from StarredProduct where userIdx = $userIdx and status = 'N')
Star on P.productIdx = Star.productIdx
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

WHERE RowIdx = 1) RC on RC.productIdx = P.productIdx
where productName like concat('%','$keyword','%') or productInfo like concat('%','$keyword','%')
or sellerName like concat('%','$keyword','%');";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// CREATE 작품 검색
function createSearchLog($userIdx, $keyword)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO SearchLog (userIdx, keyword) VALUES (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $keyword]);
    $st = null;
    $pdo = null;
}

// GET 최근 검색어 조회
function getLatestSearch($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select group_concat(keyword) as latestKeyword
from SearchLog
where userIdx = ? and HOUR(TIMEDIFF(SearchLog.createdAt, CURRENT_TIMESTAMP())) < 24
group by userIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// GET 인기 검색어 조회
function getTopSearch(){
    $pdo = pdoSqlConnect();
    $query = "select ROW_NUMBER() OVER (ORDER BY cnt desc), keyword as topKeyword
from (select keyword, count(keyword) as cnt
from SearchLog
where HOUR(TIMEDIFF(SearchLog.createdAt, CURRENT_TIMESTAMP())) < 24
group by keyword) S;";

    $st = $pdo->prepare($query);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// GET 24시간이내 검색기록이 있는지 확인
function searchedIn24Hrs($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select userIdx
from SearchLog
where userIdx = ? and HOUR(TIMEDIFF(SearchLog.createdAt, CURRENT_TIMESTAMP())) < 24) as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

