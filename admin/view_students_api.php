<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

session_start();

try {
    $query = "SELECT s.id, s.name, s.roll_no, st.name as stream_name, sem.semester_number, s.photoUrl FROM face_students s JOIN streams st ON s.stream_id = st.id JOIN semesters sem ON s.semester_id = sem.id";

    // Filter based on user role
    if (isset($_SESSION['admin_role'])) {
        if ($_SESSION['admin_role'] === 'hod') {
            $query .= " WHERE s.stream_id = " . intval($_SESSION['admin_stream_id']);
        } elseif ($_SESSION['admin_role'] === 'teacher') {
            $query .= " WHERE s.stream_id = " . intval($_SESSION['admin_stream_id']) . " AND s.semester_id = " . intval($_SESSION['admin_semester_id']);
        }
    }

    $result = $conn->query($query);

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