// Getting form and input elements
const form = document.getElementById('loginForm');
const email = document.getElementById('email');
const password = document.getElementById('password');

// Getting all the error divs
const errorEmail = document.getElementById('error-email');
const errorPassword = document.getElementById('error-password');

form.addEventListener('submit', function(event) {
    event.preventDefault();
    
    let isValid = true;
    
    // Perform Validation 
    
    if(email.value.trim() === '') {
        showError(errorEmail, email, 'Email is required');
        isValid = false;
    }
      if(password.value === '') {
        showError(errorPassword, password, 'Password is required');
        isValid = false;
    }
    
    // if(password.value.length <= 6) {
    //     showError(errorPassword, password, 'Password must be longer than 6 characters');
    //     isValid = false;
    // }
    
    // If all validations pass then submit the form
    if(isValid) {
        alert('Form submitted successfully!');
        form.submit(); 
    }
});

// Helper function to show error
function showError(errorDiv, inputElement, message) {
    errorDiv.innerText = message;
    errorDiv.classList.add('show');
    inputElement.classList.add('error');
}

