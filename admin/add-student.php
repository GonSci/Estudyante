<?php include 'header.php'; ?>
<?php include '../includes/db.php'; ?>

<h3>Add New Student</h3>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
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
    $password        = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validation check
    if (
        empty($first_name) || empty($last_name) || empty($email) || empty($program) ||
        empty($username) || empty($_POST['password'])
    ) {
        echo "<p class='text-danger'>Please fill in all required fields.</p>";
    } else {
        $sql = "INSERT INTO students 
                (first_name, middle_name, last_name, date_of_birth, address, contact_number, email, program, enrollment_year, semester, username, password, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo "<p class='text-danger'>Prepare failed: " . $conn->error . "</p>";
        } else {
            $stmt->bind_param(
                'ssssssssssss',
                $first_name,$middle_name, $last_name, $date_of_birth, $address, $contact_number,
                $email, $program, $enrollment_year, $semester, $username, $password
            );
            if ($stmt->execute()) {
                // Get the new student's ID
                $new_student_id = $conn->insert_id;

                // Prepare user insert (use the same username and password, role = 'student')
                $user_stmt = $conn->prepare("INSERT INTO users (username, password, role, student_id, created_at) VALUES (?, ?, 'student', ?, NOW())");
                $user_stmt->bind_param('ssi', $username, $password, $new_student_id);

                if ($user_stmt->execute()) {
                    header("Location: manage-students.php?success=1");
                    exit;
                } else {
                    echo "<p class='text-danger'>User creation failed: " . $user_stmt->error . "</p>";
                }
                $user_stmt->close();
            } else {
                echo "<p class='text-danger'>Execute failed: " . $stmt->error . "</p>";
            }
            $stmt->close();
        }
    }
}
?>

<!-- 🧾 Student Form -->
<form method="POST" action="">
    <div class="mb-3"><label>First Name:</label><input type="text" name="first_name" class="form-control" required></div>
    <div class="mb-3"><label>Middle Name:</label><input type="text" name="middle_name" class="form-control" required></div>
    <div class="mb-3"><label>Last Name:</label><input type="text" name="last_name" class="form-control" required></div>
    <div class="mb-3"><label>Date of Birth:</label><input type="date" name="date_of_birth" class="form-control"></div>
    <div class="mb-3"><label>Address:</label><textarea name="address" class="form-control"></textarea></div>
    <div class="mb-3"><label>Contact Number:</label><input type="text" name="contact_number" class="form-control"></div>
    <div class="mb-3"><label>Email:</label><input type="email" name="email" class="form-control" required></div>
    <div class="mb-3"><label>Program:</label><input type="text" name="program" class="form-control" required></div>
    <div class="mb-3">
        <label>Enrollment Year:</label>
        <input type="number" name="enrollment_year" min="2000" max="<?= date('Y') ?>" class="form-control">
    </div>
    <div class="mb-3">
        <label>Semester:</label>
        <select name="semester" class="form-control">
            <option value="">Select</option>
            <option value="1st">1st</option>
            <option value="2nd">2nd</option>
            <option value="3rd">3rd</option>
            <option value="4th">4th</option>
            <option value="5th">5th</option>
            <option value="Summer">Summer</option>
        </select>
    </div>
    <hr>
    <div class="mb-3"><label>Username:</label><input type="text" name="username" class="form-control" required></div>
    <div class="mb-3"><label>Password:</label><input type="password" name="password" class="form-control" required></div>
    <button type="submit" class="btn btn-primary">Add Student</button>
</form>

<?php include 'footer.php'; ?>
