<?php


session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../db/connect_db.php';

// Check if user is a faculty, faculty_intern, or teacher
$allowed_roles = ['faculty', 'faculty_intern', 'teacher'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header('Location: ../login/signin.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Function to generate a random 6-character attendance code
function generateAttendanceCode() {
    return strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
}

// session creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_session'])) {
    $course_id = intval($_POST['course_id']);
    $topic = trim($_POST['session_name']); // Using session_name as topic
    $location = trim($_POST['location']) ?? '';
    $session_date = $_POST['session_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Validate inputs
    if (empty($topic) || empty($session_date) || empty($start_time) || empty($end_time)) {
        $error = "All fields are required.";
    } else {
        // Insert session (matching your database schema)
        $stmt = $conn->prepare("
            INSERT INTO sessions
            (course_id, topic, location, start_time, end_time, date)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssss", $course_id, $topic, $location, $start_time, $end_time, $session_date);

        if ($stmt->execute()) {
            $session_id = $conn->insert_id;
            $message = "Session created successfully! Session ID: <strong>$session_id</strong>";
        } else {
            $error = "Error creating session: " . $stmt->error;
        }
    }
}

// Fetch instructor's courses
$courses_query = $conn->prepare("SELECT course_id, course_code, course_name FROM courses WHERE faculty_id = ?");
$courses_query->bind_param("i", $user_id);
$courses_query->execute();
$courses = $courses_query->get_result();

// Fetch all sessions for courses taught by this instructor
$sessions_query = $conn->prepare("
    SELECT s.*, c.course_code, c.course_name,
           (SELECT COUNT(*) FROM attendance WHERE session_id = s.session_id) as attendance_count
    FROM sessions s
    JOIN courses c ON s.course_id = c.course_id
    WHERE c.faculty_id = ?
    ORDER BY s.date DESC, s.start_time DESC
");
$sessions_query->bind_param("i", $user_id);
$sessions_query->execute();
$sessions = $sessions_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Session</title>
    <link rel="stylesheet" href="intern.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .form-section {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .sessions-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
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
        .code-badge {
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: bold;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .status-scheduled { background: #ffc107; color: #000; }
        .status-active { background: #28a745; color: #fff; }
        .status-closed { background: #6c757d; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Class Session</h1>

        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Session Creation Form -->
        <div class="form-section">
            <h2>New Session</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="course_id">Course *</label>
                    <select name="course_id" id="course_id" required>
                        <option value="">Select a course</option>
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <option value="<?php echo $course['course_id']; ?>">
                                <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="session_name">Session Name *</label>
                    <input type="text" name="session_name" id="session_name"
                           placeholder="e.g., Week 5 - Data Structures Lecture" required>
                </div>

                <div class="form-group">
                    <label for="session_date">Date *</label>
                    <input type="date" name="session_date" id="session_date" required>
                </div>

                <div class="form-group">
                    <label for="start_time">Start Time *</label>
                    <input type="time" name="start_time" id="start_time" required>
                </div>

                <div class="form-group">
                    <label for="end_time">End Time *</label>
                    <input type="time" name="end_time" id="end_time" required>
                </div>

                <div class="form-group">
                    <label for="location">Location (Optional)</label>
                    <input type="text" name="location" id="location" placeholder="e.g., Room 301, Online">
                </div>

                <button type="submit" name="create_session" class="btn">Create Session</button>
                <a href="facultydashboard.php" style="margin-left: 10px;">Back to Dashboard</a>
            </form>
        </div>

        <!-- List of Sessions -->
        <div class="sessions-table">
            <h2 style="padding: 20px 20px 0;">My Sessions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Topic</th>
                        <th>Date & Time</th>
                        <th>Location</th>
                        <th>Attendance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sessions->num_rows > 0): ?>
                        <?php while ($session = $sessions->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($session['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($session['topic'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($session['date'])); ?><br>
                                    <?php echo date('g:i A', strtotime($session['start_time'])); ?> -
                                    <?php echo date('g:i A', strtotime($session['end_time'])); ?>
                                </td>
                                <td><?php echo htmlspecialchars($session['location'] ?? 'N/A'); ?></td>
                                <td><?php echo $session['attendance_count']; ?> students</td>
                                <td>
                                    <a href="manage_attendance.php?session_id=<?php echo $session['session_id']; ?>" style="background: #4CAF50; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; display: inline-block;">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                No sessions created yet. Create your first session above!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
