<?php
header('Content-Type: application/json');
require_once 'config/config.php';

try {
    $result = $conn->query("SELECT a.id, s.name, s.roll_no, a.timestamp, a.status FROM face_attendance a JOIN face_students s ON a.student_id = s.id ORDER BY a.timestamp DESC");

    if ($result) {
        $attendance = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($attendance);
    } else {
        echo json_encode([]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>