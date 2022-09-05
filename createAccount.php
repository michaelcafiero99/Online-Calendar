<?php

require 'database.php';

header("Content-Type: application/json"); // Since we are sending a JSON response here (not an HTML document), set the MIME Type to application/json

//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');
//This will store the data into an associative array
$json_obj = json_decode($json_str, true);

//Variables can be accessed as such:
$username = $json_obj['userName'];
$password = $json_obj['password'];
$fname =  $json_obj['fname'];
$lname =  $json_obj['lname'];
$userexists = false;

if ($username!= "" && $password != "" && $fname != "" && $lname != "") {
    $hashedPW = password_hash($password, PASSWORD_BCRYPT);
    $stmt2 = $mysqli->prepare("select userName from users where userName = ?");
    if(!$stmt2){
        printf("Query 2 Prep Failed: %s\n", $mysqli->error);
        exit;
    }
    $stmt2->bind_param('s', $username);
    $stmt2->execute();
    $stmt2->bind_result($user);
    if($stmt2->fetch() != null){

        $userexists = true;
        $stmt2->close();
        echo json_encode(array(
            "success" => false,
            "message" => "user already exists!"
        ));
        exit;
    }
    
    else{
        $stmt2->close();
        $stmt = $mysqli->prepare("insert into users (userName, firstName, lastName, password) values (?, ?, ?, ?)");
        if(!$stmt){
            echo printf("Query Prep Failed: %s\n", $mysqli->error);
            exit;
        }

        $stmt->bind_param('ssss', $username, $fname, $lname, $hashedPW);
        $stmt->execute();

        $stmt->close();
        ini_set("session.cookie_httponly", 1);
        session_start();

        $previous_ua = @$_SESSION['useragent'];
        $current_ua = $_SERVER['HTTP_USER_AGENT'];
        if(isset($_SESSION['useragent']) && $previous_ua !== $current_ua){
            die("Session hijack detected");
        }else{
            $_SESSION['useragent'] = $current_ua;
        }
        $_SESSION["username"] = $username;
        $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
        echo json_encode(array(
            "success" => true,
            "token" => $_SESSION['token']
        ));
        exit;
    }
}else{
    echo json_encode(array(
        "success" => false,
        "message" => "Missing inputs"
    ));
    exit;
}

?>

