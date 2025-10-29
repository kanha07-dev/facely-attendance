<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/public/index.php"><img src="/img/abit-logo.png" alt="ABIT Logo" width="30" height="30" class="d-inline-block align-text-top"> ABIT - Ajay Binay Institute of Technology</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/public/index.php">Home</a>
                </li>
                <?php
                session_start();
                if (isset($_SESSION['admin_id'])) {
                    echo '<li class="nav-item"><a class="nav-link" href="/admin/view_attendance.php">Attendance</a></li>';
                } elseif (isset($_SESSION['student_id'])) {
                    echo '<li class="nav-item"><a class="nav-link" href="/public/student_attendance.php">Attendance</a></li>';
                }
                ?>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/auth/login.php">Admin</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php
                if (isset($_SESSION['admin_id'])) {
                    echo '<li class="nav-item"><a class="nav-link" href="/admin/auth/logout.php">Logout</a></li>';
                } elseif (isset($_SESSION['student_id'])) {
                    echo '<li class="nav-item"><a class="nav-link" href="/admin/auth/logout.php">Logout</a></li>';
                } else {
                    echo '<li class="nav-item"><a class="nav-link" href="/admin/auth/login.php">Login as Admin</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="/auth/login.php">Login as Student</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
</nav>