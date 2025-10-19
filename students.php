<?php
header('Content-Type: application/json');
require_once 'config/config.php';

try {
    $result = $conn->query("SELECT id, name, roll_no, stream, photoUrl FROM face_students");

    if ($result) {
        $students = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($students);
    } else {
        echo json_encode([]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>