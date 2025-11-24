<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require_once __DIR__ . '/../db/connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // form inputs
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
    $password = isset($_POST["password"]) ? $_POST["password"] : '';

    if (empty($email) || empty($password)) {
        header("Location: signin.php?error=empty");
        exit();
    }

    // Prepare SQL with correct column names
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password_hash, role FROM users WHERE email = ?");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
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
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['role'] = $user['role'];

            $stmt->close();
            $conn->close();

            // Get base path for redirects
            $basePath = dirname(dirname($_SERVER['PHP_SELF']));

            // Redirect based on role
            switch ($user['role']) {
                case 'student':
                    header("Location: " . $basePath . "/dashboard/studentdashboard.php");
                    break;
                case 'faculty':
                    header("Location: " . $basePath . "/dashboard/facultydashboard.php");
                    break;
                case 'faculty_intern':
                    header("Location: " . $basePath . "/dashboard/facultyInternDashboard.php");
                    break;
                case 'admin':
                    header("Location: " . $basePath . "/dashboard/admin.php");
                    break;
                default:
                    header("Location: " . $basePath . "/dashboard/studentdashboard.php");
            }
            exit();

        } else {
            // Invalid password - redirect back with error
            $stmt->close();
            $conn->close();
            header("Location: signin.php?error=invalid");
            exit();
        }

    } else {
        // No user found - redirect back with error
        $stmt->close();
        $conn->close();
        header("Location: signin.php?error=notfound");
        exit();
    }

} else {
    // Not a POST request - redirect to login page
    header("Location: signin.php");
    exit();
}
?>
