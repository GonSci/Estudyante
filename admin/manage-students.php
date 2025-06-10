<?php include 'header.php'; ?>
<?php include '../includes/db.php'; ?>

<h3>Manage Students</h3>

<?php
// Assuming $conn is a MySQLi object from db.php
$query = "SELECT * FROM students";
$result = $conn->query($query);

if (!$result) {
    echo "<p class='text-danger'>Failed to retrieve students: " . $conn->error . "</p>";
    include 'footer.php';
    exit;
}
?>

<!-- Student Button -->
<a href="add-student.php" class="btn btn-sm btn-success mb-2">Add student</a>

<!-- Student Table -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Program</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= $row['first_name'] . " " . $row['last_name']; ?></td>
            <td><?= $row['email']; ?></td>
            <td><?= $row['program']; ?></td>
            <td>
                <a href="edit-student.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="delete-student.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>
