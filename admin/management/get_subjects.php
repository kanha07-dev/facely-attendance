<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isset($_GET['stream_id']) || !isset($_GET['semester'])) {
    echo json_encode([]);
    exit;
}

$streamId = intval($_GET['stream_id']);
$semester = intval($_GET['semester']);

$stmt = $conn->prepare("
    SELECT s.id, s.subject_name
    FROM subjects s
    JOIN semesters sem ON s.semester_id = sem.id
    WHERE s.stream_id = ? AND sem.semester_number = ?
");
$stmt->bind_param("ii", $streamId, $semester);
$stmt->execute();
$result = $stmt->get_result();

$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

echo json_encode($subjects);
$stmt->close();
$conn->close();
?>