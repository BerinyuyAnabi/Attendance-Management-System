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

    // Get courses not enrolled in
    $stmt = $conn->prepare("
        SELECT
            c.*,
            u.first_name as faculty_first_name,
            u.last_name as faculty_last_name,
            CASE
                WHEN csl.student_id IS NOT NULL THEN 'enrolled'
                WHEN cr.status = 'pending' THEN 'pending'
                WHEN cr.status = 'rejected' THEN 'rejected'
                ELSE 'available'
            END as enrollment_status
        FROM courses c
        JOIN attend_users u ON c.faculty_id = u.user_id
        LEFT JOIN course_student_list csl ON c.course_id = csl.course_id AND csl.student_id = ?
        LEFT JOIN course_requests cr ON c.course_id = cr.course_id AND cr.student_id = ?
        WHERE csl.student_id IS NULL
        ORDER BY c.course_id DESC
    ");
    $stmt->bind_param("ii", $student_id, $student_id);
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
