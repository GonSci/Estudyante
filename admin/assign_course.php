<?php
include '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$program_id = $data['program_id'];
$course_id = $data['course_id'];

// Get course prerequisites
$sql = "SELECT prerequisite_id FROM course_prerequisites WHERE course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

$missing = [];
while ($row = $result->fetch_assoc()) {
    $check = $conn->prepare("SELECT 1 FROM program_course WHERE program_code = ? AND course_id = ?");
    $check->bind_param("ii", $program_id, $row['prerequisite_id']);
    $check->execute();
    $checkResult = $check->get_result();
    if ($checkResult->num_rows === 0) {
        $prereqTitle = $conn->query("SELECT title FROM courses WHERE id = {$row['prerequisite_id']}")->fetch_assoc()['title'];
        $missing[] = $prereqTitle;
    }
}

if (!empty($missing)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing prerequisites: ' . implode(', ', $missing)
    ]);
    exit;
}

// Assign course
$stmt = $conn->prepare("INSERT INTO program_course (program_code, course_id) VALUES (?, ?)");
$stmt->bind_param("ii", $program_id, $course_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB insert error']);
}
