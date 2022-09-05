<?php
require 'database.php';

// login_ajax.php

header("Content-Type: application/json"); // Since we are sending a JSON response here (not an HTML document), set the MIME Type to application/json

//Because you are posting the data via fetch(), php has to retrieve it elsewhere.
$json_str = file_get_contents('php://input');
//This will store the data into an associative array
$json_obj = json_decode($json_str, true);

//Variables can be accessed as such:
$username = $json_obj['userName'];
$password = $json_obj['password'];
//This is equivalent to what you previously did with $_POST['username'] and $_POST['password']

// Check to see if the username and password are valid.  (You learned how to do this in Module 3.)

if( $username != "" && $password != "" ){
	$stmt = $mysqli->prepare("select COUNT(*), userId, password FROM users WHERE userName = ?");
    if(!$stmt){
        printf("Query 2 Prep Failed: %s\n", $mysqli->error);
        exit;
    }

    // Bind the parameter
    $stmt->bind_param('s', $username);
    $stmt->execute();
    
    // Bind the results
    $stmt->bind_result($cnt, $user_id, $pwd_hash);
    $stmt->fetch();

    if($cnt == 1 && password_verify($password, $pwd_hash)){
        // Login succeeded!
        ini_set("session.cookie_httponly", 1);
        session_start();

        $previous_ua = @$_SESSION['useragent'];
        $current_ua = $_SERVER['HTTP_USER_AGENT'];
        if(isset($_SESSION['useragent']) && $previous_ua !== $current_ua){
            die("Session hijack detected");
        }else{
            $_SESSION['useragent'] = $current_ua;
        }
        $_SESSION['username'] = $username;
        $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
        echo json_encode(array(
            "success" => true,
            "token" => $_SESSION['token']
        ));
        exit;

    } else{
        // Login failed; redirect back to the login screen
        echo json_encode(array(
            "success" => false,
            "message" => "Incorrect Username or Password"
        ));
        exit;
    }
}else{
	echo json_encode(array(
		"success" => false,
		"message" => "things look empty!"
	));
	exit;
}
?>