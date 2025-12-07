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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    $request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $faculty_id = $_SESSION['user_id'];

    if (!$request_id || !in_array($action, ['approve', 'reject'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request parameters'
        ]);
        exit();
    }

    // Verify faculty owns this course
    $stmt = $conn->prepare("
        SELECT cr.*, c.faculty_id
        FROM course_requests cr
        JOIN courses c ON cr.course_id = c.course_id
        WHERE cr.request_id = ? AND c.faculty_id = ?
    ");
    $stmt->bind_param("ii", $request_id, $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Request not found or unauthorized'
        ]);
        exit();
    }

    $request = $result->fetch_assoc();
    $stmt->close();

    // Begin transaction
    $conn->begin_transaction();

    // Update request status
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';
    $stmt = $conn->prepare("UPDATE course_requests SET status = ?, processed_at = NOW() WHERE request_id = ?");
    $stmt->bind_param("si", $new_status, $request_id);
    $stmt->execute();
    $stmt->close();

    // If approved, add student to course_student_list
    if ($action === 'approve') {
        $stmt = $conn->prepare("INSERT INTO course_student_list (student_id, course_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $request['student_id'], $request['course_id']);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => 'Request ' . $new_status . ' successfully'
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
