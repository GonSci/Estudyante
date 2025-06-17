<?php include 'navbar.php'; ?>
<main class="col-md-10 main-content">
<?php
include '../includes/db.php';

$username = $_SESSION['username'];




$stmt = $conn->prepare("SELECT * FROM students WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();


$_SESSION['username'] = $username;
$_SESSION['role'] = 'student';
?>
<p>Welcome, <?= $student['first_name'] ." " . $student['middle_name'] ." ". $student['last_name']?></p>

<h2>My Profile</h2>
<table class="table table-bordered">
    <tr><th>Student ID</th><td><?= htmlspecialchars($student['id']) ?></td></tr>
    <tr><th>First Name</th><td><?= htmlspecialchars($student['first_name']) ?></td></tr>
    <tr><th>Middle Name</th><td><?= htmlspecialchars($student['middle_name']) ?></td></tr>
    <tr><th>Last Name</th><td><?= htmlspecialchars($student['last_name']) ?></td></tr>
    <tr><th>Date of Birth</th><td><?= htmlspecialchars($student['date_of_birth']) ?></td></tr>
    <tr><th>Address</th><td><?= htmlspecialchars($student['address']) ?></td></tr>
    <tr><th>Contact Number</th><td><?= htmlspecialchars($student['contact_number']) ?></td></tr>
    <tr><th>Program</th><td><?= htmlspecialchars($student['program']) ?></td></tr>
    <tr><th>Enrollment Year</th><td><?= htmlspecialchars($student['enrollment_year']) ?></td></tr>
    <tr><th>Semester</th><td><?= htmlspecialchars($student['semester']) ?></td></tr>
    <tr><th>Username</th><td><?= htmlspecialchars($student['username']) ?></td></tr>
</table>
        </main>
    </div>
</div>
</body>
</html>