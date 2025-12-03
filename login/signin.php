<?php 

//Scure the page by starting the session 

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Including the the database connection file
require_once __DIR__ . '/../db/connect_db.php';

// authenticate user
// include 'auth_check.php';

// checkRole('student');
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

    <div class="login">

        <div class="image">
            <h3>Attendance Management System</h3>
        </div>

        <div class="form">
            <h4>Log In to your Account</h4>

            <!-- Message display area for AJAX responses -->
            <div id="message-container" style="display: none; padding: 10px; margin-bottom: 15px; border-radius: 5px;"></div>

           <form id="loginForm" class="input">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" placeholder="Enter your email" required>
            <div id="error-email" class="error-message"></div>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" placeholder="Enter your password" required>
            <div id="error-password" class="error-message"></div>

            <button id="forgot-pswd" type="button">Forgot Password</button>

            <div class="foot">
                <button id="login" type="submit">Log In</button>
                <a id="signup" class="button-link" href="../signup/signup.php" role="button">Create an Account</a>
            </div>
           </form>


    </div>
    </div>

    <script src="login.js"></script>
</body>

</html>