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
$month = $json_obj['currMonth'];
$year = $json_obj['currYear'];
$len = $json_obj['monthLen'];

$startDate = $year . "-" . $month . "-1";
$endDate = $year . "-" . $month . "-" . $len;

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

$stmt = $mysqli->prepare("select eventTitle, eventTime FROM events WHERE userId = ? and priority = 3 and DATE(eventTime) BETWEEN ? AND ?");
if(!$stmt){
    printf("Auery 2 Prep Failed: %s\n", $mysqli->error);
    exit;
}
// Bind the parameter
$stmt->bind_param('sss', $userId, $startDate, $endDate);
$stmt->execute();
// Bind the results
$stmt->bind_result($eventTitle, $eventTime);
$eventTitles = array();
$eventDates = array();
while($stmt->fetch()){
    array_push($eventTitles, htmlentities($eventTitle));
    array_push($eventDates, htmlentities($eventTime));
}

$stmt->close();
echo json_encode(array(
    "success" => true,
    "eventTitles" => $eventTitles, 
    "eventDates" => $eventDates
));
exit;
?>
