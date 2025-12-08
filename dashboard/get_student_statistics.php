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

    // Get total enrolled courses
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM course_student_list WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_courses = $result->fetch_assoc()['total'];
    $stmt->close();

    // Get total sessions across all enrolled courses
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT s.session_id) as total
        FROM sessions s
        JOIN course_student_list csl ON s.course_id = csl.course_id
        WHERE csl.student_id = ?
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_sessions = $result->fetch_assoc()['total'];
    $stmt->close();

    // Get attendance statistics
    $stmt = $conn->prepare("
        SELECT
            COUNT(DISTINCT s.session_id) as total_sessions,
            COUNT(DISTINCT a.attendance_id) as attended_sessions
        FROM sessions s
        JOIN course_student_list csl ON s.course_id = csl.course_id
        LEFT JOIN attendance a ON s.session_id = a.session_id AND a.student_id = ?
        WHERE csl.student_id = ?
        AND s.date <= CURDATE()
    ");
    $stmt->bind_param("ii", $student_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance_data = $result->fetch_assoc();
    $stmt->close();

    // Calculate attendance rate
    $attendance_rate = '0%';
    if ($attendance_data['total_sessions'] > 0) {
        $rate = ($attendance_data['attended_sessions'] / $attendance_data['total_sessions']) * 100;
        $attendance_rate = round($rate) . '%';
    }

    // GPA calculation (placeholder - you can implement actual GPA logic later)
    // For now, we'll just show a dash since there's no grades table
    $gpa = '-';

    echo json_encode([
        'success' => true,
        'total_courses' => $total_courses,
        'total_sessions' => $total_sessions,
        'attendance_rate' => $attendance_rate,
        'gpa' => $gpa
    ]);

    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
