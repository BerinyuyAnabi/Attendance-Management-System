
<?php
session_start();
header('Content-Type: application/json');

// Database connection  
require_once 'Attendance-Management-System/db/connect_db.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // form inputs
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Prepare SQL
    $stmt = $conn->prepare("SELECT id, fullname, username, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {

            // Set session
            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];

            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();

        } else {
            echo "Invalid email or password";
        }

    } else {
        echo "No user found with that email";
    }

    $stmt->close();
    $conn->close();
}
?>
