<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Set header for JSON response
header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/../db/connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // form inputs
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
    $password = isset($_POST["password"]) ? $_POST["password"] : '';

    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email and password are required'
        ]);
        exit();
    }

    // Prepare SQL with correct column names
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, email, password_hash, role FROM attend_users WHERE email = ?");

    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error occurred'
        ]);
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify password using password_hash column
        if (password_verify($password, $user['password_hash'])) {

            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];

            $stmt->close();
            $conn->close();

            // Get base path for redirects
            $basePath = dirname(dirname($_SERVER['PHP_SELF']));

            // Determine redirect URL based on role
            $redirectUrl = '';
            switch ($user['role']) {
                case 'student':
                    $redirectUrl = $basePath . "/dashboard/studentdashboard.php";
                    break;
                case 'faculty':
                    $redirectUrl = $basePath . "/dashboard/facultydashboard.php";
                    break;
                case 'faculty_intern':
                    $redirectUrl = $basePath . "/dashboard/facultyInternDashboard.php";
                    break;
                case 'admin':
                    $redirectUrl = $basePath . "/dashboard/admin.php";
                    break;
                default:
                    $redirectUrl = $basePath . "/dashboard/studentdashboard.php";
            }

            // Return success response with redirect URL
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'redirect' => $redirectUrl,
                'user' => [
                    'name' => $user['first_name'] . ' ' . $user['last_name'],
                    'role' => $user['role']
                ]
            ]);
            exit();

        } else {
            // Invalid password
            $stmt->close();
            $conn->close();
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email or password'
            ]);
            exit();
        }

    } else {
        // No user found
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'No account found with that email'
        ]);
        exit();
    }

} else {
    // Not a POST request
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}