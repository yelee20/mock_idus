<?php

// 내가 주문한게 맞는지, 배송 완료됐는지 확인
function isDelivered($userIdx, $orderIdx){
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

// 후기 인덱스 유효성 검사
function isValidReviewIdx($reviewIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from Review where
                reviewIdx = ? and status != 'D') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// 수정된 후기인지
function hasReviewEverEdited($reviewIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from Review where
                reviewIdx = ? and status = 'E') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// 내가 작성한 후기가 맞는지 확인
function isReviewWrittenByMe($userIdx, $reviewIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from Review where userIdx = ? and 
                reviewIdx = ? and status != 'D') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $reviewIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// UPDATE 후기 수정
function editReview($rate, $reviewContent, $imageUrl, $reviewIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE Review
                        SET rate = ?,
                            content  = ?,
                            imageUrl = ?,
                            updatedAt = CURRENT_TIMESTAMP,
                            status = 'E'
                        WHERE reviewIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$rate, $reviewContent, $imageUrl, $reviewIdx]);
    $st = null;
    $pdo = null;
}

// UPDATE 후기 삭제
function deleteReview($reviewIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE Review
                        SET updatedAt = CURRENT_TIMESTAMP,
                            status = 'D'
                        WHERE reviewIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx]);
    $st = null;
    $pdo = null;
}

//READ 홈 화면 조회
function getReviews($productIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select reviewIdx, R.userIdx as reviewerIdx, userName as reviewerName,
       U.profileImageUrl as reviewerProfileImageUrl, imageUrl as reviewImageUrl, content as reviewContent,
       rate, date_format(R.createdAt,'%Y년 %c월 %e일') as date
from Review as R
inner join (select userIdx, userName, profileImageUrl from UserInfo) U on U.userIdx = R.userIdx
inner join (select orderIdx, productIdx from OrderLog) O on O.orderIdx = R.orderIdx
where O.productIdx = ?
order by reviewIdx desc;";

    $st = $pdo->prepare($query);
    $st->execute([$productIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// CREATE 후기 등록
function createReviewComment($reviewIdx, $userIdx, $commentContent)
{
    $pdo = pdoSqlConnect();
    $query = "insert into ReviewComment (reviewIdx, userIdx, comment) 
                values (?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$reviewIdx, $userIdx, $commentContent]);
    $st = null;
    $pdo = null;
}

// 후기 댓글 인덱스 확인
function isValidReviewCommentIdx($commentIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from ReviewComment where 
                commentIdx = ? and status != 'D') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// 내가 작성한 후기 댓글이 맞는지 확인
function isCommentWrittenByMe($commentIdx, $userIdx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select * from ReviewComment where 
                commentIdx = ? and userIdx = ? and status != 'D') as Exist;";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx, $userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]['Exist']);
}

// UPDATE 후기 삭제
function deleteReviewComment($commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE ReviewComment
                        SET updatedAt = CURRENT_TIMESTAMP,
                            status = 'D'
                        WHERE commentIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx]);
    $st = null;
    $pdo = null;
}
