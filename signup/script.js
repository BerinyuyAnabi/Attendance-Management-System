// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signupForm');
    
    if (signupForm) {
        // Remove the action attribute - prevent normal form submission
        signupForm.removeAttribute('action');
        
        signupForm.addEventListener('submit', function(e) {
            // Prevent default form submission
            e.preventDefault();
            e.stopPropagation();
            
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
            
            // Submit form via AJAX
            fetch('signup_check.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Redirect to login page
                    window.location.href = '../login/login.php';
                } else {
                    if (errorDiv) {
                        errorDiv.textContent = data.message;
                        errorDiv.style.color = 'red';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (errorDiv) {
                    errorDiv.textContent = 'An error occurred. Please try again.';
                    errorDiv.style.color = 'red';
                }
            });
            
            return false;
        });
    }
});