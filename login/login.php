<?php
session_start();

// Database connection
require_once __DIR__ . '/../db/connect_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // form inputs
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Prepare SQL with correct column names
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password_hash, role FROM users WHERE email = ?");
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
            header("Location: signin.php?error=invalid");
            exit();
        }

    } else {
        // No user found - redirect back with error
        header("Location: signin.php?error=notfound");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // Not a POST request - redirect to login page
    header("Location: signin.php");
    exit();
}
?>
