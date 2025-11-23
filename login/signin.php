<?php 

// Including the the database connection file
require_once '/db/connect_db.php';
// authenticate user
include 'auth_check.php';

checkRole('student');
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
           <form id = "loginForm" action="login.php" method="post" class="input">
            <label for="email" > Email</label>
            <input id = 'email' type= "email" name= "email" placeholder= "Enter your email" required></input>
             <label for="password"> Password</label>
            <input id = 'password' type= "password" name= "password" placeholder= "Enter your password" required></input>
           </form>
<!-- Adding the back and from button -->
 
            <button id="forgot-pswd">Forgot Password</button>

            <div class="foot">
                <button id="login" type="submit">Log In</button>
                <a id="signup" class="button-link" href="signup.html" role="button" name="loginbtn">Create an Account</a>
            </div>

            
    </div>
    </div>
</body>

</html>