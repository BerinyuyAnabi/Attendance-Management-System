
<?php

// Use __DIR__ to get the current file's directory, then go up one level
$env = parse_ini_file(__DIR__ . '/../env/connect.env');

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
}
?>

