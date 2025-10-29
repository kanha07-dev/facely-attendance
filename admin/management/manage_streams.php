<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../../config/config.php';

$content = '
<div class="container mt-4">
    <h1>Manage Streams</h1>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Add Stream</h5>
                    <form action="manage_streams.php" method="post">
                        <div class="mb-3">
                            <label for="stream_name" class="form-label">Stream Name</label>
                            <input type="text" name="stream_name" id="stream_name" class="form-control" required>
                        </div>
                        <button type="submit" name="add_stream" class="btn btn-primary">Add Stream</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Existing Streams</h5>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Stream</th>
                                <th>Semesters</th>
                                <th>Subjects</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
';

$result = $conn->query("SELECT * FROM streams");
while ($stream = $result->fetch_assoc()) {
    $content .= '<tr>';
    $content .= '<td>' . htmlspecialchars($stream['name']) . '</td>';
    $content .= '<td>';
    $semesters = $conn->query("SELECT semester_number FROM semesters WHERE stream_id = " . $stream['id']);
    while ($sem = $semesters->fetch_assoc()) {
        $content .= $sem['semester_number'] . ', ';
    }
    $content .= '</td>';
    $content .= '<td>';
    $subjects = $conn->query("SELECT subject_name FROM subjects WHERE stream_id = " . $stream['id']);
    while ($sub = $subjects->fetch_assoc()) {
        $content .= $sub['subject_name'] . ', ';
    }
    $content .= '</td>';
    $content .= '<td><button class="btn btn-sm btn-info" onclick="editStream(' . $stream['id'] . ')">Edit</button></td>';
    $content .= '</tr>';
}

$content .= '
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_stream'])) {
    $streamName = trim($_POST['stream_name']);
    if (!empty($streamName)) {
        $stmt = $conn->prepare("INSERT INTO streams (name) VALUES (?)");
        $stmt->bind_param("s", $streamName);
        if ($stmt->execute()) {
            header('Location: manage_streams.php?success=1', true, 303);
        }
        $stmt->close();
    }
}

include '../layout.php';
$conn->close();
?>