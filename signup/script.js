// Multiple methods to ensure form doesn't submit normally
(function() {
    'use strict';
    
    function initSignupForm() {
        const signupForm = document.getElementById('signupForm');
        
        if (!signupForm) {
            console.error('Signup form not found');
            return;
        }
        
        // Method 1: Remove action attribute
        signupForm.setAttribute('action', 'javascript:void(0);');
        
        // Method 2: Add onsubmit handler
        signupForm.onsubmit = function(e) {
            e.preventDefault();
            handleFormSubmit(e);
            return false;
        };
        
        // Method 3: Add event listener
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            handleFormSubmit(e);
            return false;
        }, true);
        
        function handleFormSubmit(e) {
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
            
            // Disable submit button to prevent double submission
            const submitBtn = signupForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Signing up...';
            }
            
            // Submit form via AJAX
            fetch('signup_check.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Redirect to login page
                    window.location.href = '../login/signin.php';
                } else {
                    if (errorDiv) {
                        errorDiv.textContent = data.message;
                        errorDiv.style.color = 'red';
                    }
                    // Re-enable submit button
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
                // Re-enable submit button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign Up';
                }
            });
            
            return false;
        }
    }
    
    // Try to initialize immediately
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSignupForm);
    } else {
        initSignupForm();
    }
})();