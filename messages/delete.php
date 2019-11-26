<?php
/**
 * If the request is a GET, we'll return a JSON-formatted list of recent holidayGreetings.
 * If the request is a POST, we'll post the incoming holidayGreeting.
 */

$dsn = "mysql:host=localhost;dbname=merrySparkmas;charset=utf8";
$dbUsername = "merrySparkmas";
$dbPassword = "uglyGhos+52";

$db = new PDO($dsn, $dbUsername, $dbPassword);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("SET NAMES utf8;");

if ($_SERVER['REQUEST_METHOD']=="GET") {
    if (($holidayGreetingId = filter_var($_GET['holidayGreetingId'], FILTER_VALIDATE_INT))===false) {
        exit;
    }

    if (!isset($_GET['holidayGreetingIdHash'])) {
        exit;
    }

    if ($_GET['holidayGreetingIdHash'] != md5("fvbnk" . $holidayGreetingId . "cvbnmb")) {
        exit;
    }

    $q = <<<SQL
DELETE
FROM
    holidayGreeting
WHERE
    holidayGreetingId=:holidayGreetingId;
SQL;

    $stmt = $db->prepare($q);
    $stmt->bindValue(':holidayGreetingId', $holidayGreetingId, PDO::PARAM_INT);
    $stmt->execute();

    echo "Greeting #" . $holidayGreetingId . " deleted.";

}