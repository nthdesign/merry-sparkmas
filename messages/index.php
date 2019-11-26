<?php
/**
 * If the request is a GET, we'll return a JSON-formatted list of recent holidayGreetings.
 * If the request is a POST, we'll post the incoming holidayGreeting.
 */

$dsn = "mysql:host=localhost;dbname=merrySparkmas;charset=utf8";
$dbUsername = "merrySparkmas";
$dbPassword = "uglyGhos+52";
$numRecordsToDisplay = 10000;

$curseWords = array(
    "nigger", "fag", "faggot", "fuck", "shit", "pussy", "cunt", "bitch",
    "idiot", "bastard", "penis", "vagina", "cock", "blowjob", " ass ", " tits ", " dick ", "8=", " dicks ", "hitler",
    "jew", "kike"
);

$db = new PDO($dsn, $dbUsername, $dbPassword);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("SET NAMES utf8;");

if ($_SERVER['REQUEST_METHOD']=="GET") {
    $q = <<<SQL
SELECT
    hg.holidayGreetingId,
    hg.dateCreated,
    hg.twitterUsername,
    hg.holidayGreeting
FROM
    holidayGreeting hg
ORDER BY
    hg.dateCreated DESC
LIMIT :numRecordsToDisplay;
SQL;

    $stmt = $db->prepare($q);
    $stmt->bindValue(':numRecordsToDisplay', $numRecordsToDisplay, PDO::PARAM_INT);
    $stmt->execute();

    $arrayOfHolidayGreetings = array();

    while ($row = $stmt->fetchObject()) {
        $arrayOfHolidayGreetings[] = $row;
    }

    $json = json_encode($arrayOfHolidayGreetings);

    header("Content-type: application/json");
    echo $json;

} elseif ($_SERVER['REQUEST_METHOD']=="POST") {
    /**
     * INSERT the new holidayGreeting.
     */
    if (empty($_POST['twitterUsername'])===false && strposa($_POST['twitterUsername'], $curseWords)===false) {
        $twitterUsername = trim($_POST['twitterUsername']);
        $twitterUsername = str_replace("@", "", $twitterUsername);
    } else {
        $twitterUsername = "";
    }

    if (empty($_POST['holidayGreeting'])===false && strposa($_POST['holidayGreeting'], $curseWords)===false) {
        $holidayGreeting = trim($_POST['holidayGreeting']);
        $holidayGreeting = str_replace("\n", " ", $holidayGreeting);
    } else {
        $holidayGreeting = "";
    }

    //Exit if our holidayGreeting is empty.
    if (empty($holidayGreeting)) {
        exit;
    }

    //Build the INSERT query.
    $q = <<<SQL
INSERT INTO holidayGreeting (
    dateCreated,
	dateModified,
	twitterUsername,
	holidayGreeting
) VALUES (
    NOW(),
    NOW(),
    :twitterUsername,
    :holidayGreeting
);
SQL;

    $stmt = $db->prepare($q);
    $stmt->bindValue(':twitterUsername', $twitterUsername, PDO::PARAM_STR);
    $stmt->bindValue(':holidayGreeting', $holidayGreeting, PDO::PARAM_STR);
    $stmt->execute();

    $holidayGreetingId = $db->lastInsertId();
    $holidayGreetingIdHash = md5("fvbnk" . $holidayGreetingId . "cvbnmb");

    //Send an email to the admin.
    $to = "nthdesign@gmail.com";
    $subject = "New Sparkmas Greeting";
    $message = "MESSAGE: " . $holidayGreeting . "\n\n";
    if (!empty($twitterUsername)) {
        $message .= "TWITTER: http://twitter.com/" . $twitterUsername . "/\n\n";
    }
    $message .= "DELETE: http://www.nth-design.com/merry-sparkmas/messages/delete.php?";
    $message .= "holidayGreetingId=" . $holidayGreetingId;
    $message .= "&holidayGreetingIdHash=" . $holidayGreetingIdHash;
    mail($to, $subject, $message);

    //Display the greeting on the ornament.
    $url = "https://api.spark.io/v1/devices/48ff6e065067555049412287/showGreeting";
    $parameters = array("access_token"=>"3ad8d6f6c0906b791aa992a65dc5d9d0ed7a607a",
        "greeting"=>$holidayGreeting);
    $c = curl_init();	//Initialize a cURL connection.
    curl_setopt($c, CURLOPT_URL, $url);		//Tell cURL which URL to connect to.
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);		//Tell cURL we want our output returned as a string.
    curl_setopt($c, CURLOPT_POST, 1);	//Tell cURL we want to POST our request.
    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($parameters));		//Set the POST variables.

    //Get the cURL response.
    $response = curl_exec($c);

    $data = array("ornamentResponse"=>$response);
    $json = json_encode($data);
    header("Content-type: application/json");
    echo $json;
}

function strposa($haystack, $needle, $offset=0) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $query) {
        if(stripos($haystack, $query, $offset) !== false) return true;
    }
    return false;
}