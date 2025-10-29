<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - ABIT</title>
    <link rel="icon" href="../img/abit-logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            position: fixed;
            width: 250px;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="sidebar d-none d-lg-block">
        <h4 class="text-center">Admin Panel</h4>
        <a href="/admin/index.php">Dashboard</a>
        <?php
        if ($_SESSION['admin_role'] === 'admin') {
            echo '<a href="/admin/management/manage_streams.php">Manage Streams</a>';
            echo '<a href="/admin/management/manage_subjects.php">Manage Subjects</a>';
            echo '<a href="/admin/users/add_admin.php">Add Admin</a>';
            echo '<a href="/admin/users/add_teacher.php">Add Teacher</a>';
            echo '<a href="/admin/users/add_student.php">Add Student</a>';
            echo '<a href="/admin/users/view_students.php">Students</a>';
            echo '<a href="/admin/users/view_teachers.php">Teachers</a>';
            echo '<a href="/admin/attendance.php">Attendance Log</a>';
        } elseif ($_SESSION['admin_role'] === 'hod') {
            echo '<a href="/admin/users/add_teacher.php">Add Teacher</a>';
            echo '<a href="/admin/users/add_student.php">Add Student</a>';
            echo '<a href="/admin/users/view_students.php">View Students</a>';
            echo '<a href="/admin/users/view_teachers.php">View Teachers</a>';
            echo '<a href="/admin/hod/hod_view_attendance.php">Department Attendance</a>';
        } elseif ($_SESSION['admin_role'] === 'teacher') {
            echo '<a href="/admin/teacher/teacher_students.php">My Students</a>';
            echo '<a href="/admin/teacher/teacher_attendance.php">My Attendance</a>';
        }
        ?>
        <a href="/admin/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
        <div class="offcanvas offcanvas-start d-lg-none" id="sidebarOffcanvas" tabindex="-1">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title">Admin Panel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <a href="/admin/index.php">Dashboard</a>
                <?php
                if ($_SESSION['admin_role'] === 'admin') {
                    echo '<a href="/admin/management/manage_streams.php">Manage Streams</a>';
                    echo '<a href="/admin/management/manage_subjects.php">Manage Subjects</a>';
                    echo '<a href="/admin/users/add_admin.php">Add Admin</a>';
                    echo '<a href="/admin/users/add_teacher.php">Add Teacher</a>';
                    echo '<a href="/admin/users/add_student.php">Add Student</a>';
                    echo '<a href="/admin/users/view_students.php">Students</a>';
                    echo '<a href="/admin/users/view_teachers.php">Teachers</a>';
                    echo '<a href="/admin/attendance.php">Attendance Log</a>';
                } elseif ($_SESSION['admin_role'] === 'hod') {
                    echo '<a href="/admin/users/add_teacher.php">Add Teacher</a>';
                    echo '<a href="/admin/users/add_student.php">Add Student</a>';
                    echo '<a href="/admin/users/view_students.php">View Students</a>';
                    echo '<a href="/admin/users/view_teachers.php">View Teachers</a>';
                    echo '<a href="/admin/hod/hod_view_attendance.php">Department Attendance</a>';
                } elseif ($_SESSION['admin_role'] === 'teacher') {
                    echo '<a href="/admin/teacher/teacher_students.php">My Students</a>';
                    echo '<a href="/admin/teacher/teacher_attendance.php">My Attendance</a>';
                }
                ?>
                <a href="/admin/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <button class="btn btn-primary d-lg-none mb-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">Menu</button>
        <div class="main-content">
        <?php echo $content; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>