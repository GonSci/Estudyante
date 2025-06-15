<?php include 'header.php'; ?>
<?php include '../includes/db.php'; ?>

<h3>Add New Course</h3>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = trim($_POST['title']);
    $description   = trim($_POST['description']);
    $credits       = intval($_POST['credits']);
    $prerequisites = trim($_POST['prerequisites']);
    $max_capacity  = intval($_POST['max_capacity']);

    if (empty($title) || empty($description) || empty($credits) || empty($max_capacity)) {
        echo "<p class='text-danger'>Please fill in all required fields.</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO courses (title, description, credits, prerequisites, max_capacity) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisi", $title, $description, $credits, $prerequisites, $max_capacity);
        if ($stmt->execute()) {
            header("Location: manage-courses.php?success=1");
            exit;
        } else {
            echo "<p class='text-danger'>Failed to add course: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}
?>

<form method="POST" action="">
    <div class="mb-3">
        <label>Title:</label>
        <input type="text" name="title" class="form-control" required value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
    </div>
    <div class="mb-3">
        <label>Description:</label>
        <textarea name="description" class="form-control" required><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
    </div>
    <div class="mb-3">
        <label>Units:</label>
        <input type="number" name="credits" class="form-control" min="1" required value="<?= isset($_POST['credits']) ? htmlspecialchars($_POST['credits']) : '' ?>">
    </div>
    <div class="mb-3">
        <label>Prerequisites:</label>
        <input type="text" name="prerequisites" class="form-control" value="<?= isset($_POST['prerequisites']) ? htmlspecialchars($_POST['prerequisites']) : '' ?>">
    </div>
    <div class="mb-3">
        <label>Max Capacity:</label>
        <input type="number" name="max_capacity" class="form-control" min="1" required value="<?= isset($_POST['max_capacity']) ? htmlspecialchars($_POST['max_capacity']) : '' ?>">
    </div>
    <button type="submit" class="btn btn-primary">Add Course</button>
    <a href="manage-courses.php" class="btn btn-secondary">Back</a>
</form>

<?php include 'footer.php'; ?>