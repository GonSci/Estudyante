<?php
// Start session first
session_start();

// Include database connection
include '../includes/db.php';

// Check if student is logged in (optional security check)
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
    header("Location: ../login.php");
    exit;
}

// Now include the navbar (after session and auth check)
include 'navbar.php';

// Get active announcements that haven't expired
$announcements = $conn->query("
    SELECT * FROM announcements
    WHERE is_active = 1 
    AND (expiry_date >= CURDATE() OR expiry_date IS NULL)
    ORDER BY created_at DESC
");

// Debug query
if (!$announcements) {
    echo "<!-- Query error: " . $conn->error . " -->";
}
?>

<!-- Rest of your HTML content -->
<div class="container mt-4">
    <!-- Display announcements to students -->
    <?php if($announcements && $announcements->num_rows > 0): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>📢 Announcements</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php while($row = $announcements->fetch_assoc()): ?>
                        <div class="list-group-item">
                            <h5 class="mb-1"><?= htmlspecialchars($row['title']) ?></h5>
                            <p class="mb-1"><?= nl2br(htmlspecialchars($row['message'])) ?></p>
                            <small class="text-muted">
                                Posted on <?= date('F j, Y', strtotime($row['created_at'])) ?>
                            </small>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Rest of your dashboard content -->
</div>

<?php include 'footer.php'; ?>
