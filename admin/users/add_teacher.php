<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../../config/config.php';

$streamOptions = '';
$query = "SELECT id, name FROM streams";

// Filter streams based on user role - HODs can only add teachers to their department
if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'hod') {
    $query .= " WHERE id = " . intval($_SESSION['admin_stream_id']);
}

$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $streamOptions .= '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
}

$content = '
<div class="container mt-4">
    <h1>Add Teacher</h1>
    ' . (isset($_GET['status']) ? ($_GET['status'] === 'success' ? '<div class="alert alert-success">Teacher added successfully!</div>' : '<div class="alert alert-danger">Error: ' . htmlspecialchars($_GET['message']) . '</div>') : '') . '
    <form action="add_teacher.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Teacher Name:</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="roll_no" class="form-label">Teacher ID:</label>
            <input type="text" name="roll_no" id="roll_no" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="stream" class="form-label">Department:</label>
            <select name="stream" id="stream" class="form-select" required ' . (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'hod' ? 'disabled' : '') . '>
                <option value="' . (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'hod' ? $_SESSION['admin_stream_id'] : '') . '">' . (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'hod' ? (isset($_SESSION['admin_stream_name']) ? $_SESSION['admin_stream_name'] : 'Your Department') : 'Select Department') . '</option>
                ' . $streamOptions . '
            </select>
            ' . (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'hod' ? '<input type="hidden" name="stream" value="' . $_SESSION['admin_stream_id'] . '">' : '') . '
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
            <label for="subjects" class="form-label">Subjects:</label>
            <select name="subjects[]" id="subjects" class="form-select" multiple style="height: 150px;">
                <option value="">Select subjects (hold Ctrl to select multiple)</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" required>
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role:</label>
            <select name="role" id="role" class="form-select" required>
                <option value="hod">HOD</option>
                <option value="teacher">Teacher</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="photo" class="form-label">Photo:</label>
            <input type="file" name="photo" id="photo" class="form-control" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Teacher</button>
    </form>
</div>
<script>
function loadSubjects() {
    const streamId = document.getElementById("stream").value;
    const semester = document.getElementById("semester").value;
    const select = document.getElementById("subjects");

    // Clear existing options except the first one
    while (select.options.length > 1) {
        select.remove(1);
    }

    if (!streamId || !semester) {
        return;
    }

    fetch("../management/get_subjects.php?stream_id=" + streamId + "&semester=" + semester)
        .then(response => response.json())
        .then(data => {
            data.forEach(subject => {
                const option = document.createElement("option");
                option.value = subject.id;
                option.textContent = subject.subject_name;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error("Error loading subjects:", error);
        });
}

document.getElementById("stream").addEventListener("change", loadSubjects);
document.getElementById("semester").addEventListener("change", loadSubjects);

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
    $stream = isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'hod' ? $_SESSION['admin_stream_id'] : trim($_POST['stream']);
    $semester = trim($_POST['semester']);
    $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);
    $photo = $_FILES['photo'];

    if (empty($name) || empty($roll_no) || empty($stream) || empty($semester) || empty($password) || empty($role)) {
        header('Location: add_teacher.php?status=error&message=' . urlencode('All fields are required.'));
        exit;
    }

    if (isset($photo) && $photo['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'img/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $sanitized_name = preg_replace('/[^a-zA-Z0-9-_.]/', '_', basename($photo['name']));
        $fileName = uniqid() . '-' . $sanitized_name;
        $uploadFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($photo['tmp_name'], $uploadFilePath)) {
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


                // Insert teacher
                 $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                 $stmt = $conn->prepare("INSERT INTO admin (username, password, role, stream_id) VALUES (?, ?, ?, ?)");
                 $stmt->bind_param("sssi", $roll_no, $hashedPassword, $role, $streamId);

                 if ($stmt->execute()) {
                     $adminId = $conn->insert_id;
                     $stmt->close();

                     // Insert teacher details
                     $defaultSubjectId = 1; // Default subject_id
                     $stmt = $conn->prepare("INSERT INTO teachers (admin_id, name, stream_id, semester_id, subject_id) VALUES (?, ?, ?, ?, ?)");
                     $stmt->bind_param("isiii", $adminId, $name, $streamId, $semesterId, $defaultSubjectId);
                     $stmt->execute();
                     $teacherId = $conn->insert_id;
                     $stmt->close();

                     // Insert subjects if provided
                     if (!empty($subjects)) {
                         foreach ($subjects as $subjectId) {
                             // Link teacher to existing subject
                             $stmt = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
                             $stmt->bind_param("ii", $teacherId, $subjectId);
                             $stmt->execute();
                             $stmt->close();
                         }
                     }

                     $conn->commit();
                     header('Location: add_teacher.php?status=success');
                     exit;
                 } else {
                     throw new Exception('Database error: ' . $stmt->error);
                 }
            } catch (Exception $e) {
                $conn->rollback();
                header('Location: add_teacher.php?status=error&message=' . urlencode($e->getMessage()));
                unlink($uploadFilePath);
                exit;
            }
        } else {
            header('Location: add_teacher.php?status=error&message=' . urlencode('Failed to upload photo.'));
            exit;
        }
    } else {
        header('Location: add_teacher.php?status=error&message=' . urlencode('Photo is required.'));
        exit;
    }
    $conn->close();
}

include '../layout.php';
?>