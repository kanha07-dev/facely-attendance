<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'hod') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../../config/config.php';

$streamOptions = '';
$result = $conn->query("SELECT id, name FROM streams WHERE id = " . $_SESSION['admin_stream_id']);
while ($row = $result->fetch_assoc()) {
    $streamOptions .= '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
}

$content = '
<div class="container mt-4">
    <h1>Add Teacher</h1>
    <form action="hod_add_teacher.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Teacher Name:</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="stream" class="form-label">Department:</label>
            <select name="stream" id="stream" class="form-select" required>
                <option value="">Select Department</option>
                ' . $streamOptions . '
            </select>
        </div>
        <div class="mb-3">
            <label for="semester" class="form-label">Semester:</label>
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
            <label for="subjects" class="form-label">Subjects (comma-separated):</label>
            <input type="text" name="subjects" id="subjects" class="form-control" placeholder="e.g., Data Structures, Algorithms">
        </div>
        <button type="submit" class="btn btn-primary">Add Teacher</button>
    </form>
</div>
';

include '../layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $stream = trim($_POST['stream']);
    $semester = trim($_POST['semester']);
    $subjects = trim($_POST['subjects']);

    if (empty($name) || empty($username) || empty($password) || empty($stream) || empty($semester)) {
        header('Location: hod_add_teacher.php?status=error&message=' . urlencode('All fields are required.'));
        exit;
    }

    $conn->begin_transaction();

    try {
        // Get semester ID
        $stmt = $conn->prepare("SELECT id FROM semesters WHERE stream_id = ? AND semester_number = ?");
        $stmt->bind_param("ii", $stream, $semester);
        $stmt->execute();
        $stmt->bind_result($semesterId);
        $stmt->fetch();
        $stmt->close();

        // Insert subjects if provided
        $subjectIds = [];
        if (!empty($subjects)) {
            $subjectList = array_map('trim', explode(',', $subjects));
            foreach ($subjectList as $subject) {
                $stmt = $conn->prepare("INSERT IGNORE INTO subjects (stream_id, semester_id, subject_name) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $stream, $semesterId, $subject);
                $stmt->execute();
                $subjectIds[] = $conn->insert_id ? $conn->insert_id : $conn->query("SELECT id FROM subjects WHERE stream_id = $stream AND semester_id = $semesterId AND subject_name = '$subject'")->fetch_assoc()['id'];
                $stmt->close();
            }
        }

        // Insert teacher
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admin (username, password, role, stream_id) VALUES (?, ?, 'teacher', ?)");
        $stmt->bind_param("ssi", $username, $hashedPassword, $stream);
        $stmt->execute();
        $teacherId = $conn->insert_id;
        $stmt->close();

        // Assign subjects to teacher
        foreach ($subjectIds as $subjectId) {
            $stmt = $conn->prepare("INSERT INTO teachers (admin_id, stream_id, semester_id, subject_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiii", $teacherId, $stream, $semesterId, $subjectId);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        header('Location: hod_add_teacher.php?status=success');
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: hod_add_teacher.php?status=error&message=' . urlencode($e->getMessage()));
    }
    $conn->close();
}
?>