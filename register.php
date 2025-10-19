<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $roll_no = trim($_POST['roll_no']);
    $stream = trim($_POST['stream']);
    $photo = $_FILES['photo'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB

    if (empty($name)) {
        header('Location: admin.php?status=error&message=' . urlencode('Student name cannot be empty.'));
        exit;
    }
    if (empty($roll_no)) {
        header('Location: admin.php?status=error&message=' . urlencode('Roll No cannot be empty.'));
        exit;
    }
    if (empty($stream)) {
        header('Location: admin.php?status=error&message=' . urlencode('Stream cannot be empty.'));
        exit;
    }

    if (isset($photo) && $photo['size'] > $maxFileSize) {
        header('Location: admin.php?status=error&message=' . urlencode('File is too large. Please upload a file smaller than 2MB.'));
        exit;
    }

    if (isset($photo) && $photo['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                header('Location: admin.php?status=error&message=Failed to create upload directory.');
                exit;
            }
        }

        $sanitized_name = preg_replace('/[^a-zA-Z0-9-_.]/', '_', basename($photo['name']));
        $fileName = uniqid() . '-' . $sanitized_name;
        $uploadFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($photo['tmp_name'], $uploadFilePath)) {
            // Check for duplicate roll number
            $stmt = $conn->prepare("SELECT id FROM face_students WHERE roll_no = ?");
            $stmt->bind_param("s", $roll_no);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                header('Location: admin.php?status=error&message=' . urlencode('A student with this Roll No already exists.'));
                unlink($uploadFilePath);
                $stmt->close();
                $conn->close();
                exit;
            }
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO face_students (name, roll_no, stream, photoUrl) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $roll_no, $stream, $uploadFilePath);

            if ($stmt->execute()) {
                header('Location: admin.php?status=success');
            } else {
                header('Location: admin.php?status=error&message=' . urlencode('Database error: ' . $stmt->error));
                unlink($uploadFilePath);
            }

            $stmt->close();
            $conn->close();
            exit;
        }
    }

    $upload_errors = [
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the server\'s maximum file size limit.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the maximum file size specified in the form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
    ];
    $error_message = isset($photo['error']) ? $upload_errors[$photo['error']] : 'Unknown upload error.';
    header('Location: admin.php?status=error&message=' . urlencode($error_message));
    exit;
}
?>