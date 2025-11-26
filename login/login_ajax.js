function loginAjax(event) {
    // Prevent default form submission
    event.preventDefault();

    // Collect form data
    const uemail = document.getElementById("email").value;
    const upass = document.getElementById("password").value;

    // validation
    if (!uemail || !upass) {
        alert("Please fill in all fields");
        return;
    }

    // Creating a new XMLHttpRequest object
    const xhr = new XMLHttpRequest();

    // Configuring the request - POST method to login.php
    xhr.open("POST", "login.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    // Handle the response
    xhr.onload = function () {
        console.log("Response Status:", xhr.status);
        console.log("Response Text:", xhr.responseText);

        if (xhr.status >= 200 && xhr.status < 300) {
            try {
                const response = JSON.parse(xhr.responseText);
                console.log("Parsed Response:", response);

                if (response.success) {
                    console.log("Login successful! Redirecting to:", response.redirect);
                    // Redirect to the appropriate dashboard
                    window.location.href = response.redirect;
                } else {
                    // Show error message
                    console.error("Login failed:", response.message);
                    alert(response.message || "Login failed");
                }
            } catch (e) {
                console.error("Failed to parse response:", e);
                console.error("Raw response was:", xhr.responseText);
                alert("An error occurred during login");
            }
        } else {
            alert("Request failed with status: " + xhr.status);
        }
    };

    // Handle network errors
    xhr.onerror = function () {
        console.error("Network error occurred.");
        alert("Network error. Please check your connection.");
    };

    // Prepare and send the data as POST
    const data = "email=" + encodeURIComponent(uemail) + "&password=" + encodeURIComponent(upass);
    console.log("Sending request with data:", data);
    xhr.send(data);
}