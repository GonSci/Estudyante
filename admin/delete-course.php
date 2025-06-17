<?php
include '../includes/db.php';

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id > 0) {
    // Remove all prerequisite links involving this course
    $stmt = $conn->prepare("DELETE FROM course_prerequisites WHERE course_id = ? OR prerequisite_id = ?");
    $stmt->bind_param("ii", $course_id, $course_id);
    $stmt->execute();
    $stmt->close();

    // Now delete the course
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: manage-courses.php");
exit;
?>