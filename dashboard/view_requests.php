<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../db/connect_db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    die("Youu are not authorized to access this page.");
}

$course_id = $_GET['course_id'] ?? 0;

// Handle approve/reject
if (isset($_GET['action']) && isset($_GET['request_id'])) {
    $request_id = $_GET['request_id'];
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        // Get student and course
        $stmt = $conn->prepare("SELECT student_id, course_id FROM course_requests WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        // Add to course_student_list
        $stmt2 = $conn->prepare("INSERT INTO course_student_list (student_id, course_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $result['student_id'], $result['course_id']);
        $stmt2->execute();
        
        // Update request
        $stmt3 = $conn->prepare("UPDATE course_requests SET status = 'approved' WHERE request_id = ?");
        $stmt3->bind_param("i", $request_id);
        $stmt3->execute();
        
        echo "<script>alert('Approved!'); window.location='view_requests.php?course_id=$course_id';</script>";
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE course_requests SET status = 'rejected' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        
        echo "<script>alert('Rejected!'); window.location='view_requests.php?course_id=$course_id';</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>View Requests</title></head>
<body>
    <h2>Course Join Requests</h2>
    <a href="faculty_create_course.php">Back to Courses</a>
    
    <?php
    $stmt = $conn->prepare("
        SELECT cr.*, u.first_name, u.last_name, u.email
        FROM course_requests cr
        JOIN students s ON cr.student_id = s.student_id
        JOIN users u ON s.student_id = u.user_id
        WHERE cr.course_id = ? AND cr.status = 'pending'
    ");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>
                    <p><strong>{$row['first_name']} {$row['last_name']}</strong> ({$row['email']})</p>
                    <p>Requested: {$row['request_date']}</p>
                    <a href='?course_id=$course_id&request_id={$row['request_id']}&action=approve'>Approve</a> | 
                    <a href='?course_id=$course_id&request_id={$row['request_id']}&action=reject'>Reject</a>
                  </div>";
        }
    } else {
        echo "<p>No pending requests</p>";
    }
    ?>
</body>
</html>