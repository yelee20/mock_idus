<?php

//READ
function getUserInfo($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select userName, profileImageUrl, email, concat('xn#mobileNo',mobileNo) as mobileNo, class as userClass, ifnull(couponNum,0) as couponNum, point
from UserInfo
left join (select userIdx, count(couponIdx) as couponNum
from Coupon
where userIdx = $userIdx
group by userIdx) C on C.userIdx = UserInfo.userIdx
where UserInfo.userIdx = $userIdx;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

//READ
function getUserDetail($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select * from UserInfo where userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

//READ
function isValidUserIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from UserInfo where userIdx = ? and status = 'N') exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

// Check Validation of Email
function isValidEmail($email)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from UserInfo where email = ? and status = 'N') exist;";

    $st = $pdo->prepare($query);
    $st->execute([$email]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}


// Check Validation of MobileNo
function isValidMobileNo($mobileNo)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from UserInfo where mobileNo = ? and status = 'N') exist;";

    $st = $pdo->prepare($query);
    $st->execute([$mobileNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

// Check Validation of ReferenceCode
function isValidReferenceCode($referenceCode)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from UserInfo where referenceCode = ? and status = 'N') exist;";

    $st = $pdo->prepare($query);
    $st->execute([$referenceCode]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

// Check Validation of ProductIdx
function isValidProductIdx($productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from Product where productIdx = ? and status = 'N') exist;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

// GET userIdx by facebookID
function getUserIdxByFacebookID($facebookID)
{
    $pdo = pdoSqlConnect();
    $query = "select userIdx from UserInfo where facebookID = ? and status = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$facebookID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function createUser($userName, $mobileNo, $email, $facebookID)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO UserInfo (userName, mobileNo, email, facebookID) VALUES (?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userName, $mobileNo, $email, $facebookID]);
    $userIdx = $pdo->lastInsertId();

    $st = null;
    $pdo = null;

    return $userIdx;

}

function createReferenceCode($email, $mobileNo)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE UserInfo
                        SET referenceCode = ?
                        WHERE mobileNo = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$email,$mobileNo]);

    $st = null;
    $pdo = null;

}


function createTest($userName,$pwd)
{
    $pdo = pdoSqlConnect();
    $query = "insert into test (userName, password) values (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userName,$pwd]);

    $st = null;
    $pdo = null;

}

// UPDATE 사용자 정보 수정
function updateUserInfo($userName, $profileImageUrl, $mobileNo, $email, $gender, $birthday, $userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE UserInfo
                        SET userName = ?,
                            profileImageUrl  = ?,
                            mobileNo  = ?,
                            email = ?,
                            gender  = ?,
                            birthday = ?,
                            updatedAt = CURRENT_TIMESTAMP
                        WHERE userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userName, $profileImageUrl, $mobileNo, $email, $gender, $birthday, $userIdx]);
    $st = null;
    $pdo = null;
}

// 중복 체크
function isDuplicateEmail($userIdx, $email)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from UserInfo where userIdx != ? and email = ? and status = 'N') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $email]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// 중복 체크
function isDuplicateMobileNo($userIdx, $mobileNo)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from UserInfo where userIdx != ? and mobileNo = ? and status = 'N') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $mobileNo]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}



//function createUser($email)
//{
//    $pdo = pdoSqlConnect();
//    $query = "INSERT INTO Users (ID, pwd, name) VALUES (?,?,?);";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$ID, $pwd, $name]);
//
//    $st = null;
//    $pdo = null;
//
//}


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
