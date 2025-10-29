<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: view_students.php');
    exit;
}

$studentId = intval($_GET['id']);
require_once __DIR__ . '/../../config/config.php';

// Get student data
$stmt = $conn->prepare("SELECT s.*, st.name as stream_name, sem.semester_number FROM face_students s JOIN streams st ON s.stream_id = st.id JOIN semesters sem ON s.semester_id = sem.id WHERE s.id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: view_students.php');
    exit;
}

$student = $result->fetch_assoc();
$stmt->close();

// Get student's subjects
$studentSubjects = [];
$stmt = $conn->prepare("SELECT subject_id FROM student_subjects WHERE student_id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $studentSubjects[] = $row['subject_id'];
}
$stmt->close();

// Get all streams for dropdown
$streamOptions = '';
$result = $conn->query("SELECT id, name FROM streams");
while ($row = $result->fetch_assoc()) {
    $selected = ($row['id'] == $student['stream_id']) ? 'selected' : '';
    $streamOptions .= '<option value="' . $row['id'] . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
}

$content = '
<div class="container mt-4">
    <h1>Edit Student</h1>
    ' . (isset($_GET['status']) ? ($_GET['status'] === 'success' ? '<div class="alert alert-success">Student updated successfully!</div>' : '<div class="alert alert-danger">Error: ' . htmlspecialchars($_GET['message']) . '</div>') : '') . '
    <form action="edit_student.php?id=' . $studentId . '" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Student Name:</label>
            <input type="text" name="name" id="name" class="form-control" value="' . htmlspecialchars($student['name']) . '" required>
        </div>
        <div class="mb-3">
            <label for="roll_no" class="form-label">Roll No:</label>
            <input type="text" name="roll_no" id="roll_no" class="form-control" value="' . htmlspecialchars($student['roll_no']) . '" required>
        </div>
        <div class="mb-3">
            <label for="stream" class="form-label">Stream:</label>
            <select name="stream" id="stream" class="form-select" required>
                <option value="">Select Stream</option>
                ' . $streamOptions . '
            </select>
        </div>
        <div class="mb-3">
            <label for="semester" class="form-label">Semester:</label>
            <select name="semester" id="semester" class="form-select" required>
                <option value="">Select Semester</option>
                ' . str_repeat('<option value="' . $student['semester_number'] . '" selected>' . $student['semester_number'] . '</option>', 1) . '
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
            <div class="mt-2">
                <small class="form-text text-muted">Current photo:</small>
                <img src="../' . $student['photoUrl'] . '" width="100" height="100" alt="Current Photo" class="border rounded">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Update Student</button>
        <a href="view_students.php" class="btn btn-secondary">Cancel</a>
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
                    const checked = ' . json_encode($studentSubjects) . '.includes(subject.id) ? \'checked\' : \'\';
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
    $roll_no = trim($_POST['roll_no']);
    $stream = trim($_POST['stream']);
    $semester = trim($_POST['semester']);
    $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    $password = trim($_POST['password']);
    $photo = $_FILES['photo'];

    if (empty($name) || empty($roll_no) || empty($stream) || empty($semester)) {
        header('Location: edit_student.php?id=' . $studentId . '&status=error&message=' . urlencode('All fields are required.'));
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

        // Update student
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE face_students SET name = ?, roll_no = ?, stream_id = ?, semester_id = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssiisi", $name, $roll_no, $streamId, $semesterId, $hashedPassword, $studentId);
        } else {
            $stmt = $conn->prepare("UPDATE face_students SET name = ?, roll_no = ?, stream_id = ?, semester_id = ? WHERE id = ?");
            $stmt->bind_param("ssiii", $name, $roll_no, $streamId, $semesterId, $studentId);
        }
        $stmt->execute();
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
                $stmt = $conn->prepare("UPDATE face_students SET photoUrl = ? WHERE id = ?");
                $stmt->bind_param("si", $uploadFilePath, $studentId);
                $stmt->execute();
                $stmt->close();

                // Delete old photo if it exists
                if (!empty($student['photoUrl']) && file_exists('../' . $student['photoUrl'])) {
                    unlink('../' . $student['photoUrl']);
                }
            }
        }

        // Update subjects - first remove all existing, then add selected
        $stmt = $conn->prepare("DELETE FROM student_subjects WHERE student_id = ?");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $stmt->close();

        if (!empty($subjects)) {
            foreach ($subjects as $subjectId) {
                $stmt = $conn->prepare("INSERT INTO student_subjects (student_id, subject_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $studentId, $subjectId);
                $stmt->execute();
                $stmt->close();
            }
        }

        $conn->commit();
        header('Location: edit_student.php?id=' . $studentId . '&status=success');
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: edit_student.php?id=' . $studentId . '&status=error&message=' . urlencode($e->getMessage()));
        exit;
    }
}

include '../layout.php';
?>