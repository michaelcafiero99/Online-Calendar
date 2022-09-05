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
// login_ajax.php

header("Content-Type: application/json"); // Since we are sending a JSON response here (not an HTML document), set the MIME Type to application/json

//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');
//This will store the data into an associative array
$json_obj = json_decode($json_str, true);

//Variables can be accessed as such:
$title = $json_obj['title'];
$content = $json_obj['content'];
$time = $json_obj['time'];
$priority = $json_obj['priority'];
$user = $_SESSION['username'];
$token = $json_obj['token'];
// check for access
if(!isset($_SESSION['username'])){
    die("You do not have access");
}
else if (!hash_equals($_SESSION['token'], $token)){
    die("You do not have access");
}

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


$stmt = $mysqli->prepare("insert into events (userId, eventTitle, eventContent, eventTime, priority) VALUES (?, ?, ?, ?, ?)");
if(!$stmt){
    printf("Query 2 Prep Failed: %s\n", $mysqli->error);
    exit;
}
$stmt->bind_param('ssssi', $userId, $title, $content, $time, $priority);
$stmt->execute();
$stmt->close();

echo json_encode(array(
    "success" => true
));
exit;

?>