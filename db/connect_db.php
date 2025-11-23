
<?php
// Import environment variables from connect.env
$env = parse_ini_file('/Applications/MAMP/htdocs/Attendance-Management-System/env/connect.env');// if your connect is in

// Collect the data from the form

$firstName = $_GET['firstname'];
$lastName  = $_GET['lastname'];
$email     = $_GET['email'];
$role      = $_GET['role'];
$password  = $_GET['password']; 
// Use the values from the environment file to connect
$conn = new mysqli(
$env['host'],
$env['user'],
$env['password'],
$env['database']
);
// Check connection
if ($conn->connect_error) {
die("Connection failed: " . $conn->connect_error);
}
else{
    echo "Connected successfully \n";
    // <br>
}
?>

