<?php
include '../includes/db.php';

if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
}
header("Location: manage-students.php?deleted=1");
exit;