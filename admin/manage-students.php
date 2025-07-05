<?php include 'header.php'; ?>
<?php include '../includes/db.php'; ?>

<!-- Simple Clean Styling -->
<style>
/* Modern page header */
.page-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem 0;
    margin: -1.5rem -1.5rem 1.5rem -1.5rem;
    border-bottom: 4px solid #4e73df;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.page-header h2 {
    margin: 0;
    color: #2c3e50;
    font-weight: 700;
    font-size: 1.75rem;
}

.page-header .btn {
    box-shadow: 0 2px 8px rgba(28, 200, 138, 0.3);
    transition: all 0.3s ease;
}

.page-header .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(28, 200, 138, 0.4);
}

/* Simple search area */
.search-section {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
}

/* Simple buttons */
.btn-simple {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    text-decoration: none;
    border: none;
}

.btn-primary-simple {
    background: #4e73df;
    color: white;
}

.btn-primary-simple:hover {
    background: #2e59d9;
    color: white;
}

.btn-success-simple {
    background: #1cc88a;
    color: white;
}

.btn-success-simple:hover {
    background: #17a673;
    color: white;
}

.btn-secondary-simple {
    background: #6c757d;
    color: white;
}

.btn-secondary-simple:hover {
    background: #545b62;
    color: white;
}

.students-table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.table-header {
    background: #4e73df;
    color: white;
    padding: 1rem;
    margin: 0;
}

.table-header h5 {
    margin: 0;
    font-weight: 600;
}

.modern-table {
    margin: 0;
    background: white;
}

.modern-table thead th {
    background: #f8f9fa;
    border: none;
    padding: 1rem;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}

.modern-table tbody tr {
    border: none;
    transition: all 0.3s ease;
}

.modern-table tbody tr:hover {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    transform: scale(1.01);
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.modern-table tbody td {
    padding: 1rem;
    border: none;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
}

.student-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4e73df, #224abe);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.student-info {
    display: flex;
    align-items: center;
}

.student-details h6 {
    margin: 0;
    font-weight: 600;
    color: #2c3e50;
}

.student-details small {
    color: #6c757d;
    font-size: 0.875rem;
}

.program-badge {
    background: linear-gradient(135deg, #36b9cc, #2c9faf);
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-action {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-edit {
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: white;
}

.btn-edit:hover {
    background: linear-gradient(135deg, #224abe, #1e3a8a);
    color: white;
    transform: translateY(-1px);
}

.btn-delete {
    background: linear-gradient(135deg, #e74a3b, #c82333);
    color: white;
}

.btn-delete:hover {
    background: linear-gradient(135deg, #c82333, #a71e2a);
    color: white;
    transform: translateY(-1px);
}

.pagination-container {
    text-align: center;
    margin-top: 1rem;
}

.modern-pagination {
    margin: 0;
    justify-content: center;
}

.modern-pagination .page-item .page-link {
    border: 1px solid #dee2e6;
    color: #495057;
    padding: 0.5rem 0.75rem;
    margin: 0 0.125rem;
    border-radius: 4px;
}

.modern-pagination .page-item .page-link:hover {
    border-color: #4e73df;
    background: #4e73df;
    color: white;
}

.modern-pagination .page-item.active .page-link {
    background: #4e73df;
    border-color: #4e73df;
    color: white;
}

.modern-pagination .page-item.disabled .page-link {
    color: #adb5bd;
    background: #f8f9fa;
    border-color: #dee2e6;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.empty-state i {
    font-size: 3rem;
    color: #adb5bd;
    margin-bottom: 1rem;
}

.empty-state h5 {
    color: #495057;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6c757d;
    margin-bottom: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header {
        padding: 1rem 0;
        margin: -1rem -1rem 1rem -1rem;
    }
    
    .page-header h2 {
        font-size: 1.5rem;
    }
    
    .search-section {
        padding: 0.75rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-action {
        width: 100%;
        text-align: center;
    }
    
    .student-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .modern-table tbody td {
        padding: 0.75rem 0.5rem;
    }
    
    /* Mobile table optimizations */
    .modern-table thead th {
        padding: 0.75rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .student-avatar {
        width: 35px;
        height: 35px;
        font-size: 0.8rem;
        margin-right: 0.5rem;
    }
    
    .student-details h6 {
        font-size: 0.9rem;
    }
    
    .program-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
}

@media (max-width: 576px) {
    .modern-pagination .page-item .page-link {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Stack table for very small screens */
    .modern-table {
        font-size: 0.85rem;
    }
    
    .modern-table thead {
        display: none;
    }
    
    .modern-table tbody,
    .modern-table tbody tr,
    .modern-table tbody td {
        display: block;
        width: 100%;
    }
    
    .modern-table tbody tr {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 1rem;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .modern-table tbody tr:hover {
        transform: none;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .modern-table tbody td {
        border: none;
        padding: 0.5rem 0;
        position: relative;
        padding-left: 35%;
    }
    
    .modern-table tbody td::before {
        content: attr(data-label);
        position: absolute;
        left: 0;
        top: 0.5rem;
        width: 30%;
        font-weight: 600;
        color: #495057;
        font-size: 0.8rem;
        text-transform: uppercase;
    }
    
    .student-info {
        flex-direction: row;
        align-items: center;
        gap: 0.5rem;
    }
    
    .action-buttons {
        flex-direction: row;
        gap: 0.25rem;
    }
    
    .btn-action {
        width: auto;
        font-size: 0.8rem;
        padding: 0.375rem 0.75rem;
    }
}
</style>

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
