<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../../config/config.php';

$content = '
<div class="container mt-4">
    <h1>View Teachers</h1>
    <div class="table-responsive">
        <table class="table table-striped">
        <thead>
            <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Stream</th>
                <th>Photo</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
';

$query = "SELECT a.id, a.username, a.role, s.name as stream_name, t.photoUrl FROM admin a LEFT JOIN streams s ON a.stream_id = s.id LEFT JOIN teachers t ON a.id = t.admin_id WHERE a.role IN ('hod', 'teacher')";

// Filter based on user role
if (isset($_SESSION['admin_role'])) {
    if ($_SESSION['admin_role'] === 'hod') {
        $query .= " AND a.stream_id = " . intval($_SESSION['admin_stream_id']);
    } elseif ($_SESSION['admin_role'] === 'teacher') {
        // Teachers shouldn't see this page, but if they do, show nothing
        $query .= " AND 1=0";
    }
}

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $content .= '<tr>';
    $content .= '<td>' . htmlspecialchars($row['username']) . '</td>';
    $content .= '<td>' . ucfirst($row['role']) . '</td>';
    $content .= '<td>' . htmlspecialchars($row['stream_name'] ?? 'N/A') . '</td>';
    $content .= '<td>' . (!empty($row['photoUrl']) ? '<img src="' . $row['photoUrl'] . '" width="50" height="50" alt="Teacher Photo">' : 'No Photo') . '</td>';
    $content .= '<td>' . (isset($_SESSION['admin_role']) && ($_SESSION['admin_role'] === 'admin' || $_SESSION['admin_role'] === 'hod') ? '<button class="btn btn-sm btn-info" onclick="editTeacher(' . $row['id'] . ')">Edit</button>' : '<span class="text-muted">No actions available</span>') . '</td>';
    $content .= '</tr>';
}

$content .= '
        </tbody>
        </table>
    </div>
</div>
<script>
function editTeacher(teacherId) {
    window.location.href = "edit_teacher.php?id=" + teacherId;
}
</script>
';

include '../layout.php';
$conn->close();
?>