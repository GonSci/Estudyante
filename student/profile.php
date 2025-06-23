<?php include 'navbar.php'; ?>

<style>


h2 {
    color:rgb(0, 3, 10);
    margin-bottom: 1rem;
}

.profile-table {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.profile-table th {
    background: #3b82f6;
    color: white;
    font-weight: 600;
    padding: 12px 15px;
    width: 180px;
}

.profile-table td {
    padding: 12px 15px;
    color: #374151;
}

.profile-table tr:nth-child(even) {
    background: #f8fafc;
}
</style>

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

<h2>My Profile</h2>

<table class="table table-bordered profile-table">
    <tr><th>Student ID</th><td><?= htmlspecialchars($student['id']) ?></td></tr>
    <tr><th>First Name</th><td><?= htmlspecialchars($student['first_name']) ?></td></tr>
    <tr><th>Middle Name</th><td><?= htmlspecialchars($student['middle_name']) ?></td></tr>
    <tr><th>Last Name</th><td><?= htmlspecialchars($student['last_name']) ?></td></tr>
    <tr><th>Date of Birth</th><td><?= htmlspecialchars($student['date_of_birth']) ?></td></tr>
    <tr><th>Address</th><td><?= htmlspecialchars($student['address']) ?></td></tr>
    <tr><th>Contact Number</th><td><?= htmlspecialchars($student['contact_number']) ?></td></tr>
    <tr><th>Program</th><td><?= htmlspecialchars($student['program']) ?></td></tr>
    <tr><th>Enrollment Year</th><td><?= htmlspecialchars($student['year_level']) ?></td></tr>
    <tr><th>Semester</th><td><?= htmlspecialchars($student['academic_term']) ?></td></tr>
    <tr><th>Username</th><td><?= htmlspecialchars($student['username']) ?></td></tr>
</table>

        </main>
    </div>
</div>
</body>
</html>