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
            <li><a href="#my-courses">My Courses</a></li>
            <li><a href="#schedule">Session Schedule</a></li>
            <li><a href="#grades">Grades/Reports</a></li>
            <li><a href="#join">Join Course</a></li>
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
            <p>4</p>
          </div>
          <div>
            <h4>Upcoming Sessions</h4>
            <p>8</p>
          </div>
          <div>
            <h4>Completed</h4>
            <p>12</p>
          </div>
          <div>
            <h4>Overall Grade</h4>
            <p>A-</p>
          </div>
        </div>

         <a href="view_courses.php">My Courses</a>
        <a href="join_course.php">Join Course</a>
        <!-- My Courses Section -->
        <h5 id="my-courses">My Courses</h5>
        <div class="courses">
          <div class="course1">
            <h6 id="course_code">CS 601</h6>
            <h5>Programming with Python</h5>
            <div class="details">
              <p class="progress">75%</p>
              <p class="grade">A</p>
            </div>
            <button>View Course</button>
          </div>

          <div class="course1">
            <h6 id="course_code">CS 403</h6>
            <h5>Web Development</h5>
            <div class="details">
              <p class="progress">85%</p>
              <p class="grade">A-</p>
            </div>
            <button>View Course</button>
          </div>
        </div>

        <!-- Session Schedule Section -->
        <h5 id="schedule">Session Schedule</h5>
        <div class="sessions">
          <div class="session1">
            <div class="session_details">
              <h6 id="date">Oct 12, 2025 - 10:00 AM</h6>
              <h5>CS 601</h5>
              <p>Advanced Python Concepts</p>
              <p>Room: Lab 204</p>
            </div>
            <button class="upcoming-session">Join Session</button>
          </div>


          <div class="session1">
            <div class="session_details">
              <h6 id="date">Oct 15, 2025 - 11:00 AM</h6>
              <h5>CS 403</h5>
              <p>React Components Workshop</p>
              <p>Room: Computer Lab 1</p>
            </div>
            <button class="upcoming-session">Join Session</button>
          </div>
        </div>

        <!-- Grades and Reports Section -->
        <h5 id="grades">Grades & Reports</h5>
        <div class="grades">
          <div class="grade">
            <div class="grade">
              <h6 id="course_code">CS 601</h6>
              <h5>Programming with Python</h5>
            </div>
            <div class="grade-content">
              <div class="grade-item">
                <p>Assignment 1</p>
                <span class="grade-value">95%</span>
              </div>
              <div class="grade-item">
                <p>Midterm Exam</p>
                <span class="grade-value">88%</span>
              </div>
              <div class="grade-item">
                <p>Project</p>
                <span class="grade-value">92%</span>
              </div>
              <div class="grade-overall">
                <p>Overall Grade</p>
                <span class="grade-value">A</span>
              </div>
            </div>
          </div>

          <div class="grade">
            <div class="grade">
              <h6 id="course_code">CS 502</h6>
              <h5>Data Structures</h5>
            </div>
            <div class="grade-content">
              <div class="grade-item">
                <p>Quiz 1</p>
                <span class="grade-value">82%</span>
              </div>
              <div class="grade-item">
                <p>Lab Work</p>
                <span class="grade-value">90%</span>
              </div>
              <div class="grade-item">
                <p>Assignment 2</p>
                <span class="grade-value">85%</span>
              </div>
              <div class="grade-overall">
                <p>Overall Grade</p>
                <span class="grade-value">B+</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Feedback Section -->
        <h5>Recent Feedback</h5>
        <div class="feedback">
          <div class="feedback">
            <div class="feedback">
              <h6>Faculty Intern - John Doe</h6>
              <span class="feedback-date">Oct 8, 2025</span>
            </div>
            <p class="feedback-course">CS 601 - Programming with Python</p>
            <p class="feedback-text">Excellent work on the recent assignment!.</p>
          </div>

        </div>

        <!-- Join Course Section -->
        <h5 id="join">Join Course as Auditor/Observer</h5>
        <div class="join-course">
          <div class="course1 join-course">
            <h6 id="course_code">CS 701</h6>
            <h5>Machine Learning Fundamentals</h5>
            <p class="course-description">Introduction to ML algorithms and applications</p>
            <div class="join-options">
              <button class="auditor">Join as Auditor</button>
              <button class="observer">Join as Observer</button>
            </div>
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
