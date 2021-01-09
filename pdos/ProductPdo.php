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

//READ 홈 화면 조회
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

//// GET 작품 옵션 조회
//function getOptionDetail($productIdx){
//    $pdo = pdoSqlConnect();
//    $query = "select group_concat(optionDetail separator '/') as optionDetail
//from ProductOption
//where productIdx = ?
//group by productIdx, optionIdx;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$productIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
//
//// GET 작품 옵션 조회
//function getOption($productIdx){
//    $pdo = pdoSqlConnect();
//    $query = "select group_concat(optionName separator '/') as optionName
//from(
//select DISTINCT  productIdx, optionIdx, optionName
//from ProductOption
//where productIdx = ?) O
//group by productIdx;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$productIdx]);
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
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

// GET 작품 옵션 조회
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
// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
