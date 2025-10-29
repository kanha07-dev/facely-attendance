<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

try {
    $result = $conn->query("SELECT a.id, a.student_id, s.name, s.roll_no, st.name as stream_name, st.id as stream_id, sem.semester_number, a.timestamp, a.status FROM face_attendance a JOIN face_students s ON a.student_id = s.id JOIN streams st ON s.stream_id = st.id JOIN semesters sem ON s.semester_id = sem.id ORDER BY a.timestamp DESC");

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