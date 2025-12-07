<?php
// Load environment variables from connect.env
$env = @parse_ini_file(__DIR__ . '/../env/connect.env');

// Check if parsing was successful
if ($env === false) {
    // Check if this is an AJAX request expecting JSON
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        echo json_encode(["success" => false, "message" => "Database configuration error"]);
        exit();
    }
    die("Error: Could not load environment file");
}

// Create database connection (suppress warnings with @)
$conn = @new mysqli(
    $env['host'],
    $env['user'],
    $env['password'],
    $env['database']
);

// Check connection
if ($conn->connect_error) {
    // Check if this is an AJAX request expecting JSON
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        echo json_encode(["success" => false, "message" => "Database connection failed"]);
        exit();
    }
    die("Connection failed: " . $conn->connect_error);
}

// Don't echo anything here - just make the connection available
?>