
<?php
include '../includes/db.php';

$program_code = isset($_GET['program_code']) ? $_GET['program_code'] : '';
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($program_code && $course_id > 0) {
    $stmt = $conn->prepare("DELETE FROM program_course WHERE program_code = ? AND course_id = ?");
    $stmt->bind_param("si", $program_code, $course_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: assign-courses-to-program.php");
exit;
?>