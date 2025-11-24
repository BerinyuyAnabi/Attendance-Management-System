<?php
// Disable HTML error output - log errors instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ||
          strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

if ($isAjax) {
    header('Content-Type: application/json');
}

// Buffer output to catch any unexpected output
ob_start();

require_once "../db/connect_db.php";

// Clear any output that might have been generated
ob_clean();

// Check if POST data exists
if(empty($_POST)){
    echo json_encode([
        "success" => false,
        "message" => "No form data received. Please ensure JavaScript is enabled."
    ]);
    exit();
}

// Get form data with proper null handling
$firstName = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
$lastName = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$role = isset($_POST['role']) ? $_POST['role'] : '';

// Validate required fields
if(empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($role)){
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
    if ($isAjax) {
        echo json_encode([
            "success" => true,
            "message" => "Registration successful!"
        ]);
    } else {
        // Direct form submission - redirect to login page
        header("Location: ../login/login.php?registered=1");
        exit();
    }

} catch(Exception $e){
    $conn->rollback();
    echo json_encode([
        "success" => false,
        "message" => "Registration Failed: " . $e->getMessage()
    ]);
}

$conn->close();
?>