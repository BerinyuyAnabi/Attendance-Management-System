<?php
session_start();
header('Content-Type: application/json');

// Buffer output to catch any unexpected output
ob_start();

require_once '../db/connect_db.php';

// Clear any output that might have been generated
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
    $course_code = isset($_POST['course_code']) ? trim($_POST['course_code']) : '';
    $course_name = isset($_POST['course_name']) ? trim($_POST['course_name']) : '';
    $course_description = isset($_POST['course_description']) ? trim($_POST['course_description']) : '';
    $faculty_id = $_SESSION['user_id'];

    // Validation
    if (empty($course_code) || empty($course_name)) {
        echo json_encode([
            'success' => false,
            'message' => 'Course code and name are required'
        ]);
        exit();
    }

    // Check if course code already exists
    $stmt = $conn->prepare("SELECT course_id FROM courses WHERE course_code = ?");
    $stmt->bind_param("s", $course_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Course code already exists'
        ]);
        exit();
    }
    $stmt->close();

    // Insert course
    $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, course_description, faculty_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $course_code, $course_name, $course_description, $faculty_id);

    if ($stmt->execute()) {
        $course_id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Course created successfully!',
            'course' => [
                'course_id' => $course_id,
                'course_code' => $course_code,
                'course_name' => $course_name,
                'course_description' => $course_description
            ]
        ]);
    } else {
        throw new Exception('Failed to create course');
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
