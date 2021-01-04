<?php

//READ
function getUsers()
{
    $pdo = pdoSqlConnect();
    $query = "select * from Users;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//READ
function getUserDetail($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select * from User where userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

//READ 홈 화면 조회
function getHome($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select P.productIdx, P.productName, productImageUrl, S.sellerIdx, S.sellerName, S.profileImageUrl as sellerProfileImageUrl,
       reviewerIdx, reviewerName, reviewerProfileImageUrl, reviewcontent, isReviewImageAttached, rate,
       case when Star.userIdx is null then 0 else 1 end as isStarredByMe
from Product as P
inner join (select sellerIdx, sellerName, profileImageUrl from Seller) S on P.sellerIdx = S.sellerIdx
left join (select P.productIdx, group_concat(productImageUrl) as productImageUrl
from Product as P
left join (select productIdx, productImageUrl as productImageUrl from ProductImage) PI on PI.productIdx = P.productIdx
group by P.productIdx) PI on PI.productIdx = P.productIdx
inner join (SELECT productIdx, reviewIdx, reviewerIdx, reviewerName, reviewerProfileImageUrl, reviewcontent, isReviewImageAttached, rate

FROM (

SELECT reviewIdx, Product.productIdx
     , reviewerIdx, reviewerName, reviewerProfileImageUrl, reviewcontent, isReviewImageAttached, rate

, ROW_NUMBER() OVER(PARTITION BY Product.productIdx ORDER BY reviewIdx DESC) as RowIdx

From Product
inner join(
select reviewIdx as reviewIdx, productIdx, U.userIdx as reviewerIdx, userName as reviewerName,
       profileImageUrl as reviewerProfileImageUrl, content as reviewcontent,
        case when imageUrl is null then 0 else 1 end as isReviewImageAttached, rate
from Review
inner join (select userIdx, userName, profileImageUrl from User) U on U.userIdx = Review.userIdx
order by reviewIdx desc) R on R.productIdx = Product.productIdx

) AS t

WHERE RowIdx = 1) R on P.productIdx = R.productIdx
left join (select productIdx, userIdx from StarredProduct where userIdx = ?) Star on P.productIdx = Star.productIdx
order by productIdx desc;
";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


//READ
function isValidUserIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from User where userIdx = ?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}


function createUser($ID, $pwd, $name)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Users (ID, pwd, name) VALUES (?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$ID, $pwd, $name]);

    $st = null;
    $pdo = null;

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
