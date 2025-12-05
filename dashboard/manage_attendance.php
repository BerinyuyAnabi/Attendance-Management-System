<?php
/**
 * MANAGE ATTENDANCE - For Faculty, Interns, and Teachers
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../db/connect_db.php';

// Check if user is authorized
$allowed_roles = ['faculty', 'faculty_intern', 'teacher'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header('Location: ../login/signin.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;

if ($session_id === 0) {
    die("Invalid session ID");
}

// Verify this session belongs to this user
$verify = $conn->prepare("SELECT * FROM class_sessions WHERE session_id = ? AND created_by = ?");
$verify->bind_param("ii", $session_id, $user_id);
$verify->execute();
$session_result = $verify->get_result();

if ($session_result->num_rows === 0) {
    die("You don't have permission to manage this session");
}

$session_info = $session_result->fetch_assoc();

//  manual attendance marking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $student_id = intval($_POST['student_id']);
    $status = $_POST['status'];

    // Check if attendance already exists
    $check = $conn->prepare("SELECT attendance_id FROM attendance_records WHERE session_id = ? AND student_id = ?");
    $check->bind_param("ii", $session_id, $student_id);
    $check->execute();
    $exists = $check->get_result();

    if ($exists->num_rows > 0) {
        // Update existing record
        $update = $conn->prepare("UPDATE attendance_records SET status = ?, marked_by = ? WHERE session_id = ? AND student_id = ?");
        $update->bind_param("siii", $status, $user_id, $session_id, $student_id);
        $update->execute();
    } else {
        // Insert new record
        $insert = $conn->prepare("INSERT INTO attendance_records (session_id, student_id, status, marked_by) VALUES (?, ?, ?, ?)");
        $insert->bind_param("iisi", $session_id, $student_id, $status, $user_id);
        $insert->execute();
    }

    header("Location: manage_attendance.php?session_id=$session_id&success=1");
    exit();
}

// Get course information
$course_query = $conn->prepare("
    SELECT c.* FROM courses c
    JOIN class_sessions cs ON c.course_id = cs.course_id
    WHERE cs.session_id = ?
");
$course_query->bind_param("i", $session_id);
$course_query->execute();
$course = $course_query->get_result()->fetch_assoc();

// Get all enrolled students and their attendance status
$students_query = $conn->prepare("
    SELECT
        u.user_id,
        u.first_name,
        u.last_name,
        u.email,
        ar.status as attendance_status,
        ar.marked_at,
        ar.marked_by,
        marker.first_name as marked_by_name
    FROM course_enrollments ce
    JOIN attend_users u ON ce.student_id = u.user_id
    LEFT JOIN attendance_records ar ON ar.session_id = ? AND ar.student_id = u.user_id
    LEFT JOIN attend_users marker ON ar.marked_by = marker.user_id
    WHERE ce.course_id = ? AND ce.status = 'active'
    ORDER BY u.last_name, u.first_name
");
$students_query->bind_param("ii", $session_id, $course['course_id']);
$students_query->execute();
$students = $students_query->get_result();

// Calculate statistics
$total_students = $students->num_rows;
$students_data = $students->fetch_all(MYSQLI_ASSOC);
$present_count = count(array_filter($students_data, fn($s) => $s['attendance_status'] === 'present'));
$absent_count = count(array_filter($students_data, fn($s) => $s['attendance_status'] === 'absent'));
$late_count = count(array_filter($students_data, fn($s) => $s['attendance_status'] === 'late'));
$not_marked = $total_students - ($present_count + $absent_count + $late_count);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance</title>
    <link rel="stylesheet" href="intern.css">
    <style>
        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        .session-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 32px;
            color: #333;
        }
        .stat-card p {
            margin: 0;
            color: #666;
        }
        .stat-present { border-left: 4px solid #28a745; }
        .stat-absent { border-left: 4px solid #dc3545; }
        .stat-late { border-left: 4px solid #ffc107; }
        .stat-not-marked { border-left: 4px solid #6c757d; }
        .attendance-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
        }
        .badge-present { background: #d4edda; color: #155724; }
        .badge-absent { background: #f8d7da; color: #721c24; }
        .badge-late { background: #fff3cd; color: #856404; }
        .badge-none { background: #e9ecef; color: #6c757d; }
        select.status-select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .code-display {
            background: #28a745;
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            font-family: monospace;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="session-header">
            <h1>Manage Attendance</h1>
            <h2><?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?></h2>
            <p>
                <strong>Session:</strong> <?php echo htmlspecialchars($session_info['session_name']); ?><br>
                <strong>Date:</strong> <?php echo date('F j, Y', strtotime($session_info['session_date'])); ?><br>
                <strong>Time:</strong> <?php echo date('g:i A', strtotime($session_info['start_time'])); ?> -
                <?php echo date('g:i A', strtotime($session_info['end_time'])); ?>
            </p>

            <div class="code-display">
                Attendance Code: <?php echo $session_info['attendance_code']; ?>
                <br>
                <small style="font-size: 14px;">Expires: <?php echo date('M d, g:i A', strtotime($session_info['code_expires_at'])); ?></small>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card stat-present">
                <h3><?php echo $present_count; ?></h3>
                <p>Present</p>
            </div>
            <div class="stat-card stat-absent">
                <h3><?php echo $absent_count; ?></h3>
                <p>Absent</p>
            </div>
            <div class="stat-card stat-late">
                <h3><?php echo $late_count; ?></h3>
                <p>Late</p>
            </div>
            <div class="stat-card stat-not-marked">
                <h3><?php echo $not_marked; ?></h3>
                <p>Not Marked</p>
            </div>
        </div>

        <!-- Student List -->
        <div class="attendance-table">
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Marked At</th>
                        <th>Marked By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students_data as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td>
                                <?php if ($student['attendance_status']): ?>
                                    <span class="status-badge badge-<?php echo $student['attendance_status']; ?>">
                                        <?php echo ucfirst($student['attendance_status']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge badge-none">Not Marked</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $student['marked_at'] ? date('M d, g:i A', strtotime($student['marked_at'])) : '-'; ?>
                            </td>
                            <td>
                                <?php
                                if ($student['marked_by_name']) {
                                    echo htmlspecialchars($student['marked_by_name']);
                                } elseif ($student['marked_by'] == $student['user_id']) {
                                    echo 'Self';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="student_id" value="<?php echo $student['user_id']; ?>">
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="">Mark as...</option>
                                        <option value="present">Present</option>
                                        <option value="late">Late</option>
                                        <option value="absent">Absent</option>
                                    </select>
                                    <input type="hidden" name="mark_attendance" value="1">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            <a href="create_session.php" class="btn btn-primary">Back to Sessions</a>
        </div>
    </div>
</body>
</html>
