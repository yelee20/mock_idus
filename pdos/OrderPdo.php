<?php
//READ
function isValidAddressIdx($userIdx)
{
$pdo = pdoSqlConnect();
$query = "select EXISTS(select * from User where userIdx = ? and isDeleted = 'N') exist;";

$st = $pdo->prepare($query);
$st->execute([$userIdx]);
//    $st->execute();
$st->setFetchMode(PDO::FETCH_ASSOC);
$res = $st->fetchAll();

$st = null;
$pdo = null;

return $res[0]['exist'];
}