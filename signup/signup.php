
<?php
// require_once '../login/auth_check.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup Page</title>
    <!-- <link rel="stylesheet" href="/Attendance-Management-System/login/login.css"> -->
    <link rel="stylesheet" href="../login/login.css">
</head>
<body>

    <div class="login">

        <div class="image">
            <h3>Attendance Management System</h3>
        </div>

        <div class="form"> 
            <h4> Create a New Account</h4>

<div id="error"></div>


    <form id="signupForm" action="signup_check.php" method="get" class="input">
    <label for="firstname">First Name</label>
    <input id='f_name' type="text" name="firstname" placeholder="Enter your First Name">
    <div class="error-message" id="error-fname"></div>

    <label for="lastname">Last Name</label>
    <input id='l_name' type="text" name="lastname" placeholder="Enter your Last Name">
    <div class="error-message" id="error-lname"></div>

    <label for="email">Email</label>
    <input id='email' type="email" name="email" placeholder="Enter your email">
    <div class="error-message" id="error-email"></div>

    <label for="email">Role</label>
    <!-- <input id='role' type="role" name="email" placeholder="Choose your role"> -->
    <!-- Role dropdown -->
        <select id="role" name="role">
            <option value="" disabled selected>Select your role</option>
            <option value="student">Student</option>
            <option value="faculty">Faculty</option>
            <option value="faculty_intern">Faculty Intern</option>
     </select>
    
    <div class="error-message" id="error-role"></div>
    

    <label for="password">Password</label>
    <input id='password' type="password" name="password" placeholder="Enter your password">
    <div class="error-message" id="error-password"></div>

    <label for="c_password">Confirm Password</label>
    <input id='c_password' type="password" name="c_password" placeholder="Confirm your password">
    <div class="error-message" id="error-cpassword"></div>

    <div class="foot">
        <button id="login" type="submit" name="Signup">Sign Up</button>
        <a id="signup" class="button-link" href="login.html" role="button">Already have an Account</a>
    </div>
</form>

           
            
    </div>
    </div>
    <script src="script.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
</body>

</html>