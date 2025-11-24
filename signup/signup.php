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
<form id="signupForm" action="javascript:void(0);" method="post" class="input">
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
<a id="signup" class="button-link" href="../login/login.php" role="button">Already have an Account</a>
</div>
</form>
</div>
</div>

<script>
(function() {
    'use strict';
    
    const signupForm = document.getElementById('signupForm');
    
    if (signupForm) {
        signupForm.onsubmit = function(e) {
            e.preventDefault();
            
            // Clear previous error messages
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            const errorDiv = document.getElementById('error');
            if (errorDiv) errorDiv.textContent = '';
            
            // Get form values
            const firstName = document.getElementById('f_name').value.trim();
            const lastName = document.getElementById('l_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const role = document.getElementById('role').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('c_password').value;
            
            // Client-side validation
            let hasError = false;
            
            if (!firstName) {
                document.getElementById('error-fname').textContent = 'First name is required';
                hasError = true;
            }
            
            if (!lastName) {
                document.getElementById('error-lname').textContent = 'Last name is required';
                hasError = true;
            }
            
            if (!email) {
                document.getElementById('error-email').textContent = 'Email is required';
                hasError = true;
            }
            
            if (!role) {
                document.getElementById('error-role').textContent = 'Please select a role';
                hasError = true;
            }
            
            if (!password) {
                document.getElementById('error-password').textContent = 'Password is required';
                hasError = true;
            }
            
            if (password !== confirmPassword) {
                document.getElementById('error-cpassword').textContent = 'Passwords do not match';
                hasError = true;
            }
            
            if (hasError) {
                return false;
            }
            
            // Create FormData object
            const formData = new FormData();
            formData.append('firstname', firstName);
            formData.append('lastname', lastName);
            formData.append('email', email);
            formData.append('role', role);
            formData.append('password', password);
            
            // Disable submit button
            const submitBtn = document.getElementById('login');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Signing up...';
            }
            
            // Submit form via AJAX
            fetch('signup_check.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = '../login/login.php';
                } else {
                    if (errorDiv) {
                        errorDiv.textContent = data.message;
                        errorDiv.style.color = 'red';
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Sign Up';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (errorDiv) {
                    errorDiv.textContent = 'An error occurred. Please try again.';
                    errorDiv.style.color = 'red';
                }
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign Up';
                }
            });
            
            return false;
        };
    }
})();
</script>
</body>
</html>