<?php
// GET 작품 검색
function searchKeyword($keyword){
    $pdo = pdoSqlConnect();
    $query = ";";

    $st = $pdo->prepare($query);
    $st->execute([$keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
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

