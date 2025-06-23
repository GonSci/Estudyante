<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection, but NOT the header yet
include '../includes/db.php';

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
        echo "<p class='text-danger'>Please fill in all required fields including Year Level and Academic Term.</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO courses (title, description, credits, max_capacity) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $title, $description, $credits, $max_capacity);
        if ($stmt->execute()) {
            $course_id = $stmt->insert_id;

            // Insert prerequisites into course_prerequisites table
            foreach ($prerequisites as $prereq_id) {
                $prereq_id = intval($prereq_id);
                if ($prereq_id > 0) {
                    $insert_prereq = $conn->prepare("INSERT INTO course_prerequisites (course_id, prerequisite_id) VALUES (?, ?)");
                    $insert_prereq->bind_param("ii", $course_id, $prereq_id);
                    $insert_prereq->execute();
                }
            }

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

            header("Location: manage-courses.php?success=1");
            exit;
        } else {
            echo "<p class='text-danger'>Failed to add course: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}

// Only include the header AFTER all possible redirects
include 'header.php';

// Fetch all existing courses for prerequisites dropdown
$all_courses = $conn->query("SELECT id, title FROM courses");
$programs = $conn->query("SELECT program_code, program_name FROM programs ORDER BY program_code")
?>

<h3>Add New Course</h3>

<form method="POST" action="">
    <div class="mb-3">
        <label>Title:</label>
        <input type="text" name="title" class="form-control" required value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
    </div>
    <div class="mb-3">
        <label>Description:</label>
        <textarea name="description" class="form-control" required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
    </div>
    <div class="mb-3">
        <label>Units:</label>
        <input type="number" name="credits" class="form-control" min="1" required value="<?= isset($_POST['credits']) ? htmlspecialchars($_POST['credits']) : '' ?>">
    </div>
    <div class="mb-3">
        <label>Prerequisites:</label>
        <select name="prerequisites[]" class="form-control" multiple>
            <?php while ($row = $all_courses->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>"
                    <?= (isset($_POST['prerequisites']) && in_array($row['id'], $_POST['prerequisites'])) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['title']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <small class="form-text text-muted">Hold Ctrl (Windows) or Command (Mac) to select multiple prerequisites. Leave blank if none.</small>
    </div>
    <div class="mb-3">
        <label>Max Capacity:</label>
        <input type="number" name="max_capacity" class="form-control" min="1" required value="<?= isset($_POST['max_capacity']) ? htmlspecialchars($_POST['max_capacity']) : '' ?>">
    </div>
    <div class="mb-3">
        <label>Assign to Program(s):</label>
        <select name="programs[]" class="form-control" multiple>
            <?php while ($row = $programs->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['program_code']) ?>"
                    <?= (isset($_POST['programs']) && in_array($row['program_code'], $_POST['programs'])) ? 'selected' : '' ?>>
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
            <option value="1st" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '1st') ? 'selected' : '' ?>>1st Year</option>
            <option value="2nd" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '2nd') ? 'selected' : '' ?>>2nd Year</option>
            <option value="3rd" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '3rd') ? 'selected' : '' ?>>3rd Year</option>
            <option value="4th" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '4th') ? 'selected' : '' ?>>4th Year</option>

        </select>
    </div>


    <div class="mb-3">
        <label>Academic Term:</label>
        <select name="academic_term" class="form-control" required>
            <option value="">-- Select Term --</option>
            <option value="1st Term" <?= (isset($_POST['academic_term']) && $_POST['academic_term'] == '1st Term') ? 'selected' : '' ?>>1st Term</option>
            <option value="2nd Term" <?= (isset($_POST['academic_term']) && $_POST['academic_term'] == '2nd Term') ? 'selected' : '' ?>>2nd Term</option>
            <option value="3rd Term" <?= (isset($_POST['academic_term']) && $_POST['academic_term'] == '3rd Term') ? 'selected' : '' ?>>3rd Term</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Add Course</button>
    <a href="manage-courses.php" class="btn btn-secondary">Back</a>
</form>

<?php
// Display prerequisites for the current course (if editing)
if (isset($course_id)) {
    $stmt = $conn->prepare("SELECT c.title FROM course_prerequisites cp JOIN courses c ON cp.prerequisite_id = c.id WHERE cp.course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "<h4>Current Prerequisites:</h4><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row['title']) . "</li>";
    }
    echo "</ul>";
}
?>

<?php include 'footer.php'; ?>