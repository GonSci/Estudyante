<?php
include '../includes/db.php';

$program_id = $_GET['program_id'] ?? 0;

$sql = "SELECT c.title FROM program_course pc
        JOIN courses c ON pc.course_id = c.id
        WHERE pc.program_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $program_id);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = ['title' => $row['title']];
}

echo json_encode($courses);
