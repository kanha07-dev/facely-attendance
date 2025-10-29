<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: view_teachers.php');
    exit;
}

$teacherId = intval($_GET['id']);
require_once __DIR__ . '/../../config/config.php';

// Get teacher data
$stmt = $conn->prepare("SELECT a.*, t.name as teacher_name FROM admin a LEFT JOIN teachers t ON a.id = t.admin_id WHERE a.id = ? AND a.role IN ('hod', 'teacher')");
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: view_teachers.php');
    exit;
}

$teacher = $result->fetch_assoc();
$stmt->close();

// Get teacher's subjects
$teacherSubjects = [];
$stmt = $conn->prepare("SELECT ts.subject_id FROM teacher_subjects ts JOIN teachers t ON ts.teacher_id = t.id WHERE t.admin_id = ?");
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $teacherSubjects[] = $row['subject_id'];
}
$stmt->close();

// Get all streams for dropdown
$streamOptions = '';
$result = $conn->query("SELECT id, name FROM streams");
while ($row = $result->fetch_assoc()) {
    $selected = ($row['id'] == $teacher['stream_id']) ? 'selected' : '';
    $streamOptions .= '<option value="' . $row['id'] . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
}

$content = '
<div class="container mt-4">
    <h1>Edit Teacher</h1>
    ' . (isset($_GET['status']) ? ($_GET['status'] === 'success' ? '<div class="alert alert-success">Teacher updated successfully!</div>' : '<div class="alert alert-danger">Error: ' . htmlspecialchars($_GET['message']) . '</div>') : '') . '
    <form action="edit_teacher.php?id=' . $teacherId . '" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Teacher Name:</label>
            <input type="text" name="name" id="name" class="form-control" value="' . htmlspecialchars($teacher['teacher_name'] ?? '') . '" required>
        </div>
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" name="username" id="username" class="form-control" value="' . htmlspecialchars($teacher['username']) . '" required>
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
            <label class="form-label">Subjects:</label>
            <div id="subjects-container">
                <p class="text-muted">Select a stream and semester to load subjects.</p>
            </div>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role:</label>
            <select name="role" id="role" class="form-select" required>
                <option value="hod" ' . ($teacher['role'] === 'hod' ? 'selected' : '') . '>HOD</option>
                <option value="teacher" ' . ($teacher['role'] === 'teacher' ? 'selected' : '') . '>Teacher</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">New Password (leave empty to keep current):</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
        <div class="mb-3">
            <label for="photo" class="form-label">New Photo (leave empty to keep current):</label>
            <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
            <small class="form-text text-muted">Current photo: No Photo</small>
        </div>
        <button type="submit" class="btn btn-primary">Update Teacher</button>
        <a href="view_teachers.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<script>
function loadSubjects() {
    const streamId = document.getElementById("stream").value;
    const semester = document.getElementById("semester").value;
    const container = document.getElementById("subjects-container");

    if (!streamId || !semester) {
        container.innerHTML = \'<p class="text-muted">Select a stream and semester to load subjects.</p>\';
        return;
    }

    fetch("../management/get_subjects.php?stream_id=" + streamId + "&semester=" + semester)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                container.innerHTML = \'<p class="text-muted">No subjects found for this stream and semester.</p>\';
            } else {
                let html = \'\';
                data.forEach(subject => {
                    const checked = ' . json_encode($teacherSubjects) . '.includes(subject.id) ? \'checked\' : \'\';
                    html += `<div class="form-check">
                        <input class="form-check-input" type="checkbox" name="subjects[]" value="${subject.id}" id="subject_${subject.id}" ${checked}>
                        <label class="form-check-label" for="subject_${subject.id}">
                            ${subject.subject_name}
                        </label>
                    </div>`;
                });
                container.innerHTML = html;
            }
        })
        .catch(error => {
            console.error("Error loading subjects:", error);
            container.innerHTML = \'<p class="text-danger">Error loading subjects.</p>\';
        });
}

document.getElementById("stream").addEventListener("change", loadSubjects);
document.getElementById("semester").addEventListener("change", loadSubjects);

// Load subjects on page load
loadSubjects();

document.getElementById("togglePassword").addEventListener("click", function() {
    const passwordInput = document.getElementById("password");
    const icon = this.querySelector("i");
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
});
</script>
';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST request before including layout
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $stream = trim($_POST['stream']);
    $semester = trim($_POST['semester']);
    $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);
    $photo = $_FILES['photo'];

    if (empty($name) || empty($username) || empty($stream) || empty($semester) || empty($role)) {
        header('Location: edit_teacher.php?id=' . $teacherId . '&status=error&message=' . urlencode('All fields are required.'));
        exit;
    }

    $conn->begin_transaction();

    try {
        // Get stream ID
        $stmt = $conn->prepare("SELECT id FROM streams WHERE id = ?");
        $stmt->bind_param("i", $stream);
        $stmt->execute();
        $stmt->bind_result($streamId);
        $stmt->fetch();
        $stmt->close();

        // Insert or get semester
        $stmt = $conn->prepare("INSERT IGNORE INTO semesters (stream_id, semester_number) VALUES (?, ?)");
        $stmt->bind_param("ii", $streamId, $semester);
        $stmt->execute();
        $semesterId = $conn->insert_id ? $conn->insert_id : $conn->query("SELECT id FROM semesters WHERE stream_id = $streamId AND semester_number = $semester")->fetch_assoc()['id'];
        $stmt->close();

        // Update admin table
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin SET username = ?, password = ?, role = ?, stream_id = ? WHERE id = ?");
            $stmt->bind_param("sssii", $username, $hashedPassword, $role, $streamId, $teacherId);
        } else {
            $stmt = $conn->prepare("UPDATE admin SET username = ?, role = ?, stream_id = ? WHERE id = ?");
            $stmt->bind_param("ssii", $username, $role, $streamId, $teacherId);
        }
        $stmt->execute();
        $stmt->close();

        // Update teacher details
        $defaultSubjectId = 1; // Use a default subject ID or get the first available subject
        $stmt = $conn->prepare("SELECT id FROM subjects LIMIT 1");
        $stmt->execute();
        $stmt->bind_result($defaultSubjectId);
        $stmt->fetch();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE teachers SET name = ?, stream_id = ?, semester_id = ?, subject_id = ? WHERE admin_id = ?");
        $stmt->bind_param("siiii", $name, $streamId, $semesterId, $defaultSubjectId, $teacherId);
        $stmt->execute();
        $stmt->close();

        // Get the teacher table ID
        $stmt = $conn->prepare("SELECT id FROM teachers WHERE admin_id = ?");
        $stmt->bind_param("i", $teacherId);
        $stmt->execute();
        $stmt->bind_result($teacherTableId);
        $stmt->fetch();
        $stmt->close();

        // Handle photo upload if provided
        if (isset($photo) && $photo['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'img/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $sanitized_name = preg_replace('/[^a-zA-Z0-9-_.]/', '_', basename($photo['name']));
            $fileName = uniqid() . '-' . $sanitized_name;
            $uploadFilePath = $uploadDir . $fileName;

            if (move_uploaded_file($photo['tmp_name'], $uploadFilePath)) {
                // Update photo URL
                $stmt = $conn->prepare("UPDATE teachers SET photoUrl = ? WHERE id = ?");
                $stmt->bind_param("si", $uploadFilePath, $teacherTableId);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Update subjects - first remove all existing, then add selected
        $stmt = $conn->prepare("DELETE FROM teacher_subjects WHERE teacher_id = ?");
        $stmt->bind_param("i", $teacherTableId);
        $stmt->execute();
        $stmt->close();

        if (!empty($subjects)) {
            foreach ($subjects as $subjectId) {
                $stmt = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $teacherTableId, $subjectId);
                $stmt->execute();
                $stmt->close();
            }
        }

        $conn->commit();
        header('Location: edit_teacher.php?id=' . $teacherId . '&status=success');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: edit_teacher.php?id=' . $teacherId . '&status=error&message=' . urlencode($e->getMessage()));
        exit;
    }
}

include '../layout.php';
?>