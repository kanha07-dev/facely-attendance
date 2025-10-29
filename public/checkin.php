<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'];

    if ($name) {
        // Get student ID from name
        $stmt = $conn->prepare("SELECT id FROM face_students WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student = $result->fetch_assoc();
            $student_id = $student['id'];

            // Check if attendance already marked for today
            $today = date('Y-m-d');
            $stmt = $conn->prepare("SELECT id FROM face_attendance WHERE student_id = ? AND DATE(timestamp) = ?");
            $stmt->bind_param("is", $student_id, $today);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                echo json_encode(['status' => 'already_marked', 'message' => $name . ' has already been marked present today.']);
            } else {
                // Insert new attendance record
                $status = 'present';
                $stmt = $conn->prepare("INSERT INTO face_attendance (student_id, status) VALUES (?, ?)");
                $stmt->bind_param("is", $student_id, $status);
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Attendance marked for ' . $name]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
                }
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Student not found.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>