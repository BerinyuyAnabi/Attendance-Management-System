
<?php
// Load environment variables from connect.env
$env = parse_ini_file(__DIR__ . '/../env/connect.env');

// Check if parsing was successful
if ($env === false) {
    die("Error: Could not load environment file");
}

// Create database connection
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

// Don't echo anything here - just make the connection available
?>