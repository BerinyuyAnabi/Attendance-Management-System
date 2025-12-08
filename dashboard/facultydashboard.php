<?php
require_once '../login/auth_check.php';

// Enforce faculty-only access
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'faculty' && $_SESSION['role'] !== 'faculty_intern')) {
    http_response_code(403);
    die("Access Denied: Faculty access only");
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="intern.css">
    <link rel="stylesheet" href="faculty.css">

  </head>
  <body>
    <div class="dashboard">
      <div class="top-bar">
        <div class="logo">
          <h3>Faculty Dashboard</h3>
        </div>

        
        <section>
          <ul>
            <li><a href="#courses">Course Management</a></li>
            <li><a href="#sessions">Session Overview</a></li>
            <li><a href="#attendance">Attendance Reports</a></li>
            <li><a href="#students">Student Performance</a></li>
          </ul>
        </section>
        
        <div class="search">
          <input type="text" placeholder="Search..." />
          <button>Search</button>
        </div>
        
        <div class="user">
          <img src="" alt="Faculty" />
          <div class="user_details">
            <p id="name"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></p>
            <p id="email"><i><?php echo htmlspecialchars($_SESSION['email']); ?></i></p>
            <a href="logout.php" class="logout-btn" style="display: inline-block; margin-top: 8px; padding: 6px 12px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 4px; font-size: 14px;">Logout</a>
          </div>
        </div>
      </div>

      <div class="section_details">

        <a href="create_course.php">Manage Courses</a>


        <!-- Report -->
        <div class="dashboard_content">
          <div>
            <h4>Total Courses</h4>
            <p>5</p>
          </div>
          <div>
            <h4>Active Sessions</h4>
            <p>12</p>
          </div>
          <div>
            <h4>Total Students</h4>
            <p>148</p>
          </div>
          <div>
            <h4>Avg Attendance</h4>
            <p>89%</p>
          </div>
        </div>

        <!-- Course Management Section -->
        <div class="section-header">
          <h5 id="courses">Course Management</h5>
          <button class="create">+ Create New Course</button>
        </div>

        <div class="courses">
          <div class="course1">
            <h6 id="course_code">CS 601</h6>
            <h5>Programming with Python</h5>
            <div class="details">
              <p class="students">115</p>
              <p class="sessions">45</p>
            </div>
            <div class="course-actions">
              <button class="edit">Edit</button>
            </div>
          </div>

          <div class="course1">
            <h6 id="course_code">CS 502</h6>
            <h5>Data Structures</h5>
            <div class="details">
              <p class="students">92</p>
              <p class="sessions">38</p>
            </div>
            <div class="course-actions">
              <button class="edit">Edit</button>
            </div>
          </div>

          <div class="course1">
            <h6 id="course_code">CS 403</h6>
            <h5>Web Development</h5>
            <div class="details">
              <p class="students">78</p>
              <p class="sessions">42</p>
            </div>
            <div class="course-actions">
              <button class="edit">Edit</button>
            </div>
          </div>
        </div>

        <!-- Session Overview Section -->
        <h5 id="sessions">Session Overview</h5>
        <div class="sessions" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0;">
          <p style="padding: 20px; text-align: center; color: #666;">
            To create and manage sessions, <a href="create_session.php" style="color: #4CAF50; font-weight: bold; text-decoration: underline;">click here</a>
          </p>
        </div>

        <h5 id="students">Student Performance</h5>
        <div class="performance-section" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 20px 0;">
          <p style="text-align: center; padding: 40px; color: #666;">
            Student performance tracking coming soon. Use <a href="create_session.php" style="color: #4CAF50; font-weight: bold; text-decoration: underline;">Session Management</a> to track attendance.
          </p>
        </div>

      </div>
    </div>

    <!-- Create Course Modal -->
    <div id="createCourseModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
      <div class="modal-content" style="background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 500px; border-radius: 8px;">
        <span class="close" onclick="closeCreateCourseModal()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
        <h2>Create New Course</h2>
        <form id="createCourseForm" onsubmit="submitCreateCourse(event)">
          <div style="margin: 15px 0;">
            <label for="course_code" style="display: block; margin-bottom: 5px;">Course Code *</label>
            <input type="text" id="course_code" name="course_code" required style="width: 100%; padding: 8px; box-sizing: border-box;">
          </div>
          <div style="margin: 15px 0;">
            <label for="course_name" style="display: block; margin-bottom: 5px;">Course Name *</label>
            <input type="text" id="course_name" name="course_name" required style="width: 100%; padding: 8px; box-sizing: border-box;">
          </div>
          <div style="margin: 15px 0;">
            <label for="course_description" style="display: block; margin-bottom: 5px;">Description</label>
            <textarea id="course_description" name="course_description" rows="4" style="width: 100%; padding: 8px; box-sizing: border-box;"></textarea>
          </div>
          <div style="margin-top: 20px; text-align: right;">
            <button type="button" onclick="closeCreateCourseModal()" style="background: #ccc; color: #000; border: none; padding: 10px 20px; margin-right: 10px; border-radius: 4px; cursor: pointer;">Cancel</button>
            <button type="submit" style="background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Create Course</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Requests Modal -->
    <div id="requestsModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
      <div class="modal-content" style="background-color: #fefefe; margin: 2% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 800px; border-radius: 8px; max-height: 85vh; overflow-y: auto;">
        <span class="close" onclick="closeRequestsModal()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
        <h2 id="requestsModalTitle">Course Requests</h2>
        <div id="requestsList"></div>
      </div>
    </div>

    <style>
      .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: bold;
      }
      .status-pending { background: #fff3cd; color: #856404; }
      .status-approved { background: #d4edda; color: #155724; }
      .status-rejected { background: #f8d7da; color: #721c24; }
    </style>

    <script src="faculty_dashboard.js"></script>
  </body>
</html>