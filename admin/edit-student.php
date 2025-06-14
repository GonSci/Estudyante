<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
include '../includes/db.php';

$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id <= 0) {
    echo "<p class='text-danger'>Invalid student ID.</p>";
    include 'footer.php';
    exit;
}

$successMessage = "";

// Fetch student data before processing POST (needed for comparison)
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "<p class='text-danger'>Student not found.</p>";
    include 'footer.php';
    exit;
}

$student = $result->fetch_assoc();
$current_username = $student['username'];
$current_email = $student['email'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name      = trim($_POST['first_name']);
    $middle_name     = trim($_POST['middle_name']);
    $last_name       = trim($_POST['last_name']);
    $date_of_birth   = $_POST['date_of_birth'];
    $address         = trim($_POST['address']);
    $contact_number  = trim($_POST['contact_number']);
    $email           = trim($_POST['email']);
    $program         = trim($_POST['program']);
    $enrollment_year = $_POST['enrollment_year'];
    $semester        = $_POST['semester'];
    $username        = trim($_POST['username']);
    $password        = $_POST['password']; // Optional new password

    if (empty($first_name) || empty($middle_name) || empty($last_name) || empty($email) || empty($program) || empty($username)) {
        echo "<p class='text-danger'>Please fill in all required fields.</p>";
    } else {
        // Only check for duplicates if username or email has changed
        if ($username !== $current_username || $email !== $current_email) {
            $check = $conn->prepare("SELECT id FROM students WHERE (username = ? OR email = ?) AND id != ?");
            $check->bind_param("ssi", $username, $email, $student_id);
            $check->execute();
            $check_result = $check->get_result();

            if ($check_result->num_rows > 0) {
                echo "<p class='text-danger'>Username or Email is already taken by another student.</p>";
                $check_result->free_result();
                $check->close();
                exit;
            }
            $check_result->free_result();
            $check->close();
        }

        // Proceed to update
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE students SET 
                first_name = ?, middle_name = ?, last_name = ?, date_of_birth = ?, address = ?, contact_number = ?, 
                email = ?, program = ?, enrollment_year = ?, semester = ?, username = ?, password = ?
                WHERE id = ?");
            $stmt->bind_param("ssssssssssssi",
                $first_name, $middle_name, $last_name, $date_of_birth, $address, $contact_number,
                $email, $program, $enrollment_year, $semester, $username, $hashedPassword, $student_id
            );
        } else {
            $stmt = $conn->prepare("UPDATE students SET 
                first_name = ?, middle_name = ?, last_name = ?, date_of_birth = ?, address = ?, contact_number = ?, 
                email = ?, program = ?, enrollment_year = ?, semester = ?, username = ?
                WHERE id = ?");
            $stmt->bind_param("sssssssssssi",
                $first_name, $middle_name, $last_name, $date_of_birth, $address, $contact_number,
                $email, $program, $enrollment_year, $semester, $username, $student_id
            );
        }

        if ($stmt->execute()) {
            // Update the users table as well
            $user_update_sql = "UPDATE users SET username = ?, password = ? WHERE student_id = ?";
            $user_update_stmt = $conn->prepare($user_update_sql);
            if (!empty($password)) {
                // If password was changed, use the new hashed password
                $user_update_stmt->bind_param('ssi', $username, $hashedPassword, $student_id);
            } else {
                // If password was not changed, fetch the current password from users table
                $get_user = $conn->prepare("SELECT password FROM users WHERE student_id = ?");
                $get_user->bind_param('i', $student_id);
                $get_user->execute();
                $get_user->bind_result($current_user_password);
                $get_user->fetch();
                $get_user->close();
                $user_update_stmt->bind_param('ssi', $username, $current_user_password, $student_id);
            }
            $user_update_stmt->execute();
            $user_update_stmt->close();

            header("Location: manage-students.php?updated=1");
            exit;
        } else {
            echo "<p class='text-danger'>Update failed: " . $stmt->error . "</p>";
        }
    }
}

include 'header.php';
?>

<h3>Edit Student</h3>

<?php if (!empty($successMessage)): ?>
    <div class="alert alert-success"><?= $successMessage ?></div>
<?php endif; ?>

<form id="editStudentForm" method="POST" action="">
    <div class="mb-3"><label>First Name:</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name']) ?>" class="form-control" required>
    </div>

    <div class="mb-3"><label>Middle Name:</label>
        <input type="text" name="middle_name" value="<?= htmlspecialchars($student['middle_name']) ?>" class="form-control">
    </div>

    <div class="mb-3"><label>Last Name:</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name']) ?>" class="form-control" required>
    </div>

    <div class="mb-3"><label>Date of Birth:</label>
        <input type="date" name="date_of_birth" value="<?= $student['date_of_birth'] ?>" class="form-control">
    </div>

    <div class="mb-3"><label>Address:</label>
        <textarea name="address" class="form-control"><?= htmlspecialchars($student['address']) ?></textarea>
    </div>

    <div class="mb-3"><label>Contact Number:</label>
        <input type="text" name="contact_number" value="<?= $student['contact_number'] ?>" class="form-control">
    </div>

    <div class="mb-3"><label>Email:</label>
        <input type="email" name="email" value="<?= $student['email'] ?>" class="form-control" required>
    </div>

    <div class="mb-3"><label>Program:</label>
        <input type="text" name="program" value="<?= htmlspecialchars($student['program']) ?>" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label>Enrollment Year:</label>
        <input type="number" name="enrollment_year" value="<?= $student['enrollment_year'] ?>" min="2000" max="<?= date('Y') ?>" class="form-control">
    </div>
    <div class="mb-3">
        <label>Semester:</label>
        <select name="semester" class="form-control">
            <?php
            $semesters = ["1st", "2nd", "3rd", "4th", "5th", "Summer"];
            foreach ($semesters as $sem) {
                $selected = ($student['semester'] === $sem) ? "selected" : "";
                echo "<option value=\"$sem\" $selected>$sem</option>";
            }
            ?>
        </select>
    </div>
    <hr>
    <div class="mb-3"><label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($student['username']) ?>" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>New Password (leave blank to keep current password):</label>
        <input type="password" name="password" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Update Student</button>
    <a href="manage-students.php" class="btn btn-secondary">Back</a>
</form>

<script>
document.getElementById('editStudentForm').addEventListener('submit', function(e) {
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
                text: 'Student updated successfully.',
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
