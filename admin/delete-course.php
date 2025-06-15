<?php
include '../includes/db.php';

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id > 0) {
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: manage-courses.php");
exit;
?>