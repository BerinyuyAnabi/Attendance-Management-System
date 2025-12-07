<?php

session_start();

// Destroy all session data

try{
session_unset();
session_destroy();

// return json response

// Redirect to login
header("Location: ../login/signin.php");
exit();

} catch(Exception $e){
    echo json_encode([
        "logout" => false,
        "message" => "Error logging out: " . $e->getMessage()
    ]);
}

?>