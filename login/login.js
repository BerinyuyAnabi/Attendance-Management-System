

// Getting all the elements we need from the HTML
const form = document.getElementById('loginForm');
const email = document.getElementById('email');
const password = document.getElementById('password');
const errorEmail = document.getElementById('error-email');
const errorPassword = document.getElementById('error-password');
const messageContainer = document.getElementById('message-container');
const loginButton = document.getElementById('login');

// Listening for form submission
form.addEventListener('submit', function(event) {
    // Prevent the default form submission (which would refresh the page)
    event.preventDefault();

    // Clear any previous error messages
    clearErrors();

    // Validating  the form inputs
    let isValid = true;

    if (email.value.trim() === '') {
        showError(errorEmail, email, 'Email is required');
        isValid = false;
    }

    if (password.value === '') {
        showError(errorPassword, password, 'Password is required');
        isValid = false;
    }

    // If validation fails, stop the process 
    if (!isValid) {
        return;
    }

    // Prepare the form data for AJAX
    const formData = new FormData(form);

    // Disable the login button to prevent multiple submissions
    loginButton.disabled = true;
    loginButton.textContent = 'Logging in...';

    // Send AJAX request using Fetch API
    fetch('login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Convert the response to JSON format
        return response.json();
    })
    .then(result => {
        // Handle the response from the server
        console.log('Server response:', result);

        if (result.success) {
            // Display success message
            showMessage(
                'Login successful! Welcome, ' + result.user.name + '!',
                'success'
            );

            // Wait 1.5 seconds before redirecting to the user dashboard 
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1500);

        } else {
            // Display error message
            showMessage(result.message, 'error');

            // Re-enable the login button
            loginButton.disabled = false;
            loginButton.textContent = 'Log In';
        }
    })
    .catch(error => {
        // Handle network errors
        console.error('Network error:', error);
        showMessage('Connection error. Please try again.', 'error');

        // Re-enable the login button
        loginButton.disabled = false;
        loginButton.textContent = 'Log In';
    });
});


// Function to display error messages under input fields
function showError(errorDiv, inputElement, message) {
    errorDiv.textContent = message;
    errorDiv.style.color = '#ff0000';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '5px';
    errorDiv.style.display = 'block';
    inputElement.style.borderColor = '#ff0000';
}

// Function to clear all error messages
function clearErrors() {
    errorEmail.textContent = '';
    errorEmail.style.display = 'none';
    errorPassword.textContent = '';
    errorPassword.style.display = 'none';
    email.style.borderColor = '';
    password.style.borderColor = '';
    messageContainer.style.display = 'none';
}

// Displays success or error messages at the top of the form
function showMessage(message, type) {
    messageContainer.textContent = message;
    messageContainer.style.display = 'block';

    if (type === 'success') {
        messageContainer.style.backgroundColor = '#d4edda';
        messageContainer.style.color = '#155724';
        messageContainer.style.border = '1px solid #c3e6cb';
    } else {
        messageContainer.style.backgroundColor = '#f8d7da';
        messageContainer.style.color = '#721c24';
        messageContainer.style.border = '1px solid #f5c6cb';
    }
}
