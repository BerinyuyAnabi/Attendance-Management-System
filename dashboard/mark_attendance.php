<?php
/**
 * MARK ATTENDANCE for Students
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../db/connect_db.php';

// Check if user is a student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login/signin.php');
    exit();
}

$student_id = $_SESSION['user_id'];
$message = '';
$error = '';
$session_info = null;

//  attendance code submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_code'])) {
    $code = strtoupper(trim($_POST['attendance_code']));

    // Find the session with this code
    $session_query = $conn->prepare("
        SELECT cs.*, c.course_code, c.course_name
        FROM class_sessions cs
        JOIN courses c ON cs.course_id = c.course_id
        WHERE cs.attendance_code = ?
    ");
    $session_query->bind_param("s", $code);
    $session_query->execute();
    $result = $session_query->get_result();

    if ($result->num_rows === 0) {
        $error = "Invalid attendance code. Please check and try again.";
    } else {
        $session = $result->fetch_assoc();
        $session_id = $session['session_id'];
        $course_id = $session['course_id'];

        // Check if code has expired
        $now = date('Y-m-d H:i:s');
        if ($now > $session['code_expires_at']) {
            $error = "This attendance code has expired. Please contact your instructor.";
        } else {
            // Check if student is enrolled in this course
            $enrollment_check = $conn->prepare("
                SELECT enrollment_id FROM course_enrollments
                WHERE student_id = ? AND course_id = ? AND status = 'active'
            ");
            $enrollment_check->bind_param("ii", $student_id, $course_id);
            $enrollment_check->execute();
            $enrollment_result = $enrollment_check->get_result();

            if ($enrollment_result->num_rows === 0) {
                $error = "You are not enrolled in this course.";
            } else {
                // Check if attendance already marked
                $attendance_check = $conn->prepare("
                    SELECT attendance_id, status FROM attendance_records
                    WHERE session_id = ? AND student_id = ?
                ");
                $attendance_check->bind_param("ii", $session_id, $student_id);
                $attendance_check->execute();
                $attendance_result = $attendance_check->get_result();

                if ($attendance_result->num_rows > 0) {
                    $existing = $attendance_result->fetch_assoc();
                    $message = "You have already marked attendance for this session as " . ucfirst($existing['status']) . ".";
                    $session_info = $session;
                } else {
                    // Determine if student is late
                    $session_start = $session['session_date'] . ' ' . $session['start_time'];
                    $status = ($now > $session_start) ? 'late' : 'present';

                    // Mark attendance
                    $mark = $conn->prepare("
                        INSERT INTO attendance_records (session_id, student_id, status, marked_by)
                        VALUES (?, ?, ?, ?)
                    ");
                    $mark->bind_param("iisi", $session_id, $student_id, $status, $student_id);

                    if ($mark->execute()) {
                        $message = "Attendance marked successfully! Status: " . ucfirst($status);
                        $session_info = $session;
                    } else {
                        $error = "Error marking attendance: " . $mark->error;
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="intern.css">
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 18px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-family: monospace;
            font-weight: bold;
        }
        .form-group input:focus {
            outline: none;
            border-color: #007bff;
        }
        .btn {
            width: 100%;
            padding: 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        .session-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .session-details h3 {
            margin: 0 0 15px 0;
            color: #333;
        }
        .session-details p {
            margin: 8px 0;
            color: #666;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .icon {
            font-size: 64px;
            text-align: center;
            margin-bottom: 20px;
        }
        .success-icon { color: #28a745; }
        .instructions {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .instructions p {
            margin: 5px 0;
            color: #004085;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1 style="text-align: center; margin-bottom: 10px;">Mark Attendance</h1>
            <p style="text-align: center; color: #666; margin-bottom: 30px;">
                Enter the attendance code provided by your instructor
            </p>

            <?php if ($message): ?>
                <div class="icon success-icon">✓</div>
                <div class="message success"><?php echo $message; ?></div>

                <?php if ($session_info): ?>
                    <div class="session-details">
                        <h3>Session Details</h3>
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($session_info['course_code'] . ' - ' . $session_info['course_name']); ?></p>
                        <p><strong>Session:</strong> <?php echo htmlspecialchars($session_info['session_name']); ?></p>
                        <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($session_info['session_date'])); ?></p>
                        <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($session_info['start_time'])); ?> - <?php echo date('g:i A', strtotime($session_info['end_time'])); ?></p>
                    </div>
                <?php endif; ?>

                <a href="mark_attendance.php" class="back-link">Mark Another Session</a>
                <a href="studentdashboard.php" class="back-link">Back to Dashboard</a>

            <?php else: ?>

                <?php if ($error): ?>
                    <div class="message error"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="instructions">
                    <p><strong>Instructions:</strong></p>
                    <p>1. Get the attendance code from your instructor</p>
                    <p>2. Enter the code below</p>
                    <p>3. Click "Submit" to mark your attendance</p>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="attendance_code">Attendance Code</label>
                        <input
                            type="text"
                            name="attendance_code"
                            id="attendance_code"
                            placeholder="XXXXXX"
                            maxlength="10"
                            required
                            autofocus
                        >
                    </div>

                    <button type="submit" class="btn">Submit Attendance</button>
                </form>

                <a href="studentdashboard.php" class="back-link">Back to Dashboard</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-uppercase input
        document.getElementById('attendance_code').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    </script>
</body>
</html>
