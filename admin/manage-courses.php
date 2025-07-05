<?php include 'header.php'; ?>
<?php include '../includes/db.php'; ?>

<link rel="stylesheet" href="css/admin-common.css">

<?php
$program_filter = isset($_GET['program']) ? trim($_GET['program']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'title';
$sort_order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc';

$allowed_sorts = ['title', 'credits', 'year_level', 'academic_term', 'max_capacity'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'title';
}

$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

$count_query = "SELECT COUNT(DISTINCT c.id) as total FROM courses c";

if (!empty($program_filter)) {
    $count_query .= " JOIN program_course pc ON c.id = pc.course_id 
                WHERE pc.program_code = '" . $conn->real_escape_string($program_filter) . "'";
}

$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

if ($sort_by === 'year_level' || $sort_by === 'academic_term') {
    $query = "SELECT DISTINCT c.*, pc.year_level, pc.academic_term FROM courses c 
              LEFT JOIN program_course pc ON c.id = pc.course_id";
} else {
    $query = "SELECT DISTINCT c.* FROM courses c";
}

if (!empty($program_filter)) {
    if ($sort_by === 'year_level' || $sort_by === 'academic_term') {
        $query .= " WHERE pc.program_code = '" . $conn->real_escape_string($program_filter) . "'";
    } else {
        $query .= " JOIN program_course pc ON c.id = pc.course_id 
                    WHERE pc.program_code = '" . $conn->real_escape_string($program_filter) . "'";
    }
}

if ($sort_by === 'year_level' || $sort_by === 'academic_term') {
    $query .= " ORDER BY pc.$sort_by $sort_order, c.title ASC";
} else {
    $query .= " ORDER BY c.$sort_by $sort_order";
}

$query .= " LIMIT $offset, $records_per_page";
$result = $conn->query($query);

if (!$result) {
    echo "<p class='text-danger'>Failed to retrieve courses: " . $conn->error . "</p>";
    include 'footer.php';
    exit;
}

$result_count = $result->num_rows;

function getPrerequisiteTitles($conn, $course_id) {
    $stmt = $conn->prepare("SELECT c.title FROM course_prerequisites cp JOIN courses c ON cp.prerequisite_id = c.id WHERE cp.course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $titles = [];
    while ($row = $result->fetch_assoc()) {
        $titles[] = $row['title'];
    }
    $stmt->close();
    return $titles;
}

function getAssignedPrograms($conn, $course_id) {
    $stmt = $conn->prepare("SELECT program_code FROM program_course WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $programs = [];
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row['program_code'];
    }
    $stmt->close();
    return $programs;
}

function getCourseYearAndTerm($conn, $course_id) {
    $stmt = $conn->prepare("SELECT DISTINCT year_level, academic_term FROM program_course WHERE course_id = ? ORDER BY year_level, academic_term");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row['year_level'] . ' - ' . $row['academic_term'];
    }
    $stmt->close();
    return $data;
}

function buildPageUrl($page_num, $program_filter, $sort_by, $sort_order) {
    $params = ['page' => $page_num];
    if (!empty($program_filter)) {
        $params['program'] = $program_filter;
    }
    if ($sort_by !== 'title') {
        $params['sort'] = $sort_by;
    }
    if ($sort_order !== 'asc') {
        $params['order'] = $sort_order;
    }
    return '?' . http_build_query($params);
}
?>

<div class="container-fluid">
    <!-- Modern Page Header -->
    <div class="page-header">                    <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-book me-2"></i>Course Management</h2>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="add-course.php" class="btn btn-success-simple">
                        <i class="fas fa-plus me-1"></i>Add Course
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="text-muted mb-3 small">
            <?php if (!empty($program_filter)): ?>
                <i class="fas fa-filter me-1"></i>
                Found <strong><?= $total_records ?></strong> course<?= ($total_records != 1) ? 's' : '' ?> for program 
                "<strong><?= htmlspecialchars($program_filter) ?></strong>" 
                (Showing <?= $result_count ?> on page <?= $page ?> of <?= $total_pages ?>)
            <?php else: ?>
                <i class="fas fa-books me-1"></i>
                Showing <strong><?= $result_count ?></strong> of <strong><?= $total_records ?></strong> course<?= ($total_records != 1) ? 's' : '' ?>
                (Page <?= $page ?> of <?= $total_pages ?>)
            <?php endif; ?>
            
            <?php if ($sort_by !== 'title' || $sort_order !== 'asc'): ?>
                <span class="ms-2 text-primary">
                    <i class="fas fa-sort me-1"></i>
                    Sorted by: <strong>
                        <?php
                        switch($sort_by) {
                            case 'title': echo 'Course Title'; break;
                            case 'credits': echo 'Credits'; break;
                            case 'year_level': echo 'Year Level'; break;
                            case 'academic_term': echo 'Academic Term'; break;
                            case 'max_capacity': echo 'Max Capacity'; break;
                        }
                        ?>
                    </strong> (<?= ucfirst($sort_order) ?>)
                </span>
            <?php endif; ?>
        </div>

        <div class="sort-controls">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="fw-bold text-muted">
                    <i class="fas fa-sort me-1"></i>Sort by:
                </span>
                
                <?php
                function getSortUrl($field, $current_sort, $current_order, $program_filter) {
                    $new_order = ($current_sort === $field && $current_order === 'asc') ? 'desc' : 'asc';
                    $params = ['sort' => $field, 'order' => $new_order];
                    if (!empty($program_filter)) {
                        $params['program'] = $program_filter;
                    }
                    return '?' . http_build_query($params);
                }
                
                function getSortIcon($field, $current_sort, $current_order) {
                    if ($current_sort === $field) {
                        return $current_order === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
                    }
                    return 'fas fa-sort';
                }
                
                $sort_options = [
                    'title' => 'Course Title',
                    'credits' => 'Credits',
                    'year_level' => 'Year Level',
                    'academic_term' => 'Academic Term',
                    'max_capacity' => 'Max Capacity'
                ];
                
                foreach ($sort_options as $field => $label):
                    $is_active = $sort_by === $field;
                    $url = getSortUrl($field, $sort_by, $sort_order, $program_filter);
                    $icon = getSortIcon($field, $sort_by, $sort_order);
                ?>
                    <a href="<?= $url ?>" class="sort-link <?= $is_active ? 'active' : '' ?>">
                        <?= $label ?>
                        <i class="<?= $icon ?> sort-arrow"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="search-section">
            <form method="GET" action="" class="row align-items-center">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort_by) ?>">
                <input type="hidden" name="order" value="<?= htmlspecialchars($sort_order) ?>">
                <div class="col-md-8">
                    <label for="program_filter" class="form-label">Filter by Program:</label>
                    <select name="program" id="program_filter" class="form-select">
                        <option value="">All Programs</option>
                        <?php
                        $programs_query = $conn->query("SELECT program_code, program_name FROM programs ORDER BY program_code");
                        while ($program = $programs_query->fetch_assoc()):
                        ?>
                            <option value="<?= htmlspecialchars($program['program_code']) ?>" 
                                    <?= (isset($_GET['program']) && $_GET['program'] == $program['program_code']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($program['program_code']) ?> - <?= htmlspecialchars($program['program_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4 mt-2 mt-md-0">
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary-simple">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <?php if(isset($_GET['program']) && !empty($_GET['program'])): ?>
                            <a href="<?= buildPageUrl(1, '', $sort_by, $sort_order) ?>" class="btn btn-secondary-simple">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($result_count > 0): ?>
            <div class="courses-table-container">
                <div class="table-header">
                    <h5><i class="fas fa-table me-2"></i>Course Records (<?= $result_count ?>)</h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table modern-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-book me-1"></i>Course</th>
                                <th><i class="fas fa-align-left me-1"></i>Description</th>
                                <th><i class="fas fa-credit-card me-1"></i>Credits</th>
                                <th><i class="fas fa-layer-group me-1"></i>Year/Term</th>
                                <th><i class="fas fa-link me-1"></i>Prerequisites</th>
                                <th><i class="fas fa-users me-1"></i>Capacity</th>
                                <th><i class="fas fa-graduation-cap me-1"></i>Programs</th>
                                <th><i class="fas fa-cogs me-1"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Course">
                                    <div class="course-title"><?= htmlspecialchars($row['title']) ?></div>
                                    <div class="course-code">ID: <?= $row['id'] ?></div>
                                </td>
                                <td data-label="Description">
                                    <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['description']) ?>">
                                        <?= htmlspecialchars(substr($row['description'], 0, 100)) ?>
                                        <?= strlen($row['description']) > 100 ? '...' : '' ?>
                                    </div>
                                </td>
                                <td data-label="Credits">
                                    <span class="credits-badge">
                                        <?= htmlspecialchars($row['credits']) ?> units
                                    </span>
                                </td>
                                <td data-label="Year/Term">
                                    <?php
                                        $yearTerms = getCourseYearAndTerm($conn, $row['id']);
                                        if ($yearTerms):
                                            foreach($yearTerms as $yearTerm):
                                    ?>
                                        <span class="year-term-badge"><?= htmlspecialchars($yearTerm) ?></span>
                                    <?php 
                                            endforeach;
                                        else:
                                    ?>
                                        <span class="text-muted"><i class="fas fa-minus"></i> Not Set</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Prerequisites">
                                    <?php
                                        $prereqs = getPrerequisiteTitles($conn, $row['id']);
                                        if ($prereqs):
                                            foreach($prereqs as $prereq):
                                    ?>
                                        <span class="prerequisite-item"><?= htmlspecialchars($prereq) ?></span>
                                    <?php 
                                            endforeach;
                                        else:
                                    ?>
                                        <span class="text-muted"><i class="fas fa-minus"></i> None</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Capacity">
                                    <span class="capacity-badge">
                                        <i class="fas fa-users me-1"></i><?= htmlspecialchars($row['max_capacity']) ?>
                                    </span>
                                </td>
                                <td data-label="Programs">
                                    <?php
                                        $programs = getAssignedPrograms($conn, $row['id']);
                                        if ($programs):
                                            foreach($programs as $program):
                                    ?>
                                        <span class="program-badge"><?= htmlspecialchars($program) ?></span>
                                    <?php 
                                            endforeach;
                                        else:
                                    ?>
                                        <span class="text-muted"><i class="fas fa-minus"></i> None</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Actions">
                                    <div class="action-buttons">
                                        <a href="edit-course.php?id=<?= $row['id'] ?>" class="btn-action btn-edit">
                                            <i class="fas fa-edit me-1"></i>
                                        </a>
                                        <button 
                                            onclick="deleteCourse(<?= $row['id'] ?>, '<?= htmlspecialchars($row['title']) ?>')"
                                            class="btn-action btn-delete">
                                            <i class="fas fa-trash me-1"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav>
                    <ul class="pagination modern-pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= buildPageUrl(1, $program_filter, $sort_by, $sort_order) ?>">
                                    <i class="fas fa-angle-double-left me-1"></i>First
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="<?= buildPageUrl($page-1, $program_filter, $sort_by, $sort_order) ?>">
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
                                <a class="page-link" href="<?= buildPageUrl($i, $program_filter, $sort_by, $sort_order) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= buildPageUrl($page+1, $program_filter, $sort_by, $sort_order) ?>">
                                    Next<i class="fas fa-angle-right ms-1"></i>
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="<?= buildPageUrl($total_pages, $program_filter, $sort_by, $sort_order) ?>">
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
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <h5>No Courses Found</h5>
                <p>
                    <?php if (!empty($program_filter)): ?>
                        No courses found for program "<strong><?= htmlspecialchars($program_filter) ?></strong>".
                        <br>Try selecting a different program or browse all courses.
                    <?php else: ?>
                        There are no courses in the system yet.
                        <br>Start by adding your first course to the database.
                    <?php endif; ?>
                </p>
                <?php if (!empty($program_filter)): ?>
                    <a href="manage-courses.php" class="btn btn-primary-simple me-2">
                        <i class="fas fa-list me-1"></i>View All
                    </a>
                <?php endif; ?>
                <a href="add-course.php" class="btn btn-success-simple">
                    <i class="fas fa-plus me-1"></i>Add Course
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deleteCourse(id, title) {
    Swal.fire({
        title: 'Delete Course?',
        html: `
            <div class="text-start">
                <p>This action will permanently remove:</p>
                <div class="alert alert-warning">
                    <strong>${title}</strong> (ID: ${id})
                </div>
                <p class="text-muted">This action cannot be undone. All course data will be lost.</p>
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
                text: 'Please wait while we remove the course',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            window.location.href = `delete-course.php?id=${id}`;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const programSelect = document.querySelector('select[name="program"]');
    const filterForm = programSelect.closest('form');
    
    programSelect.addEventListener('change', function() {
        if (this.value) {
            this.style.borderColor = '#4e73df';
            this.style.background = 'white';
        } else {
            this.style.borderColor = '#e9ecef';
            this.style.background = '#f8f9fa';
        }
    });
    
    programSelect.addEventListener('change', function() {
        filterForm.submit();
    });
    
    const sortLinks = document.querySelectorAll('.sort-link');
    sortLinks.forEach(link => {
        link.addEventListener('click', function() {
            const icon = this.querySelector('.sort-arrow');
            if (icon) {
                icon.className = 'fas fa-spinner fa-spin sort-arrow';
            }
        });
    });
});
</script>

<?php include 'footer.php'; ?>
