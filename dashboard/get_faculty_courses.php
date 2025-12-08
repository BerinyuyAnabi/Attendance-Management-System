<?php
session_start();
header('Content-Type: application/json');

ob_start();
require_once '../db/connect_db.php';
ob_clean();

// Check authorization
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'faculty' && $_SESSION['role'] !== 'faculty_intern')) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

try {
    $faculty_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT
            c.*,
            COUNT(DISTINCT csl.student_id) as student_count,
            COUNT(DISTINCT CASE WHEN cr.status = 'pending' THEN cr.request_id END) as pending_requests
        FROM courses c
        LEFT JOIN course_student_list csl ON c.course_id = csl.course_id
        LEFT JOIN course_requests cr ON c.course_id = cr.course_id
        WHERE c.faculty_id = ?
        GROUP BY c.course_id
        ORDER BY c.course_id DESC
    ");
    $stmt->bind_param("i", $faculty_id);
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
