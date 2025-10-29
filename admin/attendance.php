<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: auth/login.php');
    exit;
}

$content = '
<div class="container mt-4">
    <h1>Attendance Log</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Roll No</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="attendance-log">
            <!-- Attendance data will be loaded here -->
        </tbody>
    </table>
</div>
<script>
    fetch("../public/attendance.php")
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("attendance-log");
            data.forEach(record => {
                const tr = document.createElement("tr");
                tr.innerHTML = `<td>${record.name}</td><td>${record.roll_no}</td><td>${new Date(record.timestamp).toLocaleString()}</td><td>${record.status}</td>`;
                tbody.appendChild(tr);
            });
        });
</script>
';

include 'layout.php';
?>