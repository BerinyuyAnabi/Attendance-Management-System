// Student Dashboard JavaScript with AJAX

document.addEventListener('DOMContentLoaded', function() {
    loadStudentCourses();
    setupJoinCourseButton();
    loadStatistics();
    populateAttendanceCourseDropdown();
});

// Setup Join Course Button
function setupJoinCourseButton() {
    const joinLinks = document.querySelectorAll('a[href="#join"], a[href="join_course.php"]');
    joinLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            showJoinCourseModal();
        });
    });
}

function openJoinCourseModal() {
    loadAvailableCourses();
    const modal = document.getElementById('joinCourseModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function showJoinCourseModal() {
    openJoinCourseModal();
}

function closeJoinCourseModal() {
    const modal = document.getElementById('joinCourseModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Load Student's Enrolled Courses
function loadStudentCourses() {
    fetch('get_student_courses.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayStudentCourses(data.courses);
                updateCoursesCount(data.courses.length);
            } else {
                console.error('Error loading courses:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function displayStudentCourses(courses) {
    const container = document.getElementById('coursesList');
    if (!container) return;

    if (courses.length === 0) {
        container.innerHTML = '<p style="padding: 20px; text-align: center;">You are not enrolled in any courses yet. <a href="#join" onclick="openJoinCourseModal(); return false;" style="color: #4CAF50;">Join a course</a> to get started!</p>';
        return;
    }

    container.innerHTML = courses.map(course => `
        <div class="course1">
            <h6 id="course_code">${escapeHtml(course.course_code)}</h6>
            <h5>${escapeHtml(course.course_name)}</h5>
            ${course.description ? `<p style="font-size: 0.9em; color: #666; margin: 10px 0;">${escapeHtml(course.description)}</p>` : ''}
            <p style="font-size: 0.85em; color: #999;">Faculty: ${escapeHtml(course.faculty_first_name)} ${escapeHtml(course.faculty_last_name)}</p>
            <p style="font-size: 0.85em; color: #999;">Enrolled: ${new Date(course.enrolled_at).toLocaleDateString()}</p>
            <button onclick="showSection('mark-attendance')">Mark Attendance</button>
        </div>
    `).join('');
}

function updateCoursesCount(count) {
    const countElement = document.getElementById('stat-courses');
    if (countElement) {
        countElement.textContent = count;
    }
}

// Load Available Courses for Joining
function loadAvailableCourses() {
    const container = document.getElementById('availableCoursesList');
    if (!container) return;

    container.innerHTML = '<p style="text-align: center; padding: 20px;">Loading courses...</p>';

    fetch('get_available_courses.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAvailableCourses(data.courses);
            } else {
                container.innerHTML = `<p style="text-align: center; padding: 20px; color: red;">${escapeHtml(data.message)}</p>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<p style="text-align: center; padding: 20px; color: red;">An error occurred while loading courses.</p>';
        });
}

function displayAvailableCourses(courses) {
    const container = document.getElementById('availableCoursesList');
    if (!container) return;

    if (courses.length === 0) {
        container.innerHTML = '<p style="text-align: center; padding: 20px;">No courses available to join at the moment.</p>';
        return;
    }

    container.innerHTML = courses.map(course => `
        <div class="course-item" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">
            <div style="display: flex; justify-content: space-between; align-items: start;">
                <div style="flex: 1;">
                    <h4 style="margin: 0 0 5px 0;">${escapeHtml(course.course_code)} - ${escapeHtml(course.course_name)}</h4>
                    ${course.course_description ? `<p style="margin: 5px 0; color: #666;">${escapeHtml(course.course_description)}</p>` : ''}
                    <p style="margin: 5px 0; color: #999; font-size: 0.9em;">Faculty: ${escapeHtml(course.faculty_first_name)} ${escapeHtml(course.faculty_last_name)}</p>
                </div>
                <div style="margin-left: 15px;">
                    ${getEnrollmentButton(course)}
                </div>
            </div>
        </div>
    `).join('');
}

function getEnrollmentButton(course) {
    switch (course.enrollment_status) {
        case 'pending':
            return '<button disabled style="background: #ff9800; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: not-allowed;">Pending Approval</button>';
        case 'rejected':
            return '<button disabled style="background: #f44336; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: not-allowed;">Request Rejected</button>';
        default:
            return `<button onclick="requestCourse(${course.course_id})" style="background: #4CAF50; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Request to Join</button>`;
    }
}

// Request to Join Course
function requestCourse(courseId) {
    if (!confirm('Are you sure you want to request to join this course?')) {
        return;
    }

    const formData = new FormData();
    formData.append('course_id', courseId);

    fetch('request_course.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            loadAvailableCourses(); // Reload available courses
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting your request');
    });
}

// Utility function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Section Navigation
function showSection(sectionName) {
    // Hide all sections
    const sections = document.querySelectorAll('.dashboard-section');
    sections.forEach(section => {
        section.style.display = 'none';
    });

    // Show selected section
    const targetSection = document.getElementById('section-' + sectionName);
    if (targetSection) {
        targetSection.style.display = 'block';
    }
}

// Load Statistics
function loadStatistics() {
    fetch('get_student_statistics.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('stat-courses').textContent = data.total_courses || 0;
                document.getElementById('stat-sessions').textContent = data.total_sessions || 0;
                document.getElementById('stat-attendance').textContent = data.attendance_rate || '0%';
                document.getElementById('stat-gpa').textContent = data.gpa || '-';
            }
        })
        .catch(error => {
            console.error('Error loading statistics:', error);
        });
}

// Populate Course Dropdown for Attendance
function populateAttendanceCourseDropdown() {
    fetch('get_student_courses.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.courses.length > 0) {
                const dropdown = document.getElementById('attendance-course');
                if (dropdown) {
                    dropdown.innerHTML = '<option value="">-- Select a course --</option>' +
                        data.courses.map(course =>
                            `<option value="${course.course_id}">${escapeHtml(course.course_code)} - ${escapeHtml(course.course_name)}</option>`
                        ).join('');
                }
            }
        })
        .catch(error => {
            console.error('Error loading courses for dropdown:', error);
        });
}

// Mark Attendance Function
function markAttendance() {
    const courseId = document.getElementById('attendance-course').value;
    const sessionId = document.getElementById('session-id').value.trim();
    const messageDiv = document.getElementById('attendance-message');

    // Clear previous message
    messageDiv.style.display = 'none';
    messageDiv.textContent = '';

    // Validation
    if (!courseId) {
        messageDiv.textContent = 'Please select a course';
        messageDiv.style.background = '#ffebee';
        messageDiv.style.color = '#c62828';
        messageDiv.style.display = 'block';
        return;
    }

    if (!sessionId) {
        messageDiv.textContent = 'Please enter a session ID';
        messageDiv.style.background = '#ffebee';
        messageDiv.style.color = '#c62828';
        messageDiv.style.display = 'block';
        return;
    }

    // Submit attendance
    const formData = new FormData();
    formData.append('code', sessionId);

    fetch('mark_attendance.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.textContent = data.message || 'Attendance marked successfully!';
            messageDiv.style.background = '#e8f5e9';
            messageDiv.style.color = '#2e7d32';
            messageDiv.style.display = 'block';

            // Clear form
            document.getElementById('session-id').value = '';

            // Reload statistics
            loadStatistics();
        } else {
            messageDiv.textContent = data.message || 'Failed to mark attendance';
            messageDiv.style.background = '#ffebee';
            messageDiv.style.color = '#c62828';
            messageDiv.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageDiv.textContent = 'An error occurred. Please try again.';
        messageDiv.style.background = '#ffebee';
        messageDiv.style.color = '#c62828';
        messageDiv.style.display = 'block';
    });
}

// Close modals when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('joinCourseModal');
    if (event.target === modal) {
        closeJoinCourseModal();
    }
}
