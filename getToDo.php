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

header("Content-Type: application/json"); // Since we are sending a JSON response here (not $

//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');
//This will store the data into an associative array
$json_obj = json_decode($json_str, true);

//Variables can be accessed as such:
$date = $json_obj['date'];
$user = $_SESSION['username'];
// $token = htmlentities($json_obj['token']);


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

$stmt = $mysqli->prepare("select eventTitle FROM events WHERE userId = ? and DATE(eventTime) = ?");
if(!$stmt){
    printf("Auery 2 Prep Failed: %s\n", $mysqli->error);
    exit;
}
// Bind the parameter
$stmt->bind_param('ss', $userId, $date);
$stmt->execute();
// Bind the results
$stmt->bind_result($eventTitle);
$eventTitles = array();
while($stmt->fetch()){
    array_push($eventTitles, htmlentities($eventTitle));
}

$stmt->close();
echo json_encode(array(
    "success" => true,
    "eventTitles" => $eventTitles
));
exit;
?>
