<?php
require "database.php";

ini_set("session.cookie_httponly", 1);
session_start();

$previous_ua = @$_SESSION['useragent'];
$current_ua = $_SERVER['HTTP_USER_AGENT'];

if(isset($_SESSION['useragent']) && $previous_ua !== $current_ua){
	die("Session hijack detected");
}else{
	$_SESSION['useragent'] = $current_ua;
}

header("Content-Type: application/json"); // Since we are sending a JSON response here (not an HTML document), set the MIME Type to application/json

//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');
//This will store the data into an associative array
$json_obj = json_decode($json_str, true);

//Variables can be accessed as such:
$day = $json_obj['day'];
$month = $json_obj['month'];
$year = $json_obj['year'];
$sortVal = $json_obj['sortVal'];

$date = $year . "-" . $month . "-" . $day;

$user = $_SESSION['username'];
$stmt = $mysqli->prepare("select userId FROM users WHERE userName = ?");
if(!$stmt){
    printf("Buery 2 Prep Failed: %s\n", $mysqli->error);
    exit;
}

// Bind the parameter
$stmt->bind_param('s', $user);
$stmt->execute();

// Bind the results
$stmt->bind_result($userId);
$stmt->fetch();
$stmt->close();


if ($sortVal == "priority"){
    $stmt = $mysqli->prepare("select eventId, eventTitle, eventContent, eventTime, priority FROM events WHERE userId = ? and DATE(eventTime) = ? ORDER BY priority");
    if(!$stmt){
        printf("Auery 2 Prep Failed: %s\n", $mysqli->error);
        exit;
    }
}
else if ($sortVal == "eventTime"){
    $stmt = $mysqli->prepare("select eventId, eventTitle, eventContent, eventTime, priority FROM events WHERE userId = ? and DATE(eventTime) = ? ORDER BY eventTime");
    if(!$stmt){
        printf("Auery 2 Prep Failed: %s\n", $mysqli->error);
        exit;
    }
}
else if ($sortVal == "eventTitle"){
    $stmt = $mysqli->prepare("select eventId, eventTitle, eventContent, eventTime, priority FROM events WHERE userId = ? and DATE(eventTime) = ? ORDER BY eventTitle");
    if(!$stmt){
        printf("Auery 2 Prep Failed: %s\n", $mysqli->error);
        exit;
    }
}
else {
    $stmt = $mysqli->prepare("select eventId, eventTitle, eventContent, eventTime, priority FROM events WHERE userId = ? and DATE(eventTime) = ?");
    if(!$stmt){
        printf("Auery 2 Prep Failed: %s\n", $mysqli->error);
        exit;
    }

}
// Bind the parameter
$stmt->bind_param('ss', $userId, $date);
$stmt->execute();
// Bind the results
$stmt->bind_result($eventId, $eventTitle, $eventContent, $eventTime, $priority);
$eventIds = array();
$eventTitles = array();
$eventContents = array();
$eventDates = array();
$eventPriorities = array();
while($stmt->fetch()){
    array_push($eventIds, htmlentities($eventId));
    array_push($eventTitles, htmlentities($eventTitle));
    array_push($eventContents, htmlentities($eventContent));
    array_push($eventDates, htmlentities($eventTime));
    array_push($eventPriorities, htmlentities($priority));
}

$stmt->close();
echo json_encode(array(
    "success" => true,
    "eventIds" => $eventIds, 
    "eventTitles" => $eventTitles, 
    "eventContents" => $eventContents, 
    "eventDates" => $eventDates,
    "eventPriorities" => $eventPriorities,
    "eventTestSort" => $sortVal,
    "eventTestDate" => $date
));
exit;
?>
