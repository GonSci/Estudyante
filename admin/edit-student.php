<?php include 'header.php'; ?>
<?php include '../includes/db.php'; ?>

<?php
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id <= 0) {
    echo "<p class='text-danger'>Invalid student ID.</p>";
    include 'footer.php';
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name      = trim($_POST['first_name']);
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

    if (empty($first_name) || empty($last_name) || empty($email) || empty($program) || empty($username)) {
        echo "<p class='text-danger'>Please fill in all required fields.</p>";
    } else {
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE students SET 
                first_name = ?, last_name = ?, date_of_birth = ?, address = ?, contact_number = ?, 
                email = ?, program = ?, enrollment_year = ?, semester = ?, username = ?, password = ?
                WHERE id = ?");
            $stmt->bind_param("sssssssssssi",
                $first_name, $last_name, $date_of_birth, $address, $contact_number,
                $email, $program, $enrollment_year, $semester, $username, $hashedPassword, $student_id
            );
        } else {
            $stmt = $conn->prepare("UPDATE students SET 
                first_name = ?, last_name = ?, date_of_birth = ?, address = ?, contact_number = ?, 
                email = ?, program = ?, enrollment_year = ?, semester = ?, username = ?
                WHERE id = ?");
            $stmt->bind_param("ssssssssssi",
                $first_name, $last_name, $date_of_birth, $address, $contact_number,
                $email, $program, $enrollment_year, $semester, $username, $student_id
            );
        }

        if ($stmt->execute()) {
            header("Location: manage-students.php?updated=1");
            exit;
        } else {
            echo "<p class='text-danger'>Update failed: " . $stmt->error . "</p>";
        }
    }
}

// Fetch student data
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
?>

<h3>Edit Student</h3>

<form method="POST" action="">
    <div class="mb-3">
        <label>First Name:</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name']) ?>" class="form-control" required>
    </div>
    
    <div class="mb-3">
        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name']) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Date of Birth:</label>
        <input type="date" name="date_of_birth" value="<?= $student['date_of_birth'] ?>" class="form-control">
    </div>

    <div class="mb-3">
        <label>Address:</label>
        <textarea name="address" class="form-control"><?= htmlspecialchars($student['address']) ?></textarea>
    </div>

    <div class="mb-3">
        <label>Contact Number:</label>
        <input type="text" name="contact_number" value="<?= $student['contact_number'] ?>" class="form-control">
    </div>

    <div class="mb-3">
        <label>Email:</label>
        <input type="email" name="email" value="<?= $student['email'] ?>" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Program:</label>
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
    <h3>Account Details</h3>
    <div class="mb-3"><label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($student['username']) ?>" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>New Password (leave blank to keep current password):</label>
        <input type="password" name="password" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Update Student</button>
</form>

<?php include 'footer.php'; ?>
