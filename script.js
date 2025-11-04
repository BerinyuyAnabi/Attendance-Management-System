// Getting form and input elements
const form = document.getElementById('signupForm');
const fname = document.getElementById('f_name');
const lname = document.getElementById('l_name');
const email = document.getElementById('email');
const password = document.getElementById('password');
const c_password = document.getElementById('c_password');

// Getting all the error divs
const errorFname = document.getElementById('error-fname');
const errorLname = document.getElementById('error-lname');
const errorEmail = document.getElementById('error-email');
const errorPassword = document.getElementById('error-password');
const errorCpassword = document.getElementById('error-cpassword');

form.addEventListener('submit', function(event) {
    event.preventDefault();
    
    let isValid = true;
    
    // Perform Validation 
    if(fname.value.trim() === '') {
        showError(errorFname, fname, 'First name is required');
        isValid = false;
    }
    
    if(lname.value.trim() === '') {
        showError(errorLname, lname, 'Last name is required');
        isValid = false;
    }
    
    if(email.value.trim() === '') {
        showError(errorEmail, email, 'Email is required');
        isValid = false;
    }
    
    if(password.value.length <= 6) {
        showError(errorPassword, password, 'Password must be longer than 6 characters');
        isValid = false;
    }

    //checking for a strong password
    
    
    if(password.value !== c_password.value) {
        showError(errorCpassword, c_password, 'Passwords do not match');
        isValid = false;
    }
    
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

