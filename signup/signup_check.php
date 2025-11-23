<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "/Applications/MAMP/htdocs/Attendance-Management-System/db/connect.php";

// Get form data
$firstName = trim($_POST['firstname'] ?? '');
$lastName = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

// Validate required fields
if(empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($role)){
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "All fields are required."
    ]);
    exit();
}

// Hash the password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Starting the transaction 
$conn->begin_transaction();

try{
    // Insert into the user table 
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $firstName, $lastName, $email, $password_hash, $role);
    
    if(!$stmt->execute()){
        throw new Exception("User registration failed: " . $stmt->error);
    }
    
    // Get the inserted user ID
    $user_id = $conn->insert_id;
    $stmt->close();
    
    // Insert into role-specific table
    if($role == 'student'){
        $stmt2 = $conn->prepare("INSERT INTO students (student_id) VALUES (?)");
        $stmt2->bind_param("i", $user_id);
        if(!$stmt2->execute()){
            throw new Exception("Insertion into the student table failed!");
        }
        $stmt2->close();
    }
    else if($role == 'faculty' || $role == 'faculty_intern'){
        $stmt2 = $conn->prepare("INSERT INTO faculty (faculty_id) VALUES (?)");
        $stmt2->bind_param("i", $user_id);
        if(!$stmt2->execute()){
            throw new Exception("Insertion into the faculty table failed!");
        }
        $stmt2->close();
    }
    
    $conn->commit();
    
    // Return success response
    header('Content-Type: application/json');
    echo json_encode([
        "success" => true,
        "message" => "Registration successful!"
    ]);
    
} catch(Exception $e){
    $conn->rollback();
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false,
        "message" => "Registration Failed: " . $e->getMessage()
    ]);
}

$conn->close();
?>