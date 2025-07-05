<?php include 'header.php'; ?>
<?php include '../includes/db.php'; ?>

<link rel="stylesheet" href="css/manage-students.css">

<?php
// Keep the existing PHP logic for search, pagination, etc.
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

$count_query = "SELECT COUNT(*) as total FROM students";
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $count_query .= " WHERE first_name LIKE '%$search%' 
                     OR last_name LIKE '%$search%' 
                     OR CONCAT(first_name, ' ', last_name) LIKE '%$search%'
                     OR email LIKE '%$search%'
                     OR program LIKE '%$search%'";
}

$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

$query = "SELECT * FROM students";
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " WHERE first_name LIKE '%$search%' 
               OR last_name LIKE '%$search%' 
               OR CONCAT(first_name, ' ', last_name) LIKE '%$search%'
               OR email LIKE '%$search%'
               OR program LIKE '%$search%'";
}
$query .= " ORDER BY last_name, first_name LIMIT $offset, $records_per_page";

$result = $conn->query($query);
if (!$result) {
    echo "<p class='text-danger'>Failed to retrieve students: " . $conn->error . "</p>";
    include 'footer.php';
    exit;
}

$result_count = $result->num_rows;
?>

<div class="container-fluid">
    <!-- Simple Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-user-graduate me-2"></i>Student Management</h2>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="add-student.php" class="btn btn-success-simple">
                        <i class="fas fa-plus me-1"></i>Add Student
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Simple Info Bar -->
        <div class="text-muted mb-3 small">
            <?php if (!empty($search)): ?>
                <i class="fas fa-search me-1"></i>
                Found <strong><?= $total_records ?></strong> student<?= ($total_records != 1) ? 's' : '' ?> matching 
                "<strong><?= htmlspecialchars($search) ?></strong>" 
                (Showing <?= $result_count ?> on page <?= $page ?> of <?= $total_pages ?>)
            <?php else: ?>
                <i class="fas fa-users me-1"></i>
                Showing <strong><?= $result_count ?></strong> of <strong><?= $total_records ?></strong> student<?= ($total_records != 1) ? 's' : '' ?>
                (Page <?= $page ?> of <?= $total_pages ?>)
            <?php endif; ?>
        </div>

        <!-- Simple Search -->
        <div class="search-section">
            <form method="GET" action="" class="row align-items-center">
                <div class="col-md-8">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by name, email, or program..." 
                           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </div>
                <div class="col-md-4 mt-2 mt-md-0">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-simple">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                        <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                            <a href="manage-students.php" class="btn btn-secondary-simple">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($result_count > 0): ?>
            <!-- Students Table -->
            <div class="students-table-container">
                <div class="table-header">
                    <h5><i class="fas fa-table me-2"></i>Student Records (<?= $result_count ?>)</h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table modern-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-1"></i>ID</th>
                                <th><i class="fas fa-user me-1"></i>Student</th>
                                <th><i class="fas fa-envelope me-1"></i>Email</th>
                                <th><i class="fas fa-graduation-cap me-1"></i>Program</th>
                                <th><i class="fas fa-cogs me-1"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="ID">
                                    <span class="fw-bold text-primary"><?= $row['id']; ?></span>
                                </td>
                                <td data-label="Student">
                                    <div class="student-info">
                                        <div class="student-avatar">
                                            <?= strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)) ?>
                                        </div>
                                        <div class="student-details">
                                            <h6><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></h6>
                                            <small>Student ID: <?= $row['id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Email">
                                    <div class="text-muted">
                                        <i class="fas fa-at me-1"></i>
                                        <?= htmlspecialchars($row['email']); ?>
                                    </div>
                                </td>
                                <td data-label="Program">
                                    <span class="program-badge">
                                        <?= htmlspecialchars($row['program']); ?>
                                    </span>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <a href="edit-student.php?id=<?= $row['id']; ?>" class="btn-action btn-edit">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <button 
                                            onclick="deleteStudent(<?= $row['id']; ?>, '<?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>')"
                                            class="btn-action btn-delete">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Simple Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav>
                    <ul class="pagination modern-pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                    <i class="fas fa-angle-double-left me-1"></i>First
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page-1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                    <i class="fas fa-angle-left me-1"></i>Previous
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-angle-double-left me-1"></i>First</span>
                            </li>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="fas fa-angle-left me-1"></i>Previous</span>
                            </li>
                        <?php endif; ?>

                        <?php
                        $range = 2;
                        $start_page = max(1, $page - $range);
                        $end_page = min($total_pages, $page + $range);
                        
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
                                    Next<i class="fas fa-angle-right ms-1"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                    Last<i class="fas fa-angle-double-right ms-1"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Next<i class="fas fa-angle-right ms-1"></i></span>
                            </li>
                            <li class="page-item disabled">
                                <span class="page-link">Last<i class="fas fa-angle-double-right ms-1"></i></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-user-graduate"></i>
                <h5>No Students Found</h5>
                <p>
                    <?php if (!empty($search)): ?>
                        No students match your search criteria "<strong><?= htmlspecialchars($search) ?></strong>".
                        <br>Try adjusting your search terms or browse all students.
                    <?php else: ?>
                        There are no students in the system yet.
                        <br>Start by adding your first student to the database.
                    <?php endif; ?>
                </p>
                <?php if (!empty($search)): ?>
                    <a href="manage-students.php" class="btn btn-primary-simple me-2">
                        <i class="fas fa-list me-1"></i>View All
                    </a>
                <?php endif; ?>
                <a href="add-student.php" class="btn btn-success-simple">
                    <i class="fas fa-plus me-1"></i>Add Student
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Enhanced JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deleteStudent(id, name) {
    Swal.fire({
        title: 'Delete Student?',
        html: `
            <div class="text-start">
                <p>This action will permanently remove:</p>
                <div class="alert alert-warning">
                    <strong>${name}</strong> (ID: ${id})
                </div>
                <p class="text-muted">This action cannot be undone. All student data will be lost.</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash me-2"></i>Yes, Delete',
        cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-3',
            confirmButton: 'fw-bold',
            cancelButton: 'fw-bold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while we remove the student',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Redirect to delete
            window.location.href = `delete-student.php?id=${id}`;
        }
    });
}

// Enhanced search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="search"]');
    const searchForm = searchInput.closest('form');
    
    // Add real-time search indicator
    searchInput.addEventListener('input', function() {
        if (this.value.length > 0) {
            this.style.borderColor = '#4e73df';
            this.style.background = 'white';
        } else {
            this.style.borderColor = '#e9ecef';
            this.style.background = '#f8f9fa';
        }
    });
    
    // Focus search input on Ctrl+F
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    });
});
</script>

<?php include 'footer.php'; ?>
