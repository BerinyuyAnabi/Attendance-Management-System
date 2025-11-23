<?php

session_start();

if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $role = $_SESSION['role'];

    if($role === 'student') {
        header("Location: studentdashboard.php");
        exit();
    } elseif($role === 'faculty') {
        header("Location: facultydashboard.php");
        exit();
    } elseif($role === 'student') {
        header("Location: studentdashboard.php");
        exit();
    }
    exit();}
    else {

    header("Location: signin.php");
    exit();
}
?>