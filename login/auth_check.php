<?php
f(session_status() == PHP_SESSION_NONE){ 
    session_start();
}

if(!isset($_SESSION['user_id'])){
    // if the user is not logged in ensure they login 
    header("Location: ../signin.html");
    exit();
}

// Validating the user's role
function checkrole($required_role){
    if(!isset($_SESSION['user_id'])){
    header("Location: login.html");
    exit();

}
if($_SESSION['role'] !== $required_role){

    http_response_code(403);
    echo "Access Denied";
    // Will include proper html code to display that 

    exit();
}
}


?>
