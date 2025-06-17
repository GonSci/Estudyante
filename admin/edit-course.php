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

// Fetch all courses for prerequisites selection (excluding the current course)
$all_courses_stmt = $conn->prepare("SELECT id, title FROM courses WHERE id != ?");
$all_courses_stmt->bind_param("i", $course_id);
$all_courses_stmt->execute();
$all_courses_result = $all_courses_stmt->get_result();
$all_courses = [];
while ($row = $all_courses_result->fetch_assoc()) {
    $all_courses[] = $row;
}
$all_courses_stmt->close();

// Fetch current prerequisites for this course
$prereq_stmt = $conn->prepare("SELECT prerequisite_id FROM course_prerequisites WHERE course_id = ?");
$prereq_stmt->bind_param("i", $course_id);
$prereq_stmt->execute();
$prereq_result = $prereq_stmt->get_result();
$current_prereqs = [];
while ($row = $prereq_result->fetch_assoc()) {
    $current_prereqs[] = $row['prerequisite_id'];
}
$prereq_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = trim($_POST['title']);
    $description   = trim($_POST['description']);
    $credits       = intval($_POST['credits']);
    $max_capacity  = intval($_POST['max_capacity']);
    $prerequisites = isset($_POST['prerequisites']) ? $_POST['prerequisites'] : [];

    if (empty($title) || empty($description) || empty($credits) || empty($max_capacity)) {
        echo "<p class='text-danger'>Please fill in all required fields.</p>";
    } else {
        $stmt = $conn->prepare("UPDATE courses SET title=?, description=?, credits=?, max_capacity=? WHERE id=?");
        $stmt->bind_param("ssiii", $title, $description, $credits, $max_capacity, $course_id);
        if ($stmt->execute()) {
            // Update prerequisites
            // First, delete existing prerequisites
            $conn->query("DELETE FROM course_prerequisites WHERE course_id = $course_id");
            // Then, insert new ones
            if (!empty($prerequisites)) {
                $insert_stmt = $conn->prepare("INSERT INTO course_prerequisites (course_id, prerequisite_id) VALUES (?, ?)");
                foreach ($prerequisites as $prereq_id) {
                    $insert_stmt->bind_param("ii", $course_id, $prereq_id);
                    $insert_stmt->execute();
                }
                $insert_stmt->close();
            }
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
        <select name="prerequisites[]" class="form-control" multiple>
            <?php foreach ($all_courses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= in_array($c['id'], $current_prereqs) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small class="form-text text-muted">Hold Ctrl (Windows) or Command (Mac) to select multiple courses.</small>
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