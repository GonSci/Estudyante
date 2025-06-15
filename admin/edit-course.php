<?php include 'header.php'; ?>
<?php include '../includes/db.php'; ?>

<?php
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($course_id <= 0) {
    echo "<p class='text-danger'>Invalid course ID.</p>";
    include 'footer.php';
    exit;
}

$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    echo "<p class='text-danger'>Course not found.</p>";
    include 'footer.php';
    exit;
}
$course = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = trim($_POST['title']);
    $description   = trim($_POST['description']);
    $credits       = intval($_POST['credits']);
    $prerequisites = trim($_POST['prerequisites']);
    $max_capacity  = intval($_POST['max_capacity']);

    if (empty($title) || empty($description) || empty($credits) || empty($max_capacity)) {
        echo "<p class='text-danger'>Please fill in all required fields.</p>";
    } else {
        $stmt = $conn->prepare("UPDATE courses SET title=?, description=?, credits=?, prerequisites=?, max_capacity=? WHERE id=?");
        $stmt->bind_param("ssisii", $title, $description, $credits, $prerequisites, $max_capacity, $course_id);
        if ($stmt->execute()) {
            header("Location: manage-courses.php?updated=1");
            exit;
        } else {
            echo "<p class='text-danger'>Failed to update course: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}
?>

<h3>Edit Course</h3>
<form id="editCourseForm" method="POST" action="">
    <div class="mb-3">
        <label>Title:</label>
        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($course['title']) ?>">
    </div>
    <div class="mb-3">
        <label>Description:</label>
        <textarea name="description" class="form-control" required><?= htmlspecialchars($course['description']) ?></textarea>
    </div>
    <div class="mb-3">
        <label>Units:</label>
        <input type="number" name="credits" class="form-control" min="1" required value="<?= htmlspecialchars($course['credits']) ?>">
    </div>
    <div class="mb-3">
        <label>Prerequisites:</label>
        <input type="text" name="prerequisites" class="form-control" value="<?= htmlspecialchars($course['prerequisites']) ?>">
    </div>
    <div class="mb-3">
        <label>Max Capacity:</label>
        <input type="number" name="max_capacity" class="form-control" min="1" required value="<?= htmlspecialchars($course['max_capacity']) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Update Course</button>
    <a href="manage-courses.php" class="btn btn-secondary">Back</a>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('editCourseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: "Do you want to save the changes?",
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: "Save",
        denyButtonText: `Don't save`
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Saved!',
                text: 'Course updated successfully.',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                form.submit();
            });
        } else if (result.isDenied) {
            Swal.fire('Changes are not saved', '', 'info');
        }
    });
});
</script>

<?php include 'footer.php'; ?>