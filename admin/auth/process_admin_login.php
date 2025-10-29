<?php
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        header('Location: login.php?error=' . urlencode('Username and Password are required.'));
        exit;
    }

    $stmt = $conn->prepare("SELECT id, password, role, stream_id FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashedPassword, $role, $streamId);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            session_start();
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_role'] = $role;
            $_SESSION['admin_stream_id'] = $streamId;

            // Get user name based on role
            if ($role === 'admin') {
                $_SESSION['admin_name'] = $username; // For admin, use username
            } elseif ($role === 'hod' || $role === 'teacher') {
                // Get name from teachers table
                $nameStmt = $conn->prepare("SELECT name FROM teachers WHERE admin_id = ?");
                $nameStmt->bind_param("i", $id);
                $nameStmt->execute();
                $nameStmt->bind_result($userName);
                $nameStmt->fetch();
                $_SESSION['admin_name'] = $userName ?: $username; // Fallback to username if name not found
                $nameStmt->close();

                // Get semester_id for teacher
                if ($role === 'teacher') {
                    $semesterStmt = $conn->prepare("SELECT semester_id FROM teachers WHERE admin_id = ?");
                    $semesterStmt->bind_param("i", $id);
                    $semesterStmt->execute();
                    $semesterStmt->bind_result($semesterId);
                    $semesterStmt->fetch();
                    $_SESSION['admin_semester_id'] = $semesterId;
                    $semesterStmt->close();
                }
            }

            // Get stream name if user has a stream
            if ($streamId) {
                $streamStmt = $conn->prepare("SELECT name FROM streams WHERE id = ?");
                $streamStmt->bind_param("i", $streamId);
                $streamStmt->execute();
                $streamStmt->bind_result($streamName);
                $streamStmt->fetch();
                $_SESSION['admin_stream_name'] = $streamName;
                $streamStmt->close();
            }

            header('Location: ../index.php');
            exit;
        } else {
            header('Location: login.php?error=' . urlencode('Invalid password.'));
            exit;
        }
    } else {
        header('Location: login.php?error=' . urlencode('Invalid username.'));
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>