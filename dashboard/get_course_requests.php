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
    $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : null;

    // Build query
    if ($course_id) {
        // Get requests for specific course
        $stmt = $conn->prepare("
            SELECT
                cr.*,
                u.first_name,
                u.last_name,
                u.email,
                c.course_code,
                c.course_name
            FROM course_requests cr
            JOIN attend_users u ON cr.student_id = u.user_id
            JOIN courses c ON cr.course_id = c.course_id
            WHERE c.faculty_id = ? AND cr.course_id = ?
            ORDER BY
                CASE WHEN cr.status = 'pending' THEN 0 ELSE 1 END,
                cr.requested_at DESC
        ");
        $stmt->bind_param("ii", $faculty_id, $course_id);
    } else {
        // Get all requests for faculty's courses
        $stmt = $conn->prepare("
            SELECT
                cr.*,
                u.first_name,
                u.last_name,
                u.email,
                c.course_code,
                c.course_name
            FROM course_requests cr
            JOIN attend_users u ON cr.student_id = u.user_id
            JOIN courses c ON cr.course_id = c.course_id
            WHERE c.faculty_id = ? AND cr.status = 'pending'
            ORDER BY cr.requested_at DESC
        ");
        $stmt->bind_param("i", $faculty_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }

    echo json_encode([
        'success' => true,
        'requests' => $requests
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
