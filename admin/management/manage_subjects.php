<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../../config/config.php';

$content = '
<div class="container mt-4">
    <h1>Manage Subjects</h1>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Add Subject</h5>
                    <form action="manage_subjects.php" method="post">
                        <div class="mb-3">
                            <label for="stream" class="form-label">Stream</label>
                            <select name="stream" id="stream" class="form-select" required>
                                <option value="">Select Stream</option>
';

$result = $conn->query("SELECT id, name FROM streams");
while ($row = $result->fetch_assoc()) {
    $content .= '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
}

$content .= '
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="semester" class="form-label">Semester</label>
                            <select name="semester" id="semester" class="form-select" required>
                                <option value="">Select Semester</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="subject_name" class="form-label">Subject Name</label>
                            <input type="text" name="subject_name" id="subject_name" class="form-control" required>
                        </div>
                        <button type="submit" name="add_subject" class="btn btn-primary">Add Subject</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Existing Subjects</h5>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Stream</th>
                                <th>Semester</th>
                                <th>Subject</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
';

$result = $conn->query("SELECT s.id, s.subject_name, st.name as stream_name, sem.semester_number FROM subjects s JOIN streams st ON s.stream_id = st.id JOIN semesters sem ON s.semester_id = sem.id");
while ($row = $result->fetch_assoc()) {
    $content .= '<tr>';
    $content .= '<td>' . htmlspecialchars($row['stream_name']) . '</td>';
    $content .= '<td>' . $row['semester_number'] . '</td>';
    $content .= '<td>' . htmlspecialchars($row['subject_name']) . '</td>';
    $content .= '<td><button class="btn btn-sm btn-danger" onclick="deleteSubject(' . $row['id'] . ')">Delete</button></td>';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $streamId = $_POST['stream'];
    $semester = $_POST['semester'];
    $subjectName = trim($_POST['subject_name']);

    if (!empty($streamId) && !empty($semester) && !empty($subjectName)) {
        // Insert or get semester
        $stmt = $conn->prepare("INSERT IGNORE INTO semesters (stream_id, semester_number) VALUES (?, ?)");
        $stmt->bind_param("ii", $streamId, $semester);
        $stmt->execute();
        $semesterId = $conn->insert_id ? $conn->insert_id : $conn->query("SELECT id FROM semesters WHERE stream_id = $streamId AND semester_number = $semester")->fetch_assoc()['id'];
        $stmt->close();

        // Insert subject
        $stmt = $conn->prepare("INSERT INTO subjects (stream_id, semester_id, subject_name) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $streamId, $semesterId, $subjectName);
        if ($stmt->execute()) {
            header('Location: manage_subjects.php?success=1', true, 303);
        }
        $stmt->close();
    }
}

include '../layout.php';
$conn->close();
?>