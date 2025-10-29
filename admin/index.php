<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';

// Get statistics based on user role
$stats = [];
if ($_SESSION['admin_role'] === 'admin') {
    $result = $conn->query("SELECT COUNT(*) as total_students FROM face_students");
    $stats['students'] = $result->fetch_assoc()['total_students'];

    $result = $conn->query("SELECT COUNT(*) as total_admins FROM admin");
    $stats['admins'] = $result->fetch_assoc()['total_admins'];
} elseif ($_SESSION['admin_role'] === 'hod') {
    $result = $conn->query("SELECT COUNT(*) as total_students FROM face_students WHERE stream_id = {$_SESSION['admin_stream_id']}");
    $stats['students'] = $result->fetch_assoc()['total_students'];
} elseif ($_SESSION['admin_role'] === 'teacher') {
    if (isset($_SESSION['admin_stream_id']) && isset($_SESSION['admin_semester_id'])) {
        $result = $conn->query("SELECT COUNT(*) as total_students FROM face_students WHERE stream_id = {$_SESSION['admin_stream_id']} AND semester_id = {$_SESSION['admin_semester_id']}");
        $stats['students'] = $result->fetch_assoc()['total_students'];

        $result = $conn->query("SELECT COUNT(*) as total_attendance FROM face_attendance a JOIN face_students s ON a.student_id = s.id WHERE s.stream_id = {$_SESSION['admin_stream_id']} AND s.semester_id = {$_SESSION['admin_semester_id']}");
        $stats['attendance'] = $result->fetch_assoc()['total_attendance'];
    } else {
        $stats['students'] = 0;
        $stats['attendance'] = 0;
    }
}

$content = '
<div class="container mt-4">
    <h1>' . (isset($_SESSION['admin_role']) ? ucfirst($_SESSION['admin_role']) : 'Admin') . ' Dashboard</h1>
    ' . (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] !== 'admin' ? '<p class="text-muted">Name: ' . (isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'User') . '</p>' : '') . '
    ' . (isset($_SESSION['admin_role']) && ($_SESSION['admin_role'] === 'hod' || $_SESSION['admin_role'] === 'teacher') ? '<p class="text-muted">Department: ' . (isset($_SESSION['admin_stream_name']) ? $_SESSION['admin_stream_name'] : 'N/A') . '</p>' : '') . '
    <div class="row">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-primary">' . ($stats['students'] ?? 0) . '</h5>
                    <p class="card-text">Total Students</p>
                </div>
            </div>
        </div>
        ' . (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin' ? '
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-warning">' . ($stats['admins'] ?? 0) . '</h5>
                    <p class="card-text">Total Admins</p>
                </div>
            </div>
        </div>
        ' : '') . '
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-info">' . ($stats['attendance'] ?? 0) . '</h5>
                    <p class="card-text">Total Attendance</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>
                    ' . (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin' ? '<a href="users/add_admin.php" class="btn btn-primary me-2">Add Admin</a>' : '') . '
                    <a href="users/add_teacher.php" class="btn btn-secondary me-2">Add Teacher</a>
                    <a href="users/add_student.php" class="btn btn-success">Add Student</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">View Data</h5>
                    <a href="users/view_students.php" class="btn btn-info me-2">View Students</a>
                    <a href="users/view_teachers.php" class="btn btn-primary me-2">View Teachers</a>
                    <a href="attendance.php" class="btn btn-warning">View Attendance</a>
                </div>
            </div>
        </div>
    </div>
</div>
';

include 'layout.php';
?>