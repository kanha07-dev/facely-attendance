<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_no = trim($_POST['roll_no']);
    $password = trim($_POST['password']);

    if (empty($roll_no) || empty($password)) {
        header('Location: login.php?error=' . urlencode('Roll No and Password are required.'));
        exit;
    }

    $stmt = $conn->prepare("SELECT id, name, password FROM face_students WHERE roll_no = ?");
    $stmt->bind_param("s", $roll_no);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            session_start();
            $_SESSION['student_id'] = $id;
            $_SESSION['student_name'] = $name;
            $_SESSION['roll_no'] = $roll_no;
            header('Location: ../public/index.php');
            exit;
        } else {
            header('Location: login.php?error=' . urlencode('Invalid password.'));
            exit;
        }
    } else {
        header('Location: login.php?error=' . urlencode('Invalid Roll No.'));
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>