<?php

// Use output buffering to prevent "headers already sent" errors
ob_start();

// Check if session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debug session data (as HTML comment so it doesn't affect headers)
echo "<!-- Debug: Session data = " . print_r($_SESSION, true) . " -->";

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

include 'header.php';
include '../includes/db.php';

// Process announcement form submission
if(isset($_POST['announcement_submit'])) {
    $title = mysqli_real_escape_string($conn, $_POST['announcement_title']);
    $message = mysqli_real_escape_string($conn, $_POST['announcement_message']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $expiry_date = mysqli_real_escape_string($conn, $_POST['expiry_date']);
    
    // Check if the announcements table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'announcements'");
    if ($table_check->num_rows == 0) {
        // Create the announcements table if it doesn't exist
        $create_table = "CREATE TABLE `announcements` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `message` text NOT NULL,
            `created_by` int(11) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `is_active` tinyint(1) NOT NULL DEFAULT '1',
            `expiry_date` date DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if (!$conn->query($create_table)) {
            $error_message = "Failed to create announcements table: " . $conn->error;
        }
    }
    
    // Check if $_SESSION['id'] exists, use 1 as default admin ID if not
    $admin_id = isset($_SESSION['id']) ? $_SESSION['id'] : 1;
    
    // Insert announcement into database
    $query = "INSERT INTO announcements (title, message, created_by, is_active, expiry_date) 
              VALUES ('$title', '$message', $admin_id, $is_active, '$expiry_date')";
    
    if($conn->query($query)) {
        $_SESSION['success_message'] = "Announcement created successfully!";
        // Redirect to avoid form resubmission
        header("Location: dashboard.php");
        exit;
    } else {
        $error_message = "Error creating announcement: " . $conn->error;
    }
}

// Fetch recent announcements
$announcements = null;
$table_check = $conn->query("SHOW TABLES LIKE 'announcements'");
if ($table_check->num_rows > 0) {
    $announcements_query = "SELECT a.* FROM announcements a ORDER BY a.created_at DESC LIMIT 5";
    $announcements = $conn->query($announcements_query);
    
    if (!$announcements) {
        echo "<!-- Query error: " . $conn->error . " -->";
    }
}
?>

<div class="container mt-4">
    <!-- Announcements Section -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
            <h3 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Announcements</h3>
        </div>
        <div class="card-body">
            <?php if(isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if(isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-6">
                    <h5><i class="fas fa-plus-circle me-2 text-primary"></i>Create New Announcement</h5>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="announcement_title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="announcement_title" name="announcement_title" required>
                        </div>
                        <div class="mb-3">
                            <label for="announcement_message" class="form-label">Message</label>
                            <textarea class="form-control" id="announcement_message" name="announcement_message" rows="4" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date" 
                                       min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                    <label class="form-check-label" for="is_active">
                                        Active Announcement
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="announcement_submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Publish Announcement
                        </button>
                    </form>
                </div>
                
                <div class="col-lg-6">
                    <h5><i class="fas fa-history me-2 text-primary"></i>Recent Announcements</h5>
                    <?php if($announcements && $announcements->num_rows > 0): ?>
                        <div class="list-group">
                            <?php while($row = $announcements->fetch_assoc()): ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($row['title']) ?></h6>
                                        <small class="text-muted">
                                            <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?= htmlspecialchars(substr($row['message'], 0, 100)) ?>...</p>
                                    <small class="text-muted">
                                        Status: <span class="badge <?= $row['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </small>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <div class="mt-3 text-end">
                            <a href="manage-announcements.php" class="btn btn-sm btn-outline-primary">
                                Manage All Announcements
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No announcements have been created yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Original Dashboard Content -->
    <div class="jumbotron">
        <h2>Navigations</h2>
        <p class="lead">Use the sidebar to manage students, courses, and view registrations.</p>
        <hr class="my-4">
    </div>
    
    <!-- Quick access cards -->
    <div class="row mt-4">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manage Courses</h5>
                    <p class="card-text">Add, edit, or delete courses in the system.</p>
                    <a href="manage-courses.php" class="btn btn-primary">Go to Courses</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Manage Students</h5>
                    <p class="card-text">View and manage student accounts.</p>
                    <a href="manage-students.php" class="btn btn-primary">Go to Students</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">View Curriculum</h5>
                    <p class="card-text">View the complete program curriculum.</p>
                    <a href="view-curriculum.php" class="btn btn-primary">View Curriculum</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>


