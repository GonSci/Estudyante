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
                <a 
                    href="delete-student.php?id=<?= $row['id']; ?>" 
                    class="btn btn-sm btn-danger delete-btn"
                    data-name="<?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>"
                    data-id="<?= $row['id']; ?>"
                >Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            const name = this.getAttribute('data-name');
            const id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Are you sure?',
                html: `This action will permanently remove the student <b>${name} (ID: ${id})</b> from the system. Do you want to continue?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
});
</script>

<?php include 'footer.php'; ?>
