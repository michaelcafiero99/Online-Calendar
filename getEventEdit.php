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
$id = $json_obj['eventId'];

$stmt = $mysqli->prepare("select eventTitle, eventContent, eventTime, priority FROM events WHERE eventId = ?");
if(!$stmt){
    printf("Buery 2 Prep Failed: %s\n", $mysqli->error);
    exit;
}

// Bind the parameter
$stmt->bind_param('s', $id);
$stmt->execute();

// Bind the results
$stmt->bind_result($title, $content, $time, $priority);
$stmt->fetch();
$stmt->close();
echo json_encode(array(
    "success" => true,
    "editId"=>$id,
    "eventTitle" => htmlentities($title), 
    "eventContent" => htmlentities($content), 
    "eventTime" => htmlentities($time), 
    "priority" => htmlentities($priority)
));
exit;
?>
