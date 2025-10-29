<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$content = '
<div class="container mt-4">
    <h1>Add Admin</h1>
    ' . (isset($_GET['status']) ? ($_GET['status'] === 'success' ? '<div class="alert alert-success">Admin added successfully!</div>' : '<div class="alert alert-danger">Error: ' . htmlspecialchars($_GET['message']) . '</div>') : '') . '
    <form action="add_admin.php" method="post">
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" name="username" id="username" class="form-control" required>
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
                <option value="admin">Admin</option>
                <option value="hod">HOD</option>
                <option value="teacher">Teacher</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="stream" class="form-label">Stream (for HOD and Teacher):</label>
            <select name="stream" id="stream" class="form-select">
                <option value="">Select Stream</option>
                ' . getStreamOptions() . '
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Admin</button>
    </form>
</div>
';

$content .= '
<script>
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

include '../layout.php';

function getStreamOptions() {
    global $conn;
    $options = '';
    $result = $conn->query("SELECT id, name FROM streams");
    while ($row = $result->fetch_assoc()) {
        $options .= '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
    }
    return $options;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);
    $streamId = !empty($_POST['stream']) ? trim($_POST['stream']) : null;

    if (empty($username) || empty($password) || empty($role)) {
        header('Location: add_admin.php?status=error&message=' . urlencode('Username, password, and role are required.'));
        exit;
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header('Location: add_admin.php?status=error&message=' . urlencode('Username already exists.'));
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO admin (username, password, role, stream_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $hashedPassword, $role, $streamId);

    if ($stmt->execute()) {
        header('Location: add_admin.php?status=success');
    } else {
        header('Location: add_admin.php?status=error&message=' . urlencode('Error adding admin: ' . $conn->error));
    }

    $stmt->close();
    $conn->close();
}
?>