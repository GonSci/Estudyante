<?php include 'header.php'; ?>
<?php include '../includes/db.php'; ?>

<h3>Manage Courses</h3>

<?php
$query = "SELECT * FROM courses";
$result = $conn->query($query);

if (!$result) {
    echo "<p class='text-danger'>Failed to retrieve courses: " . $conn->error . "</p>";
    include 'footer.php';
    exit;
}

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
?>

<!-- Add Course Button -->
<a href="add-course.php" class="btn btn-sm btn-success mb-2">Add Course</a>
<a href="assign-courses-to-program.php" class="btn btn-sm btn-success mb-2">Assign Courses to Program</a>



<!-- Courses Table -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Units</th>
            <th>Prerequisites</th>
            <th>Max Capacity</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= htmlspecialchars($row['credits']) ?></td>
            <td>
                <?php
                    $prereqs = getPrerequisiteTitles($conn, $row['id']);
                    echo $prereqs ? htmlspecialchars(implode(', ', $prereqs)) : '<span class="text-muted">None</span>';
                ?>
            </td>
            <td><?= htmlspecialchars($row['max_capacity']) ?></td>
            <td>
                <a href="edit-course.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                <a 
                    href="delete-course.php?id=<?= $row['id'] ?>" 
                    class="btn btn-sm btn-danger delete-btn"
                    data-title="<?= htmlspecialchars($row['title']) ?>"
                    data-id="<?= $row['id'] ?>"
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
