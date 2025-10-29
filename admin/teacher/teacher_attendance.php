<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../../config/config.php';

$content = '
<div class="container mt-4">
    <h1>My Students Attendance</h1>
    <div class="table-responsive">
        <table class="table table-striped">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Roll No</th>
                <th>Stream</th>
                <th>Semester</th>
                <th>Photo</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
';

$result = $conn->query("SELECT s.name, s.roll_no, st.name as stream_name, sem.semester_number, s.photoUrl, a.timestamp, a.status FROM face_attendance a JOIN face_students s ON a.student_id = s.id JOIN streams st ON s.stream_id = st.id JOIN semesters sem ON s.semester_id = sem.id WHERE s.stream_id = " . (isset($_SESSION['admin_stream_id']) ? $_SESSION['admin_stream_id'] : 0) . " AND s.semester_id = " . (isset($_SESSION['admin_semester_id']) ? $_SESSION['admin_semester_id'] : 0));
while ($row = $result->fetch_assoc()) {
    $content .= '<tr>';
    $content .= '<td>' . htmlspecialchars($row['name']) . '</td>';
    $content .= '<td>' . htmlspecialchars($row['roll_no']) . '</td>';
    $content .= '<td>' . htmlspecialchars($row['stream_name']) . '</td>';
    $content .= '<td>' . $row['semester_number'] . '</td>';
    $content .= '<td>' . (!empty($row['photoUrl']) ? '<img src="' . $row['photoUrl'] . '" width="50" height="50" alt="Student Photo">' : 'No Photo') . '</td>';
    $content .= '<td>' . date('Y-m-d H:i:s', strtotime($row['timestamp'])) . '</td>';
    $content .= '<td>' . htmlspecialchars($row['status']) . '</td>';
    $content .= '</tr>';
}

$content .= '
        </tbody>
        </table>
    </div>
</div>
';

include '../layout.php';
$conn->close();
?>