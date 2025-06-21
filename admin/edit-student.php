<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
include '../includes/db.php';
include 'header.php';


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
    $year_level = $_POST['year_level'];
    $academic_term        = $_POST['academic_term'];
    $username        = trim($_POST['username']);
    $password        = $_POST['password']; // Optional new password

    if (empty($first_name) || empty($middle_name) || empty($last_name) || empty($email) || empty($program) || empty($username)) {
        echo "<div class='alert alert-danger' role='alert'>
                Please fill in all required fields.
              </div>";
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
                email = ?, program = ?, year_level = ?, academic_term = ?, username = ?, password = ?
                WHERE id = ?");
            $stmt->bind_param("ssssssssssssi",
                $first_name, $middle_name, $last_name, $date_of_birth, $address, $contact_number,
                $email, $program, $year_level, $academic_term, $username, $hashedPassword, $student_id
            );
        } else {
            $stmt = $conn->prepare("UPDATE students SET 
                first_name = ?, middle_name = ?, last_name = ?, date_of_birth = ?, address = ?, contact_number = ?, 
                email = ?, program = ?, year_level = ?, academic_term = ?, username = ?
                WHERE id = ?");
            $stmt->bind_param("sssssssssssi",
                $first_name, $middle_name, $last_name, $date_of_birth, $address, $contact_number,
                $email, $program, $year_level, $academic_term, $username, $student_id
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
        <input type="text" name="middle_name" value="<?= htmlspecialchars($student['middle_name']) ?>" class="form-control" required>
    </div>

    <div class="mb-3"><label>Last Name:</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name']) ?>" class="form-control" required>
    </div>

    <div class="mb-3"><label>Date of Birth:</label>
        <input type="date" name="date_of_birth" value="<?= $student['date_of_birth'] ?>" class="form-control"required >
    </div>

    <div class="mb-3"><label>Address:</label>
        <textarea name="address" class="form-control" required ><?= htmlspecialchars($student['address']) ?></textarea>
    </div>

    <div class="mb-3"><label>Contact Number:</label>
        <input type="tel" 
               name="contact_number" 
               value="<?= htmlspecialchars($student['contact_number']) ?>" 
               class="form-control"
               pattern="^09\d{9}$"
               maxlength="11"
               title="Contact number must start with 09 and be 11 digits long"
               required>
    </div>
    <div class="mb-3"><label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" class="form-control" required>
    </div>

    <div class="mb-3"><label>Program:</label>
        <select name="program" class="form-control" required>
            <option value="">-- Select Program --</option>
            <option value="BSCpE" <?= $student['program'] == 'BSCpE' ? 'selected' : '' ?>>BSCpE – Bachelor of Science in Computer Engineering</option>
            <option value="BSECE" <?= $student['program'] == 'BSECE' ? 'selected' : '' ?>>BSECE – Bachelor of Science in Electronics Engineering</option>
            <option value="BSEE" <?= $student['program'] == 'BSEE' ? 'selected' : '' ?>>BSEE – Bachelor of Science in Electrical Engineering</option>
            <option value="BSME" <?= $student['program'] == 'BSME' ? 'selected' : '' ?>>BSME – Bachelor of Science in Mechanical Engineering</option>
            <option value="BSCE" <?= $student['program'] == 'BSCE' ? 'selected' : '' ?>>BSCE – Bachelor of Science in Civil Engineering</option>
            <option value="BSCS-SE" <?= $student['program'] == 'BSCS-SE' ? 'selected' : '' ?>>BSCS-SE – Bachelor of Science in Computer Science major in Software Engineering</option>
            <option value="BSCS-DS" <?= $student['program'] == 'BSCS-DS' ? 'selected' : '' ?>>BSCS-DS – Bachelor of Science in Computer Science major in Data Science</option>
            <option value="BSIT-BA" <?= $student['program'] == 'BSIT-BA' ? 'selected' : '' ?>>BSIT-BA – Bachelor of Science in Information Technology major in Business Analytics</option>
            <option value="BSIT-IB" <?= $student['program'] == 'BSIT-IB' ? 'selected' : '' ?>>BSIT-IB – Bachelor of Science in Information Technology major in Innovation and Business</option>
            <option value="BSIT-AGD" <?= $student['program'] == 'BSIT-AGD' ? 'selected' : '' ?>>BSIT-AGD – Bachelor of Science in Information Technology major in Animation and Game Development</option>
            <option value="BSIT-WMA" <?= $student['program'] == 'BSIT-WMA' ? 'selected' : '' ?>>BSIT-WMA – Bachelor of Science in Information Technology major in Web and Mobile Applications</option>
            <option value="BSIT-CY" <?= $student['program'] == 'BSIT-CY' ? 'selected' : '' ?>>BSIT-CY – Bachelor of Science in Information Technology major in Cybersecurity</option>
            <option value="BMMA" <?= $student['program'] == 'BMMA' ? 'selected' : '' ?>>BMMA – Bachelor of Multimedia Arts</option>
        </select>
    </div>

    <div class="mb-3">
    <label>Year Level:</label>
        <select name="year_level" class="form-control">
            <option value="">-- Select Year --</option>
            <option value="1st" <?= $student['year_level'] == '1st' ? 'selected' : '' ?>>1st Year</option>
            <option value="2nd" <?= $student['year_level'] == '2nd' ? 'selected' : '' ?>>2nd Year</option>
            <option value="3rd" <?= $student['year_level'] == '3rd' ? 'selected' : '' ?>>3rd Year</option>
            <option value="4th" <?= $student['year_level'] == '4th' ? 'selected' : '' ?>>4th Year</option>
            <option value="5th" <?= $student['year_level'] == '5th' ? 'selected' : '' ?>>5th Year</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Academic Term:</label>
        <select name="academic_term" class="form-control" required>
            <option value="">-- Select Term --</option>
            <option value="1st Term" <?= $student['academic_term'] == '1st Term' ? 'selected' : '' ?>>1st Term</option>
            <option value="2nd Term" <?= $student['academic_term'] == '2nd Term' ? 'selected' : '' ?>>2nd Term</option>
            <option value="3rd Term" <?= $student['academic_term'] == '3rd Term' ? 'selected' : '' ?>>3rd Term</option>
        </select>
    </div>
    
    <hr>
    <div class="mb-3"><label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($student['username']) ?>" class="form-control">
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
