<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection, but NOT the header yet
include '../includes/db.php';

// Get course ID from URL
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id <= 0) {
    header("Location: manage-courses.php?error=invalid_id");
    exit;
}

// Fetch course data
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage-courses.php?error=not_found");
    exit;
}

$course = $result->fetch_assoc();
$stmt->close();

// Fetch current prerequisites
$prereq_stmt = $conn->prepare("SELECT prerequisite_id FROM course_prerequisites WHERE course_id = ?");
$prereq_stmt->bind_param("i", $course_id);
$prereq_stmt->execute();
$prereq_result = $prereq_stmt->get_result();
$current_prerequisites = [];
while ($row = $prereq_result->fetch_assoc()) {
    $current_prerequisites[] = $row['prerequisite_id'];
}
$prereq_stmt->close();

// Fetch current program assignments
$program_stmt = $conn->prepare("SELECT program_code, year_level, academic_term FROM program_course WHERE course_id = ?");
$program_stmt->bind_param("i", $course_id);
$program_stmt->execute();
$program_result = $program_stmt->get_result();
$current_programs = [];
$current_year_level = '';
$current_academic_term = '';
while ($row = $program_result->fetch_assoc()) {
    $current_programs[] = $row['program_code'];
    // Use the first assignment's year/term as default (you might want to improve this)
    if (empty($current_year_level)) {
        $current_year_level = $row['year_level'];
        $current_academic_term = $row['academic_term'];
    }
}
$program_stmt->close();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = trim($_POST['title']);
    $description   = trim($_POST['description']);
    $credits       = intval($_POST['credits']);
    $max_capacity  = intval($_POST['max_capacity']);
    $prerequisites = isset($_POST['prerequisites']) ? $_POST['prerequisites'] : [];
    $year_level    = $_POST['year_level'];
    $academic_term = $_POST['academic_term'];

    if (empty($title) || empty($description) || $credits <= 0 || $max_capacity <= 0 || empty($_POST['year_level']) || empty($_POST['academic_term'])) {
        $error_message = "Please fill in all required fields including Year Level and Academic Term.";
    } else {
        $stmt = $conn->prepare("UPDATE courses SET title = ?, description = ?, credits = ?, max_capacity = ? WHERE id = ?");
        $stmt->bind_param("ssiis", $title, $description, $credits, $max_capacity, $course_id);
        if ($stmt->execute()) {
            // Delete old prerequisites
            $delete_prereq = $conn->prepare("DELETE FROM course_prerequisites WHERE course_id = ?");
            $delete_prereq->bind_param("i", $course_id);
            $delete_prereq->execute();
            
            // Insert new prerequisites
            foreach ($prerequisites as $prereq_id) {
                $prereq_id = intval($prereq_id);
                if ($prereq_id > 0) {
                    $insert_prereq = $conn->prepare("INSERT INTO course_prerequisites (course_id, prerequisite_id) VALUES (?, ?)");
                    $insert_prereq->bind_param("ii", $course_id, $prereq_id);
                    $insert_prereq->execute();
                }
            }
            
            // Delete old program assignments
            $delete_programs = $conn->prepare("DELETE FROM program_course WHERE course_id = ?");
            $delete_programs->bind_param("i", $course_id);
            $delete_programs->execute();
            
            // Assign course to selected programs
            $assigned_programs = isset($_POST['programs']) ? $_POST['programs'] : [];
            foreach ($assigned_programs as $program_code) {
                $program_code = trim($program_code);
                if ($program_code !== '') {
                    $assign_stmt = $conn->prepare("INSERT INTO program_course (program_code, course_id, year_level, academic_term) VALUES (?, ?, ?, ?)");
                    $assign_stmt->bind_param("siss", $program_code, $course_id, $year_level, $academic_term);
                    $assign_stmt->execute();
                    $assign_stmt->close();
                }
            }

            header("Location: manage-courses.php?success=updated");
            exit;
        } else {
            $error_message = "Failed to update course: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Only include the header AFTER all possible redirects
include 'header.php';

// Fetch all existing courses for prerequisites dropdown
$all_courses = $conn->query("SELECT id, title FROM courses WHERE id != $course_id");
$programs = $conn->query("SELECT program_code, program_name FROM programs ORDER BY program_code");
?>

<h3>Edit Course</h3>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger"><?= $error_message ?></div>
<?php endif; ?>

<form method="POST" action="">
    <div class="mb-3">
        <label>Title:</label>
        <input type="text" name="title" class="form-control" required 
               value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : htmlspecialchars($course['title']) ?>">
    </div>
    <div class="mb-3">
        <label>Description:</label>
        <textarea name="description" class="form-control" required><?= isset($_POST['description']) ? 
            htmlspecialchars($_POST['description']) : htmlspecialchars($course['description']) ?></textarea>
    </div>
    <div class="mb-3">
        <label>Units:</label>
        <input type="number" name="credits" class="form-control" min="1" required 
               value="<?= isset($_POST['credits']) ? htmlspecialchars($_POST['credits']) : htmlspecialchars($course['credits']) ?>">
    </div>
    <div class="mb-3">
        <label>Prerequisites:</label>
        <select name="prerequisites[]" class="form-control" multiple>
            <?php while ($row = $all_courses->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" 
                    <?= (isset($_POST['prerequisites']) && in_array($row['id'], $_POST['prerequisites'])) || 
                        (!isset($_POST['prerequisites']) && in_array($row['id'], $current_prerequisites)) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['title']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <small class="form-text text-muted">Hold Ctrl (Windows) or Command (Mac) to select multiple prerequisites. Leave blank if none.</small>
    </div>
    <div class="mb-3">
        <label>Max Capacity:</label>
        <input type="number" name="max_capacity" class="form-control" min="1" required 
               value="<?= isset($_POST['max_capacity']) ? htmlspecialchars($_POST['max_capacity']) : htmlspecialchars($course['max_capacity']) ?>">
    </div>
    <div class="mb-3">
        <label>Assign to Program(s):</label>
        <select name="programs[]" class="form-control" multiple>
            <?php 
            // Reset the result pointer for programs
            $programs->data_seek(0);
            while ($row = $programs->fetch_assoc()): 
            ?>
                <option value="<?= htmlspecialchars($row['program_code']) ?>"
                    <?= (isset($_POST['programs']) && in_array($row['program_code'], $_POST['programs'])) || 
                        (!isset($_POST['programs']) && in_array($row['program_code'], $current_programs)) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['program_code']) ?> - <?= htmlspecialchars($row['program_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <small class="form-text text-muted">Hold Ctrl (Windows) or Command (Mac) to select multiple programs.</small>
    </div>

    <div class="mb-3">
        <label>Year Level:</label>
        <select name="year_level" class="form-control" required>
            <option value="">-- Select Year --</option>
            <option value="1st" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '1st') || 
                                   (!isset($_POST['year_level']) && $current_year_level == '1st') ? 'selected' : '' ?>>1st Year</option>
            <option value="2nd" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '2nd') || 
                                   (!isset($_POST['year_level']) && $current_year_level == '2nd') ? 'selected' : '' ?>>2nd Year</option>
            <option value="3rd" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '3rd') || 
                                   (!isset($_POST['year_level']) && $current_year_level == '3rd') ? 'selected' : '' ?>>3rd Year</option>
            <option value="4th" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '4th') || 
                                   (!isset($_POST['year_level']) && $current_year_level == '4th') ? 'selected' : '' ?>>4th Year</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Academic Term:</label>
        <select name="academic_term" class="form-control" required>
            <option value="">-- Select Term --</option>
            <option value="1st Term" <?= (isset($_POST['academic_term']) && $_POST['academic_term'] == '1st Term') || 
                                        (!isset($_POST['academic_term']) && $current_academic_term == '1st Term') ? 'selected' : '' ?>>1st Term</option>
            <option value="2nd Term" <?= (isset($_POST['academic_term']) && $_POST['academic_term'] == '2nd Term') || 
                                        (!isset($_POST['academic_term']) && $current_academic_term == '2nd Term') ? 'selected' : '' ?>>2nd Term</option>
            <option value="3rd Term" <?= (isset($_POST['academic_term']) && $_POST['academic_term'] == '3rd Term') || 
                                        (!isset($_POST['academic_term']) && $current_academic_term == '3rd Term') ? 'selected' : '' ?>>3rd Term</option>
        </select>
    </div>

    <button type="button" id="updateButton" class="btn btn-primary">Update Course</button>
    <a href="manage-courses.php" class="btn btn-secondary">Back</a>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('updateButton').addEventListener('click', function() {
    Swal.fire({
        title: 'Update Course?',
        text: 'Are you sure you want to save these changes?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, update it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit the form
            document.querySelector('form').submit();
        }
    });
});
</script>

<?php include 'footer.php'; ?>