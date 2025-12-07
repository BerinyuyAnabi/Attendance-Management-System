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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

try {
    $student_id = $_SESSION['user_id'];
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

    if (!$course_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid course ID'
        ]);
        exit();
    }

    // Check if course exists
    $stmt = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Course not found'
        ]);
        exit();
    }
    $stmt->close();

    // Check if already enrolled
    $stmt = $conn->prepare("SELECT enrollment_id FROM course_student_list WHERE student_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $student_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'You are already enrolled in this course'
        ]);
        exit();
    }
    $stmt->close();

    // Check if request already exists
    $stmt = $conn->prepare("SELECT request_id, status FROM course_requests WHERE student_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $student_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $existing = $result->fetch_assoc();
        if ($existing['status'] === 'pending') {
            echo json_encode([
                'success' => false,
                'message' => 'You have already requested to join this course'
            ]);
            exit();
        }
    }
    $stmt->close();

    // Create new request
    $stmt = $conn->prepare("INSERT INTO course_requests (student_id, course_id, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ii", $student_id, $course_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Course request submitted successfully! Waiting for faculty approval.'
        ]);
    } else {
        throw new Exception('Failed to submit request');
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
