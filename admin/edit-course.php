<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/db.php';

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id <= 0) {
    header("Location: manage-courses.php?error=invalid_id");
    exit;
}

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

$prereq_stmt = $conn->prepare("SELECT prerequisite_id FROM course_prerequisites WHERE course_id = ?");
$prereq_stmt->bind_param("i", $course_id);
$prereq_stmt->execute();
$prereq_result = $prereq_stmt->get_result();
$current_prerequisites = [];
while ($row = $prereq_result->fetch_assoc()) {
    $current_prerequisites[] = $row['prerequisite_id'];
}
$prereq_stmt->close();

$program_stmt = $conn->prepare("SELECT program_code, year_level, academic_term FROM program_course WHERE course_id = ?");
$program_stmt->bind_param("i", $course_id);
$program_stmt->execute();
$program_result = $program_stmt->get_result();
$current_programs = [];
$current_year_level = '';
$current_academic_term = '';
while ($row = $program_result->fetch_assoc()) {
    $current_programs[] = $row['program_code'];
    if (empty($current_year_level)) {
        $current_year_level = $row['year_level'];
        $current_academic_term = $row['academic_term'];
    }
}
$program_stmt->close();

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
        $stmt->bind_param("ssiii", $title, $description, $credits, $max_capacity, $course_id);
        if ($stmt->execute()) {
            $delete_prereq = $conn->prepare("DELETE FROM course_prerequisites WHERE course_id = ?");
            $delete_prereq->bind_param("i", $course_id);
            $delete_prereq->execute();
            
            foreach ($prerequisites as $prereq_id) {
                $prereq_id = intval($prereq_id);
                if ($prereq_id > 0) {
                    $insert_prereq = $conn->prepare("INSERT INTO course_prerequisites (course_id, prerequisite_id) VALUES (?, ?)");
                    $insert_prereq->bind_param("ii", $course_id, $prereq_id);
                    $insert_prereq->execute();
                }
            }
            
            $delete_programs = $conn->prepare("DELETE FROM program_course WHERE course_id = ?");
            $delete_programs->bind_param("i", $course_id);
            $delete_programs->execute();
            
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

include 'header.php';

$all_courses = $conn->query("SELECT id, title FROM courses WHERE id != $course_id");
$programs = $conn->query("SELECT program_code, program_name FROM programs ORDER BY program_code");
?>

<div class="card mb-4 shadow-sm">
<div class="card-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">       
    <h3 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Course</h3>
    </div>
    <div class="card-body">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?= $error_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-book me-1 text-primary-custom"></i> Course Title:</label>
                        <input type="text" name="title" class="form-control" required 
                               value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : htmlspecialchars($course['title']) ?>" 
                               placeholder="Enter course title">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-calculator me-1 text-primary-custom"></i> Units:</label>
                        <input type="number" name="credits" class="form-control" min="1" required 
                               value="<?= isset($_POST['credits']) ? htmlspecialchars($_POST['credits']) : htmlspecialchars($course['credits']) ?>" 
                               placeholder="Enter course units (e.g. 3)">
                    </div>
                </div>
            </div>
            
            <!-- Description -->
            <div class="mb-4">
                <label class="form-label fw-bold"><i class="fas fa-align-left me-1 text-primary-custom"></i> Description:</label>
                <textarea name="description" class="form-control" rows="3" required><?= isset($_POST['description']) ? 
                    htmlspecialchars($_POST['description']) : htmlspecialchars($course['description']) ?></textarea>
            </div>
            
            <hr class="my-4">
            
            <!-- Course Setup -->
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-arrows-alt-h me-1 text-primary-custom"></i> Prerequisites:</label>
                        <select name="prerequisites[]" class="form-select" multiple style="height: 150px;">
                            <?php while ($row = $all_courses->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>" 
                                    <?= (isset($_POST['prerequisites']) && in_array($row['id'], $_POST['prerequisites'])) || 
                                        (!isset($_POST['prerequisites']) && in_array($row['id'], $current_prerequisites)) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['title']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl (Windows) or Command (Mac) to select multiple prerequisites.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-graduation-cap me-1 text-primary-custom"></i> Assign to Program(s):</label>
                        <select name="programs[]" class="form-select" multiple style="height: 150px;">
                            <?php 
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
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-users me-1 text-primary-custom"></i> Max Capacity:</label>
                        <input type="number" name="max_capacity" class="form-control" min="1" required 
                               value="<?= isset($_POST['max_capacity']) ? htmlspecialchars($_POST['max_capacity']) : htmlspecialchars($course['max_capacity']) ?>" 
                               placeholder="Enter max capacity (e.g. 30)">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-layer-group me-1 text-primary-custom"></i> Year Level:</label>
                        <select name="year_level" class="form-select" required>
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
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-calendar-alt me-1 text-primary-custom"></i> Academic Term:</label>
                        <select name="academic_term" class="form-select" required>
                            <option value="">-- Select Term --</option>
                            <option value="1st Term" <?= (isset($_POST['academic_term']) && $_POST['academic_term'] == '1st Term') || 
                                                (!isset($_POST['academic_term']) && $current_academic_term == '1st Term') ? 'selected' : '' ?>>1st Term</option>
                            <option value="2nd Term" <?= (isset($_POST['academic_term']) && $_POST['academic_term'] == '2nd Term') || 
                                                (!isset($_POST['academic_term']) && $current_academic_term == '2nd Term') ? 'selected' : '' ?>>2nd Term</option>
                            <option value="3rd Term" <?= (isset($_POST['academic_term']) && $_POST['academic_term'] == '3rd Term') || 
                                                (!isset($_POST['academic_term']) && $current_academic_term == '3rd Term') ? 'selected' : '' ?>>3rd Term</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- Form Actions -->
            <div class="d-flex justify-content-end">
                <a href="manage-courses.php" class="btn btn-secondary me-2"><i class="fas fa-times me-1"></i> Cancel</a>
                <button type="button" class="btn" id="updateCourseBtn" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
                    <i class="fas fa-save me-1"></i> Update Course
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$stmt = $conn->prepare("SELECT c.title FROM course_prerequisites cp JOIN courses c ON cp.prerequisite_id = c.id WHERE cp.course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<div class="card mt-4 mb-4">';
    echo '<div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Current Prerequisites</h5></div>';
    echo '<div class="card-body"><ul class="list-group list-group-flush">';
    
    while ($row = $result->fetch_assoc()) {
        echo '<li class="list-group-item d-flex align-items-center">';
        echo '<i class="fas fa-check-circle text-success me-2"></i>';
        echo htmlspecialchars($row['title']);
        echo '</li>';
    }
    
    echo '</ul></div></div>';
}
?>

<?php include 'footer.php'; ?>

<!-- SweetAlert library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('updateCourseBtn').addEventListener('click', function() {
        const form = document.querySelector('form');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        Swal.fire({
            title: "Update Course?",
            text: "Are you sure you want to save these changes?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, update it!"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Course Updated!",
                    text: "The course has been successfully updated.",
                    icon: "success",
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    form.submit();
                });
            }
        });
    });
});
</script>