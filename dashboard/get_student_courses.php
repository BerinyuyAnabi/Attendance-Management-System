<?php
session_start();
header('Content-Type: application/json');

ob_start();
require_once '../db/connect_db.php';
ob_clean();

// Check authorization
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

try {
    $student_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT
            c.*,
            u.first_name as faculty_first_name,
            u.last_name as faculty_last_name,
            csl.enrolled_at
        FROM course_student_list csl
        JOIN courses c ON csl.course_id = c.course_id
        JOIN attend_users u ON c.faculty_id = u.user_id
        WHERE csl.student_id = ?
        ORDER BY csl.enrolled_at DESC
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    echo json_encode([
        'success' => true,
        'courses' => $courses
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
