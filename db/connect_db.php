<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Collect the data from the form

$firstName = $_GET['firstname'];
$lastName  = $_GET['lastname'];
$email     = $_GET['email'];
$role      = $_GET['role'];
$password  = $_GET['password']; /

// connect to the database
$servername = "localhost";
$username = "root";
$dbpassword = "root";
$dbname = "Attendance_Management_System";

// Creating a database connection
$conn = new mysqli($servername, $username, $dbpassword, $dbname);
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
else {
    echo "Connected successfully \n";
    // <br>
}

?>