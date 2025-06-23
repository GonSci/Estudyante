<?php include 'header.php'; ?>
<?php include '../includes/db.php'; ?>

<h3>Manage Students</h3>

<?php
// Get search parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination settings
$records_per_page = 10; // Number of students per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Base query for counting total records
$count_query = "SELECT COUNT(*) as total FROM students";

// Add search conditions if searching
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $count_query .= " WHERE first_name LIKE '%$search%' 
                     OR last_name LIKE '%$search%' 
                     OR CONCAT(first_name, ' ', last_name) LIKE '%$search%'";
}

$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Build query based on search parameter with pagination
$query = "SELECT * FROM students";
if (!empty($search)) {
    // Search in first_name, last_name or combined
    $search = $conn->real_escape_string($search);
    $query .= " WHERE first_name LIKE '%$search%' 
               OR last_name LIKE '%$search%' 
               OR CONCAT(first_name, ' ', last_name) LIKE '%$search%'";
}
$query .= " ORDER BY last_name, first_name LIMIT $offset, $records_per_page";

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
        Found <?= $total_records ?> student<?= ($total_records != 1) ? 's' : '' ?> matching "<?= htmlspecialchars($search) ?>"
        (Showing page <?= $page ?> of <?= $total_pages ?>)
    </div>
<?php else: ?>
    <div class="alert alert-info py-2">
        Showing <?= $result_count ?> of <?= $total_records ?> student<?= ($total_records != 1) ? 's' : '' ?>
        (Page <?= $page ?> of <?= $total_pages ?>)
    </div>
<?php endif; ?>

<?php if ($result_count > 0): ?>
    <!-- Student Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead style="background-color: #007bff; color: white;">
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
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Student pagination">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                        First
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page-1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                        Previous
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">First</a>
                </li>
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                </li>
            <?php endif; ?>

            <?php
            // Calculate range of page numbers to show
            $range = 2; // How many pages to show before and after current page
            $start_page = max(1, $page - $range);
            $end_page = min($total_pages, $page + $range);
            
            // Show page numbers
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page+1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                        Next
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                        Last
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Next</a>
                </li>
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Last</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>

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
