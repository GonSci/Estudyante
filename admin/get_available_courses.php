<?php
include '../includes/db.php';

$program_id = $_GET['program_id'] ?? 0;

$sql = "SELECT * FROM courses WHERE id NOT IN 
        (SELECT course_id FROM program_course WHERE program_code = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $program_id);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = ['id' => $row['id'], 'title' => $row['title']];
}

echo json_encode($courses);
