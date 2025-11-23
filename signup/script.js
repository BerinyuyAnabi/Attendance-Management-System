// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Script loaded');
    const signupForm = document.getElementById('signupForm');
    
    if (signupForm) {
        console.log('Form found');
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submit prevented');
            
            // Clear previous error messages
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
            document.getElementById('error').textContent = '';
            
            // Get form values
            const firstName = document.getElementById('f_name').value.trim();
            const lastName = document.getElementById('l_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const role = document.getElementById('role').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('c_password').value;
            
            console.log('Form values:', {firstName, lastName, email, role, password: '***', confirmPassword: '***'});
            
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
                console.log('Validation errors found');
                return;
            }
            
            console.log('Validation passed, submitting...');
            
            // Create FormData object
            const formData = new FormData(signupForm);
            
            // Log FormData contents
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + (pair[0] === 'password' ? '***' : pair[1]));
            }
            
            // Submit form via AJAX
            fetch('signup_check.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    alert(data.message);
                    // Redirect to login page
                    window.location.href = '../login/login.php';
                } else {
                    document.getElementById('error').textContent = data.message;
                    if (data.debug) {
                        console.log('Debug info:', data.debug);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('error').textContent = 'An error occurred. Please try again.';
            });
        });
    } else {
        console.error('Form not found!');
    }
});