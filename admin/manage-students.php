<?php include 'header.php'; ?>
<?php include '../includes/db.php'; ?>

<h3>Manage Students</h3>

<?php
// Get search parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query based on search parameter
$query = "SELECT * FROM students";
if (!empty($search)) {
    // Search in first_name, last_name or combined
    $search = $conn->real_escape_string($search);
    $query .= " WHERE first_name LIKE '%$search%' 
               OR last_name LIKE '%$search%' 
               OR CONCAT(first_name, ' ', last_name) LIKE '%$search%'";
}
$query .= " ORDER BY last_name, first_name";

$result = $conn->query($query);

if (!$result) {
    echo "<p class='text-danger'>Failed to retrieve students: " . $conn->error . "</p>";
    include 'footer.php';
    exit;
}

// Count displayed results
$result_count = $result->num_rows;
?>

<!-- Top Row with Add and Search -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <!-- Left side: Add Student Button -->
    <div>
        <a href="add-student.php" class="btn btn-success">Add Student</a>
    </div>
    
    <!-- Right side: Search Form -->
    <div>
        <form method="GET" action="" class="d-flex align-items-center">
            <input type="text" name="search" class="form-control form-control-sm me-2" 
                   placeholder="Search students..." style="width: 230px;"
                   value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button type="submit" class="btn btn-sm btn-primary me-1">Search</button>
            <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                <a href="manage-students.php" class="btn btn-sm btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Results count -->
<?php if (!empty($search)): ?>
    <div class="alert alert-info py-2">
        Found <?= $result_count ?> student<?= ($result_count != 1) ? 's' : '' ?> matching "<?= htmlspecialchars($search) ?>"
    </div>
<?php endif; ?>

<?php if ($result_count > 0): ?>
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
<?php else: ?>
    <div class="alert alert-warning">
        No students found<?= !empty($search) ? ' matching "' . htmlspecialchars($search) . '"' : '' ?>.
    </div>
<?php endif; ?>

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
