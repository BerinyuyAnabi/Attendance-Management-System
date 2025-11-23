<?php
require_once '../login/auth_check.php';
?>



<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Faculty Intern Dashboard</title>
    <link rel="stylesheet" href="intern.css">
  </head>
  <body>
    <div class="dashboard">
      <div class="top-bar">
        <div class="logo">
          <h3>FI Dashboard</h3>
        </div>
        
        <section>
          <ul>
            <li><a href="#">Courses</a></li>
            <li><a href="#">Sessions</a></li>
            <li><a href="#">Reports</a></li>
            <li><a href="#">Students</a></li>
            <li><a href="#">Logout</a></li>
          </ul>
        </section>
        <div class="search">
          <input type="text" placeholder="Search..." />
          <button>Search</button>
        </div>
        <div class="user">
          <img src="" alt="" />
          <div class="user_details">
           <p id="name">Username</p>
          <p id="email"><i>email@ashesi.edu</i></p>
          </div>
        
        </div>
      </div>

      <div class="section_details">
        <div class="dashboard_content">
            <div>
                <h4> Courses</h4>
                <p>5</p>
                </div>
            <div>
                <h4> Sessions</h4>
                <p>5</p>
            </div>
            <div>Reports</div>
            <div>
                <h4> Students</h4>
                <p>5</p>
            </div>
        </div>
    <h5>Courses</h5>

<div class="courses">
    <div class="course1">
        <h6 id="course_code">CS 601</h6>
        <h5>Programming with Python</h6>
        <div class="details">
            <p class="students">115</p>
            <p class="sessions">45</p>
        </div>
        <button>View Details</button>
    </div>
</div>

<div class="sessions">
    <h5>Recent Sessions</h5>
    <div class="session1">
        <div class="session_details">
          <h6 id="date">Oct 10,2025</h6>
           <h5>CS101</h6>
            <p>Topic of the Session</p>
            <p>Attendance</p>
        </div>
        <button>Completed</button>
    </div>
    </div>

    <div class="sessions">
    <div class="session1">
        <div class="session_details">
          <h6 id="date">Oct 10,2025</h6>
           <h5>CS101</h6>
            <p>Topic of the Session</p>
            <p>Attendance</p>
        </div>
        <button>Completed</button>
    </div>
    </div>

        <div class="manage_students">
            <h5>Auditors and Observers</h5>
            <div class="stud_details">
                <p>Name</p>
                <p>Auditors</p>
           <h5>CS101</h6>
            <p>Date</p>
           <button>manage</button>
        </div>
    </div>
    </div>


      </div>
    </div>
  </body>
</html>
