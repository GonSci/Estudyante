<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'header.php';
include '../includes/db.php';

// Fetch all programs
$programs = $conn->query(query: "SELECT DISTINCT program_code AS program FROM program_course UNION SELECT DISTINCT program AS program FROM students");

// Fetch all courses with prerequisites
$courses = $conn->query("SELECT * FROM courses");

// Helper: Get prerequisites for a course
function getPrerequisites($conn, $course_id) {
    $sql = "SELECT c.title FROM course_prerequisites cp
            JOIN courses c ON cp.prerequisite_id = c.id
            WHERE cp.course_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $prereqs = [];
    while ($row = $result->fetch_assoc()) {
        $prereqs[] = $row['title'];
    }
    return $prereqs;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program_code = $_POST['program_code'];
    $selected_courses = isset($_POST['courses']) ? $_POST['courses'] : [];

    // Validation: at least one course
    if (empty($selected_courses)) {
        echo "<p class='text-danger'>Please select at least one course for the program.</p>";
    } else {
        // Prerequisite validation
        $missing_prereqs = [];
        foreach ($selected_courses as $course_id) {
            // Get prerequisites for this course
            $sql = "SELECT prerequisite_id FROM course_prerequisites WHERE course_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                if (!in_array($row['prerequisite_id'], $selected_courses)) {
                    // Get course and prerequisite titles for message
                    $course_title = '';
                    $prereq_title = '';
                    $ct = $conn->query("SELECT title FROM courses WHERE id = $course_id")->fetch_assoc();
                    $pt = $conn->query("SELECT title FROM courses WHERE id = {$row['prerequisite_id']}")->fetch_assoc();
                    if ($ct && $pt) {
                        $course_title = $ct['title'];
                        $prereq_title = $pt['title'];
                        $missing_prereqs[] = "$course_title requires $prereq_title";
                    }
                }
            }
        }

        if (!empty($missing_prereqs)) {
            echo "<p class='text-danger'>You must also select prerequisites for the chosen courses:</p>";
            echo "<ul class='text-danger'>";
            foreach ($missing_prereqs as $msg) {
                echo "<li>" . htmlspecialchars($msg) . "</li>";
            }
            echo "</ul>";
        } else {
            // Remove old assignments
            $stmt = $conn->prepare("DELETE FROM program_course WHERE program_code = ?");
            $stmt->bind_param("s", $program_code);
            $stmt->execute();

            // Insert new assignments
            $stmt = $conn->prepare("INSERT INTO program_code (program_code, course_id) VALUES (?, ?)");
            foreach ($selected_courses as $course_id) {
                $stmt->bind_param("si", $program_code, $course_id);
                $stmt->execute();
            }
            echo "<p class='text-success'>Courses assigned to program successfully!</p>";
        }
    }
}

// Fetch all program-course assignments
$assignments = $conn->query("
    SELECT pc.program_code, c.title AS course_title
    FROM program_course pc
    JOIN courses c ON pc.course_id = c.id
    ORDER BY pc.program_code, c.title
");

$program_courses = [];
while ($row = $assignments->fetch_assoc()) {
    $program_courses[$row['program_code']][] = $row['course_title'];
}
?>

<h3>Assign Courses to Program</h3>
<form method="POST">
    <div class="mb-3">
        <label>Program:</label>
        <select name="program_code" class="form-control" required>
            <option value="">-- Select Program --</option>
            <?php while ($row = $programs->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['program_code'] ?? $row['program']) ?>">
                    <?= htmlspecialchars($row['program_code'] ?? $row['program']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="mb-3">
        <label>Courses:</label>
        <div style="max-height:200px;overflow:auto;border:1px solid #ccc;padding:10px;">
            <?php while ($course = $courses->fetch_assoc()): ?>
                <?php $prereqs = getPrerequisites($conn, $course['id']); ?>
                <div>
                    <input type="checkbox" name="courses[]" value="<?= $course['id'] ?>" id="course<?= $course['id'] ?>">
                    <label for="course<?= $course['id'] ?>">
                        <?= htmlspecialchars($course['title']) ?>
                        <?php if ($prereqs): ?>
                            <small class="text-muted">(Prerequisite: <?= htmlspecialchars(implode(', ', $prereqs)) ?>)</small>
                        <?php endif; ?>
                    </label>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Assign Courses</button>
</form>

<h4 class="mt-4">Current Program-Course Assignments</h4>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Program</th>
            <th>Assigned Courses</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($program_courses as $program => $courses): ?>
            <tr>
                <td><?= htmlspecialchars($program) ?></td>
                <td><?= htmlspecialchars(implode(', ', $courses)) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>