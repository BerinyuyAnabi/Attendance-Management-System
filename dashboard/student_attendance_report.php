<?php
/**
 * STUDENT ATTENDANCE REPORT
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
$selected_course = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Get all courses the student is enrolled in
$courses_query = $conn->prepare("
    SELECT c.course_id, c.course_code, c.course_name,
           COUNT(DISTINCT cs.session_id) as total_sessions,
           COUNT(DISTINCT ar.attendance_id) as attended_sessions,
           ROUND((COUNT(DISTINCT ar.attendance_id) / NULLIF(COUNT(DISTINCT cs.session_id), 0)) * 100, 1) as attendance_percentage
    FROM course_enrollments ce
    JOIN courses c ON ce.course_id = c.course_id
    LEFT JOIN class_sessions cs ON c.course_id = cs.course_id
    LEFT JOIN attendance_records ar ON cs.session_id = ar.session_id AND ar.student_id = ?
    WHERE ce.student_id = ? AND ce.status = 'active'
    GROUP BY c.course_id, c.course_code, c.course_name
    ORDER BY c.course_code
");
$courses_query->bind_param("ii", $student_id, $student_id);
$courses_query->execute();
$courses = $courses_query->get_result();
$courses_data = $courses->fetch_all(MYSQLI_ASSOC);

// If a course is selected, get detailed session information
$sessions_data = [];
if ($selected_course > 0) {
    $sessions_query = $conn->prepare("
        SELECT cs.*,
               ar.status as attendance_status,
               ar.marked_at,
               CASE
                   WHEN ar.attendance_id IS NOT NULL THEN 'Attended'
                   WHEN cs.code_expires_at < NOW() THEN 'Missed'
                   ELSE 'Upcoming'
               END as session_status
        FROM class_sessions cs
        LEFT JOIN attendance_records ar ON cs.session_id = ar.session_id AND ar.student_id = ?
        WHERE cs.course_id = ?
        ORDER BY cs.session_date DESC, cs.start_time DESC
    ");
    $sessions_query->bind_param("ii", $student_id, $selected_course);
    $sessions_query->execute();
    $sessions = $sessions_query->get_result();
    $sessions_data = $sessions->fetch_all(MYSQLI_ASSOC);

    // Get selected course details
    $selected_course_info = array_filter($courses_data, fn($c) => $c['course_id'] == $selected_course);
    $selected_course_info = reset($selected_course_info);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance Report</title>
    <link rel="stylesheet" href="intern.css">
    <style>
        .container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .course-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .course-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .course-card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .course-card .course-code {
            color: #007bff;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin: 15px 0;
            position: relative;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .progress-fill.low { background: linear-gradient(90deg, #dc3545, #c82333); }
        .progress-fill.medium { background: linear-gradient(90deg, #ffc107, #e0a800); }
        .stats-inline {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }
        .sessions-table {
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
        .badge-attended { background: #d4edda; color: #155724; }
        .badge-missed { background: #f8d7da; color: #721c24; }
        .badge-upcoming { background: #cce5ff; color: #004085; }
        .attendance-status {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 13px;
        }
        .status-present { background: #d4edda; color: #155724; }
        .status-late { background: #fff3cd; color: #856404; }
        .status-absent { background: #f8d7da; color: #721c24; }
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .back-btn:hover {
            background: #5a6268;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .summary-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .summary-card h4 {
            margin: 0;
            font-size: 28px;
            color: #333;
        }
        .summary-card p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>My Attendance Report</h1>
            <p>View your attendance records for all courses</p>
        </div>

        <?php if ($selected_course > 0 && $selected_course_info): ?>
            <!-- Detailed Course View -->
            <a href="student_attendance_report.php" class="back-btn">← Back to All Courses</a>

            <div class="header">
                <h2><?php echo htmlspecialchars($selected_course_info['course_code'] . ' - ' . $selected_course_info['course_name']); ?></h2>

                <div class="summary-stats">
                    <div class="summary-card">
                        <h4><?php echo $selected_course_info['total_sessions']; ?></h4>
                        <p>Total Sessions</p>
                    </div>
                    <div class="summary-card">
                        <h4><?php echo $selected_course_info['attended_sessions']; ?></h4>
                        <p>Attended</p>
                    </div>
                    <div class="summary-card">
                        <h4><?php echo $selected_course_info['total_sessions'] - $selected_course_info['attended_sessions']; ?></h4>
                        <p>Missed</p>
                    </div>
                    <div class="summary-card">
                        <h4><?php echo $selected_course_info['attendance_percentage'] ?? 0; ?>%</h4>
                        <p>Attendance Rate</p>
                    </div>
                </div>
            </div>

            <div class="sessions-table">
                <table>
                    <thead>
                        <tr>
                            <th>Session Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Attendance</th>
                            <th>Marked At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($sessions_data) > 0): ?>
                            <?php foreach ($sessions_data as $session): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($session['session_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($session['session_date'])); ?></td>
                                    <td>
                                        <?php echo date('g:i A', strtotime($session['start_time'])); ?> -
                                        <?php echo date('g:i A', strtotime($session['end_time'])); ?>
                                    </td>
                                    <td>
                                        <span class="status-badge badge-<?php echo strtolower($session['session_status']); ?>">
                                            <?php echo $session['session_status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($session['attendance_status']): ?>
                                            <span class="attendance-status status-<?php echo $session['attendance_status']; ?>">
                                                <?php echo ucfirst($session['attendance_status']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #999;">Not Marked</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $session['marked_at'] ? date('M d, g:i A', strtotime($session['marked_at'])) : '-'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px;">
                                    No sessions found for this course.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <!-- Course Overview -->
            <h2>My Courses</h2>

            <?php if (count($courses_data) > 0): ?>
                <div class="courses-grid">
                    <?php foreach ($courses_data as $course): ?>
                        <a href="student_attendance_report.php?course_id=<?php echo $course['course_id']; ?>" class="course-card">
                            <div class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></div>
                            <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>

                            <?php
                            $percentage = $course['attendance_percentage'] ?? 0;
                            $class = 'progress-fill';
                            if ($percentage < 60) {
                                $class .= ' low';
                            } elseif ($percentage < 80) {
                                $class .= ' medium';
                            }
                            ?>

                            <div class="progress-bar">
                                <div class="<?php echo $class; ?>" style="width: <?php echo $percentage; ?>%">
                                    <?php echo $percentage; ?>%
                                </div>
                            </div>

                            <div class="stats-inline">
                                <span>Attended: <?php echo $course['attended_sessions']; ?> / <?php echo $course['total_sessions']; ?></span>
                                <span>Missed: <?php echo $course['total_sessions'] - $course['attended_sessions']; ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="header">
                    <p style="text-align: center; color: #666;">
                        You are not enrolled in any courses yet.
                    </p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="studentdashboard.php" class="back-btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
