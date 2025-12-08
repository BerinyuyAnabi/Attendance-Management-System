<?php
require_once '../login/auth_check.php';

// Enforce student-only access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    die("Access Denied: Student access only");
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="intern.css">
  </head>
  <body>
    <div class="dashboard">
      <div class="top-bar">
        <div class="logo">
          <h3>Student Dashboard</h3>
        </div>
        
        <section>
          <ul>
            <li><a href="#my-courses" onclick="showSection('my-courses'); return false;">My Courses</a></li>
            <li><a href="#mark-attendance" onclick="showSection('mark-attendance'); return false;">Mark Attendance</a></li>
            <li><a href="#grades" onclick="showSection('grades'); return false;">Attendance Report</a></li>
            <li><a href="#join" onclick="openJoinCourseModal(); return false;">Join Course</a></li>
          </ul>
        </section>
        
        <div class="search">
          <input type="text" placeholder="Search courses..." />
          <button>Search</button>
        </div>
        
        <div class="user">
          <img src="" alt="User Avatar" />
          <div class="user_details">
            <p id="name"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></p>
            <p id="email"><i><?php echo htmlspecialchars($_SESSION['email']); ?></i></p>
            <a href="logout.php" class="logout-btn" style="display: inline-block; margin-top: 8px; padding: 6px 12px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 4px; font-size: 14px;">Logout</a>
          </div>
        </div>
      </div>

      <div class="section_details">
        <!-- Dashboard Statistics -->
        <div class="dashboard_content">
          <div>
            <h4>My Courses</h4>
            <p id="stat-courses">0</p>
          </div>
          <div>
            <h4>Total Sessions</h4>
            <p id="stat-sessions">0</p>
          </div>
          <div>
            <h4>Attendance Rate</h4>
            <p id="stat-attendance">0%</p>
          </div>
          <div>
            <h4>GPA</h4>
            <p id="stat-gpa">-</p>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div style="margin: 20px 0; padding: 15px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
          <button onclick="showSection('my-courses')" style="margin: 5px; padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">My Courses</button>
          <button onclick="showSection('mark-attendance')" style="margin: 5px; padding: 10px 20px; background: #2196F3; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">Mark Attendance</button>
          <button onclick="showSection('grades')" style="margin: 5px; padding: 10px 20px; background: #FF9800; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">Attendance Report</button>
          <button onclick="openJoinCourseModal()" style="margin: 5px; padding: 10px 20px; background: #9C27B0; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">Join Course</button>
        </div>

        <!-- My Courses Section -->
        <div id="section-my-courses" class="dashboard-section">
          <h5 id="my-courses">My Courses</h5>
          <div class="courses" id="coursesList">
            <p style="text-align: center; color: #666; padding: 20px;">Loading courses...</p>
          </div>
        </div>

        <!-- Mark Attendance Section -->
        <div id="section-mark-attendance" class="dashboard-section" style="display: none;">
          <h5 id="mark-attendance">Mark Attendance</h5>
          <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px;">
            <p style="color: #666; margin-bottom: 15px;">Select a course and enter the session ID to mark your attendance.</p>

            <div style="margin-bottom: 15px;">
              <label for="attendance-course" style="display: block; margin-bottom: 5px; font-weight: bold;">Select Course:</label>
              <select id="attendance-course" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                <option value="">-- Select a course --</option>
              </select>
            </div>

            <div style="margin-bottom: 15px;">
              <label for="session-id" style="display: block; margin-bottom: 5px; font-weight: bold;">Session ID:</label>
              <input type="text" id="session-id" placeholder="Enter session ID" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>

            <button onclick="markAttendance()" style="background: #4CAF50; color: white; border: none; padding: 12px 24px; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%;">Submit Attendance</button>

            <div id="attendance-message" style="margin-top: 15px; padding: 10px; border-radius: 4px; display: none;"></div>
          </div>

          <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h6 style="margin-bottom: 15px;">How to Mark Attendance:</h6>
            <ol style="color: #666; line-height: 1.8;">
              <li>Select your course from the dropdown</li>
              <li>Get the Session ID from your instructor</li>
              <li>Enter the Session ID and click Submit</li>
            </ol>
          </div>
        </div>

        <!-- Attendance Report Section -->
        <div id="section-grades" class="dashboard-section" style="display: none;">
          <h5 id="grades">Attendance Report</h5>
          <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
            <p style="color: #666; margin-bottom: 15px;">View your detailed attendance records and reports</p>
            <a href="student_attendance_report.php" style="display: inline-block; background: #2196F3; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-size: 16px;">View Full Report</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Join Course Modal -->
    <div id="joinCourseModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
      <div class="modal-content" style="background-color: #fefefe; margin: 2% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 800px; border-radius: 8px; max-height: 85vh; overflow-y: auto;">
        <span class="close" onclick="closeJoinCourseModal()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
        <h2>Available Courses</h2>
        <p style="color: #666;">Browse and request to join courses below. Faculty will review your request.</p>
        <div id="availableCoursesList" style="margin-top: 20px;"></div>
      </div>
    </div>

    <script src="student_dashboard.js"></script>
  </body>
</html>
