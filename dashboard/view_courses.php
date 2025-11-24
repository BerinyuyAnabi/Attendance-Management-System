<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../db/connect_db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    die("Youu are not authorized to access this page.");

}

$student_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html>
<head><title>My Courses</title></head>
<body>
    <h2>My Enrolled Courses</h2>
    <a href="join_course.php">Join More Courses</a>
    
    <?php
    $stmt = $conn->prepare("
        SELECT c.*, u.first_name, u.last_name
        FROM course_student_list csl
        JOIN courses c ON csl.course_id = c.course_id
        JOIN users u ON c.faculty_id = u.user_id
        WHERE csl.student_id = ?
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>
                    <h3>{$row['course_code']} - {$row['course_name']}</h3>
                    <p>Faculty: {$row['first_name']} {$row['last_name']}</p>
                    <p>Semester: {$row['semester']}</p>
                  </div>";
        }
    } else {
        echo "<p>You are not enrolled in any courses yet.</p>";
    }
    ?>
</body>
</html>