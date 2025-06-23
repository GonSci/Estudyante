<?php include 'header.php'; ?>
<?php include '../includes/db.php'; ?>

<h3>Manage Courses</h3>

<?php
// Get filter parameter
$program_filter = isset($_GET['program']) ? trim($_GET['program']) : '';

// Pagination settings
$records_per_page = 10; // Number of courses per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Base query for counting total records
$count_query = "SELECT COUNT(DISTINCT c.id) as total FROM courses c";

// Add join and where clause if filtering by program
if (!empty($program_filter)) {
    $count_query .= " JOIN program_course pc ON c.id = pc.course_id 
                WHERE pc.program_code = '" . $conn->real_escape_string($program_filter) . "'";
}

$count_result = $conn->query($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Base query for fetching courses with pagination
$query = "SELECT DISTINCT c.* FROM courses c";

// Add join and where clause if filtering by program
if (!empty($program_filter)) {
    $query .= " JOIN program_course pc ON c.id = pc.course_id 
                WHERE pc.program_code = '" . $conn->real_escape_string($program_filter) . "'";
}

$query .= " ORDER BY c.title LIMIT $offset, $records_per_page";
$result = $conn->query($query);

if (!$result) {
    echo "<p class='text-danger'>Failed to retrieve courses: " . $conn->error . "</p>";
    include 'footer.php';
    exit;
}

// Count displayed results
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
?>

<style>
    table th {
        background-color: blue;
        color: white;
    }
</style>

<!-- Top controls -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="add-course.php" class="btn btn-success">Add Course</a>
    
    <!-- Program Filter -->
    <div>
        <form method="GET" action="" class="d-flex align-items-center">
            <label for="program_filter" class="me-2">Filter by Program:</label>
            <select name="program" id="program_filter" class="form-select me-2" style="width: 150px;">
                <option value="">All Programs</option>
                <?php
                // Get unique programs
                $programs_query = $conn->query("SELECT program_code, program_name FROM programs ORDER BY program_code");
                while ($program = $programs_query->fetch_assoc()):
                ?>
                    <option value="<?= htmlspecialchars($program['program_code']) ?>" 
                            <?= (isset($_GET['program']) && $_GET['program'] == $program['program_code']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($program['program_code']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <?php if(isset($_GET['program']) && !empty($_GET['program'])): ?>
                <a href="manage-courses.php" class="btn btn-secondary btn-sm ms-1">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Results count -->
<?php if (!empty($program_filter)): ?>
    <div class="alert alert-info py-2 mb-3">
        Showing <?= $result->num_rows ?> of <?= $total_records ?> course<?= ($total_records != 1) ? 's' : '' ?> 
        for program: <strong><?= htmlspecialchars($program_filter) ?></strong>
        (Page <?= $page ?> of <?= $total_pages ?>)
    </div>
<?php else: ?>
    <div class="alert alert-info py-2 mb-3">
        Showing <?= $result->num_rows ?> of <?= $total_records ?> course<?= ($total_records != 1) ? 's' : '' ?>
        (Page <?= $page ?> of <?= $total_pages ?>)
    </div>
<?php endif; ?>

<?php if ($result_count == 0): ?>
    <div class="alert alert-warning">
        No courses found<?= !empty($program_filter) ? ' for program ' . htmlspecialchars($program_filter) : '' ?>.
    </div>
<?php else: ?>
    <!-- Responsive Table Container -->
    <div class="table-responsive">
        <!-- Courses Table -->
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Units</th>
                    <th>Prerequisites</th>
                    <th>Max Capacity</th>
                    <th>Programs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td>
                        <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($row['description']) ?>">
                            <?= htmlspecialchars($row['description']) ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($row['credits']) ?></td>
                    <td>
                        <?php
                            $prereqs = getPrerequisiteTitles($conn, $row['id']);
                            echo $prereqs ? htmlspecialchars(implode(', ', $prereqs)) : '<span class="text-muted">None</span>';
                        ?>
                    </td>
                    <td><?= htmlspecialchars($row['max_capacity']) ?></td>
                    <td>
                        <?php
                            $programs = getAssignedPrograms($conn, $row['id']);
                            echo $programs ? htmlspecialchars(implode(', ', $programs)) : '<span class="text-muted">None</span>';
                        ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="edit-course.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="delete-course.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger delete-btn"
                                data-title="<?= htmlspecialchars($row['title']) ?>"
                                data-id="<?= $row['id'] ?>"
                            >Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Course pagination">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=1<?= !empty($program_filter) ? '&program=' . urlencode($program_filter) : '' ?>">
                        First
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page-1 ?><?= !empty($program_filter) ? '&program=' . urlencode($program_filter) : '' ?>">
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
                    <a class="page-link" href="?page=<?= $i ?><?= !empty($program_filter) ? '&program=' . urlencode($program_filter) : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page+1 ?><?= !empty($program_filter) ? '&program=' . urlencode($program_filter) : '' ?>">
                        Next
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $total_pages ?><?= !empty($program_filter) ? '&program=' . urlencode($program_filter) : '' ?>">
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
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            const title = this.getAttribute('data-title');
            const id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Are you sure?',
                html: `This will permanently remove the course <b>${title} (ID: ${id})</b>. Continue?`,
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
