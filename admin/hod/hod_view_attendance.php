<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'hod') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../../config/config.php';

$content = '
<div class="container mt-4">
    <h1>Department Attendance</h1>
    <div class="table-responsive">
        <table class="table table-striped">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Roll No</th>
                <th>Stream</th>
                <th>Semester</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="attendance-data">
            <!-- Data will be loaded here by JavaScript -->
        </tbody>
        </table>
    </div>
</div>
<script>
    fetch("../../public/attendance.php")
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("attendance-data");
            const streamId = ' . $_SESSION['admin_stream_id'] . ';
            let rows = "";
            for (const record of data) {
                if (record.stream_id === streamId) {
                    rows += `<tr><td>${record.name}</td><td>${record.roll_no}</td><td>${record.stream_name}</td><td>${record.semester_number}</td><td>${new Date(record.timestamp).toLocaleString()}</td><td>${record.status}</td></tr>`;
                }
            }
            tbody.innerHTML = rows;
        });
</script>
';

include '../layout.php';
?>