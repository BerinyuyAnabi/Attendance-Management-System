<?php 
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

            <?php if (isset($_GET['error'])): ?>
                <div style="color: red; margin-bottom: 10px;">
                    <?php
                    if ($_GET['error'] == 'invalid') echo 'Invalid email or password';
                    elseif ($_GET['error'] == 'notfound') echo 'No account found with that email';
                    ?>
                </div>
            <?php endif; ?>

           <form id="loginForm" action="login.php" method="post" class="input" onsubmit="loginAjax(event)">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" placeholder="Enter your email" required>
            <label for="password">Password</label>
            <input id="password" type="password" name="password" placeholder="Enter your password" required>

            <button id="forgot-pswd" type="button">Forgot Password</button>

            <div class="foot">
                <button id="login" type="submit">Log In</button>
                <a id="signup" class="button-link" href="../signup/signup.php" role="button">Create an Account</a>
            </div>
           </form>


    </div>
    </div>

    <script src="login_ajax.js"></script>
</body>

</html>