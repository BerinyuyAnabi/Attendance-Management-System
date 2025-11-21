<?php 

// Including the the database connection file
require_once '/Applications/MAMP/htdocs/Attendance-Management-System/login/auth.php';
checkRole('admin');
?> 

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>
    <div class="header">
        <h1>Welcome, <?php echo htmlspecialchars($current_user_name); ?>!</h1>
        <p>Role: <?php echo htmlspecialchars($current_user_role); ?></p>
        <a href="../logout.php">Logout</a>
    </div>
    
    <!-- HTML for admin -->
    
</body>
</html>