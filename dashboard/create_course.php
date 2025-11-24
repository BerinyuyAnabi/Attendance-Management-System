<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../db/connect_db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    die("Youu are not authorized to access this page.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $semester = $_POST['semester'];
    $faculty_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, faculty_id, semester) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $course_code, $course_name, $faculty_id, $semester);
    
    if ($stmt->execute()) {
        echo "<script>alert('Course created!'); window.location='create_course.php';</script>";
    } else {
        echo "<script>alert('Problem with corse creation!');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Create Course</title></head>
<body>
    <h2>Create New Course</h2>
    <form method="POST">
        <input type="text" name="course_code" placeholder="Course Code" required><br>
        <input type="text" name="course_name" placeholder="Course Name" required><br>
        <input type="text" name="semester" placeholder="Semester" required><br>
        <button type="submit">Create Course</button>
    </form>
    
    <hr>
    <h3>My Courses</h3>
    <?php
    $stmt = $conn->prepare("SELECT * FROM courses WHERE faculty_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        echo "<p>{$row['course_code']} - {$row['course_name']} 
              <a href='faculty_view_requests.php?course_id={$row['course_id']}'>View Requests</a></p>";
    }
    ?>
</body>
</html>