// Faculty Dashboard JavaScript with AJAX

document.addEventListener('DOMContentLoaded', function() {
    loadFacultyCourses();
    loadPendingRequests();
    setupCreateCourseModal();
});

// Setup Create Course Modal
function setupCreateCourseModal() {
    const createBtn = document.querySelector('.create');
    if (createBtn) {
        createBtn.addEventListener('click', showCreateCourseModal);
    }
}

function showCreateCourseModal() {
    const modal = document.getElementById('createCourseModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeCreateCourseModal() {
    const modal = document.getElementById('createCourseModal');
    if (modal) {
        modal.style.display = 'none';
        document.getElementById('createCourseForm').reset();
    }
}

// Create Course via AJAX
function submitCreateCourse(event) {
    event.preventDefault();

    const formData = new FormData(document.getElementById('createCourseForm'));
    const submitBtn = event.target.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating...';

    fetch('create_course.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeCreateCourseModal();
            loadFacultyCourses(); // Reload courses list
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while creating the course');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Course';
    });
}

// Load Faculty Courses
function loadFacultyCourses() {
    fetch('get_faculty_courses.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayFacultyCourses(data.courses);
            } else {
                console.error('Error loading courses:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function displayFacultyCourses(courses) {
    const container = document.querySelector('.courses');
    if (!container) return;

    if (courses.length === 0) {
        container.innerHTML = '<p style="padding: 20px; text-align: center;">No courses created yet. Click "+ Create New Course" to get started.</p>';
        return;
    }

    container.innerHTML = courses.map(course => `
        <div class="course1">
            <h6 id="course_code">${escapeHtml(course.course_code)}</h6>
            <h5>${escapeHtml(course.course_name)}</h5>
            <div class="details">
                <p class="students">${course.student_count} Students</p>
                ${course.pending_requests > 0 ? `<p class="requests" style="color: #ff9800;">${course.pending_requests} Pending Requests</p>` : ''}
            </div>
            <div class="course-actions">
                <button class="edit" onclick="viewCourseRequests(${course.course_id}, '${escapeHtml(course.course_name)}')">View Requests</button>
            </div>
        </div>
    `).join('');
}

// Load Pending Requests Count
function loadPendingRequests() {
    fetch('get_course_requests.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.requests.length > 0) {
                showPendingRequestsNotification(data.requests.length);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function showPendingRequestsNotification(count) {
    const header = document.querySelector('.top-bar');
    if (header && count > 0) {
        const notification = document.createElement('div');
        notification.style.cssText = 'position: fixed; top: 70px; right: 20px; background: #ff9800; color: white; padding: 10px 20px; border-radius: 5px; z-index: 1000; cursor: pointer;';
        notification.innerHTML = `${count} Pending Request${count > 1 ? 's' : ''}`;
        notification.onclick = showAllRequests;
        document.body.appendChild(notification);
    }
}

// View Course Requests
function viewCourseRequests(courseId, courseName) {
    fetch(`get_course_requests.php?course_id=${courseId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showRequestsModal(data.requests, courseName);
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while loading requests');
        });
}

function showAllRequests() {
    fetch('get_course_requests.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showRequestsModal(data.requests, 'All Pending Requests');
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function showRequestsModal(requests, title) {
    const modal = document.getElementById('requestsModal');
    const titleEl = document.getElementById('requestsModalTitle');
    const container = document.getElementById('requestsList');

    titleEl.textContent = title;

    if (requests.length === 0) {
        container.innerHTML = '<p style="padding: 20px; text-align: center;">No requests found.</p>';
    } else {
        container.innerHTML = requests.map(request => `
            <div class="request-item" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <h4 style="margin: 0 0 5px 0;">${escapeHtml(request.first_name)} ${escapeHtml(request.last_name)}</h4>
                        <p style="margin: 5px 0; color: #666;">${escapeHtml(request.email)}</p>
                        <p style="margin: 5px 0;"><strong>Course:</strong> ${escapeHtml(request.course_code)} - ${escapeHtml(request.course_name)}</p>
                        <p style="margin: 5px 0; font-size: 0.9em; color: #999;">Requested: ${new Date(request.requested_at).toLocaleDateString()}</p>
                        <p style="margin: 5px 0;"><span class="status-badge status-${request.status}">${request.status.toUpperCase()}</span></p>
                    </div>
                    ${request.status === 'pending' ? `
                    <div style="display: flex; gap: 10px;">
                        <button onclick="processRequest(${request.request_id}, 'approve')" style="background: #4CAF50; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Approve</button>
                        <button onclick="processRequest(${request.request_id}, 'reject')" style="background: #f44336; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Reject</button>
                    </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }

    modal.style.display = 'block';
}

function closeRequestsModal() {
    const modal = document.getElementById('requestsModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Process Request 
function processRequest(requestId, action) {
    if (!confirm(`Are you sure you want to ${action} this request?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('action', action);

    fetch('process_request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeRequestsModal();
            loadFacultyCourses(); // Reload courses
            loadPendingRequests(); // Reload notifications
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the request');
    });
}

// Utility function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modals when clicking outside
window.onclick = function(event) {
    const createModal = document.getElementById('createCourseModal');
    const requestsModal = document.getElementById('requestsModal');

    if (event.target === createModal) {
        closeCreateCourseModal();
    }
    if (event.target === requestsModal) {
        closeRequestsModal();
    }
}
