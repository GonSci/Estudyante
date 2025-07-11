<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/db.php';

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id <= 0) {
    header("Location: manage-courses.php?error=invalid_id");
    exit;
}

$stmt = $conn->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage-courses.php?error=not_found");
    exit;
}

$course = $result->fetch_assoc();
$course_title = $course['title'];
$stmt->close();

if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
    $delete_program = $conn->prepare("DELETE FROM program_course WHERE course_id = ?");
    $delete_program->bind_param("i", $course_id);
    $delete_program->execute();
    $delete_program->close();

    $delete_prereq = $conn->prepare("DELETE FROM course_prerequisites WHERE course_id = ? OR prerequisite_id = ?");
    $delete_prereq->bind_param("ii", $course_id, $course_id);
    $delete_prereq->execute();
    $delete_prereq->close();

    $delete_course = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $delete_course->bind_param("i", $course_id);
    $result = $delete_course->execute();
    $delete_course->close();

    header("Location: manage-courses.php?success=deleted");
    exit;
}

include 'header.php';
?>

<div class="container mt-4">
    <h3>Delete Course</h3>
    <div id="deleteConfirmation">
        <p>Are you sure you want to delete the course <strong><?= htmlspecialchars($course_title) ?></strong>?</p>
        <p class="text-danger"><strong>Warning:</strong> This action cannot be undone. All program assignments and prerequisite relationships will also be deleted.</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to delete "<?= htmlspecialchars($course_title) ?>". This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'confirm_delete';
            input.value = 'yes';
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        } else {
            window.location.href = 'manage-courses.php';
        }
    });
});
</script>

<?php include 'footer.php'; ?>