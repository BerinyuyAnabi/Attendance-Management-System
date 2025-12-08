<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../db/connect_db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login/signin.php');
    exit();
}

$student_id = $_SESSION['user_id'];

// Handle join request
if (isset($_GET['join_course'])) {
    $course_id = intval($_GET['join_course']);

    $stmt = $conn->prepare("INSERT INTO course_requests (student_id, course_id, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ii", $student_id, $course_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Request sent!'); window.location='student_join_course.php';</script>";
    } else {
        echo "<script>alert('Error or already requested!');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Join Course</title></head>
<body>
    <h2>Available Courses</h2>
    <a href="student_my_courses.php">My Courses</a>
    
    <?php
    // Get courses not enrolled in
    $stmt = $conn->prepare("
        SELECT c.*, u.first_name, u.last_name,
               csl.student_id as enrolled,
               cr.status as request_status
        FROM courses c
        JOIN attend_users u ON c.faculty_id = u.user_id
        LEFT JOIN course_student_list csl ON c.course_id = csl.course_id AND csl.student_id = ?
        LEFT JOIN course_requests cr ON c.course_id = cr.course_id AND cr.student_id = ? AND cr.status = 'pending'
        WHERE csl.student_id IS NULL
    ");
    $stmt->bind_param("ii", $student_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>
                <h3>{$row['course_code']} - {$row['course_name']}</h3>
                <p>Faculty: {$row['first_name']} {$row['last_name']}</p>";
        
        if ($row['request_status'] === 'pending') {
            echo "<button disabled>Pending Approval</button>";
        } else {
            echo "<a href='?join_course={$row['course_id']}'><button>Request to Join</button></a>";
        }
        
        echo "</div>";
    }
    ?>
</body>
</html>