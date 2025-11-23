// Getting form and input elements
const form = document.getElementById('loginForm');
const email = document.getElementById('email');
const password = document.getElementById('password');

// Getting all the error divs
const errorEmail = document.getElementById('error-email');
const errorPassword = document.getElementById('error-password');

form.addEventListener('submit', async function(event) {
    event.preventDefault();

    const formData = new FormData(form);
    
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
    
    try {
        const response = await fetch('login.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            alert('Login successful!');
            // Redirect or perform other actions
        }
        // direct to pages based on role
        if (result.role === 'Student') {
            window.location.href = '/Attendance-Management-System/dashboards/studentdashboard.php';
        } else if (result.role == 'Faculty'){
            window.location.href = '/Attendance-Management-System/dashboards/facultydashboard.php';
        } else if (result.role == 'Faculty Intern'){
            window.location.href = '/Attendance-Management-System/dashboards/facultydashboard.php';
        } else
    
    
    // If all validations pass then submit the form
    if(isValid) {
        alert('Form submitted successfully!');
        form.submit(); 
    }
} catch (error) {
        console.error('Error:', error);
    }
    
});

// Helper function to show error
function showError(errorDiv, inputElement, message) {
    errorDiv.innerText = message;
    errorDiv.classList.add('show');
    inputElement.classList.add('error');
}


