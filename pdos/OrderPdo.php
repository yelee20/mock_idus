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

function isValidOrderIdx($orderIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from OrderLog where
                orderIdx = ? and isRefunded = 'N' 
                and isChanged = 'N' and status = 'N') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$orderIdx]);
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

// GET 작품 정보 가져오기
function getOptionInfoByIdx($productIdx,$optionIdx,$detailedOptionIdx){
    $pdo = pdoSqlConnect();
    $query = "select optionIdx, optionName, optionDetail, price from ProductOption 
                where productIdx = ? and optionIdx = ? and detailedOptionIdx = ? and status = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx,$optionIdx,$detailedOptionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// GET 작품 정보 가져오기
function getOrderInfoByIdx($orderIdx){
    $pdo = pdoSqlConnect();
    $query = "select orderIdx, productIdx, quantity, price from OrderLog 
                where orderIdx = ? and status = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$orderIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// GET VIP 회원인지 확인
function isVIPUser($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from UserInfo where userIdx = ? and isVIP = 1) as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// CREATE 작품 구매
function createOrder($userIdx, $productIdx, $quantity, $receiverName, $mobileNo, $address, $requestMessage, $finalPrice, $optionDetail)
{
    $pdo = pdoSqlConnect();
    $query = "insert into OrderLog (userIdx, productIdx, quantity, receiverName, mobileNo, 
                                    address, requestMessage, price, optionDetail) 
                values (?,?,?,?,?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $productIdx, $quantity, $receiverName, $mobileNo, $address, $requestMessage, $finalPrice, $optionDetail]);
    $st = null;
    $pdo = null;
}

// 옵션 인덱스 유효성 검사
function isValidOptionIdx($productIdx, $optionIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from ProductOption where productIdx = ? 
                                and optionIdx = ? and status = 'N') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx, $optionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// 옵션 선택지 인덱스 유효성 검사
function isValidDetailedOptionIdx($productIdx, $optionIdx, $detailedOptionIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from ProductOption where productIdx = ? 
                                and optionIdx = ? and detailedOptionIdx = ? and status = 'N') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx, $optionIdx, $detailedOptionIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// GET 구매한 작품 목록 조회
function getOrderList($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select orderIdx, DATE_FORMAT(createdAt, '%Y.%m.%d') as date, price as totalPrice, OrderLog.productIdx,
       productImageUrl, productName, sellerIdx, sellerName, deliveryStatus
from OrderLog
inner join (select Product.productIdx, productImageUrl, productName, S.sellerIdx, sellerName
from Product
inner join(select sellerIdx, sellerName from Seller) S on S.sellerIdx = Product.sellerIdx
left join (select P.productIdx, group_concat(productImageUrl) as productImageUrl
from Product as P
left join (SELECT * FROM ( SELECT productIdx, productImageUrl , ROW_NUMBER()
    OVER(PARTITION BY productIdx ORDER BY createdAt DESC) ITEM_RN FROM ProductImage ) TEST WHERE ITEM_RN = 1
) PI on PI.productIdx = P.productIdx
group by P.productIdx) PI on PI.productIdx = Product.productIdx) T on T.productIdx = OrderLog.productIdx
where userIdx = ? and OrderLog.status = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// UPDATE 구매 취소
function deleteOrder($orderIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE OrderLog
                        SET updatedAt = CURRENT_TIMESTAMP,
                            status = 'D'
                        WHERE orderIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$orderIdx]);
    $st = null;
    $pdo = null;
}

// 내가 주문한게 맞는지 확인
function isOrderedByMe($userIdx, $orderIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from OrderLog where userIdx = ? and 
                orderIdx = ? and isRefunded = 'N' 
                and isChanged = 'N' and status = 'N') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $orderIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// UPDATE 교환 신청
function changeOrder($orderIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE OrderLog
                        SET updatedAt = CURRENT_TIMESTAMP,
                            isChanged = 'P'
                        WHERE orderIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$orderIdx]);
    $st = null;
    $pdo = null;
}

// UPDATE 환불 신청
function refundOrder($orderIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE OrderLog
                        SET updatedAt = CURRENT_TIMESTAMP,
                            isrefunded = 'P'
                        WHERE orderIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$orderIdx]);
    $st = null;
    $pdo = null;
}

// UPDATE 교환 신청 승인/거부
function updateChangeRequest($response, $orderIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE OrderLog
                        SET updatedAt = CURRENT_TIMESTAMP,
                            isChanged = ?
                        WHERE orderIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$response, $orderIdx]);
    $st = null;
    $pdo = null;
}

// UPDATE 환불 신청 승인/거부
function updateRefundRequest($response, $orderIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE OrderLog
                        SET updatedAt = CURRENT_TIMESTAMP,
                            isRefunded = ?
                        WHERE orderIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$response, $orderIdx]);
    $st = null;
    $pdo = null;
}

// 환불 신청 들어온 작품인지, 신청 승인/거부 권한이 있는지 확인
function isSoldByMe($sellerIdx, $orderIdx) {
    $pdo = pdoSqlConnect();
    $query = "select exists(select sellerIdx, orderIdx
from OrderLog
inner join (select productIdx, sellerIdx from Product) P on P.productIdx = OrderLog.productIdx
where sellerIdx = ? and orderIdx =? and isChanged = 'P' and (isRefunded = 'N' or isRefunded = 'D') and
      status = 'N') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$sellerIdx, $orderIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// 환불 신청 들어온 작품인지, 신청 승인/거부 권한이 있는지 확인
function isSoldByMe2($sellerIdx, $orderIdx) {
    $pdo = pdoSqlConnect();
    $query = "select exists(select sellerIdx, orderIdx
from OrderLog
inner join (select productIdx, sellerIdx from Product) P on P.productIdx = OrderLog.productIdx
where sellerIdx = ? and orderIdx =? and isRefunded = 'P' and (isChanged = 'N' or isChanged = 'D') and
      status = 'N') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$sellerIdx, $orderIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}
