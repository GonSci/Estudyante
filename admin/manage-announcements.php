<?php

session_start();

// Check authentication first
if($_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

// Include database connection first (no output)
include '../includes/db.php';

// Handle announcement actions (delete, activate, deactivate)
if(isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if($_GET['action'] == 'delete') {
        $conn->query("DELETE FROM announcements WHERE id = $id");
        header("Location: dashboard.php?msg=deleted"); // Fixed path (removed "admin/")
        exit;
    } 
    else if($_GET['action'] == 'activate') {
        $conn->query("UPDATE announcements SET is_active = 1 WHERE id = $id");
        header("Location: manage-announcements.php?msg=activated");
        exit;
    }
    else if($_GET['action'] == 'deactivate') {
        $conn->query("UPDATE announcements SET is_active = 0 WHERE id = $id");
        header("Location: manage-announcements.php?msg=deactivated");
        exit;
    }
}

// NOW include the header (after all redirects)
include 'header.php';

// Get all announcements
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Manage Announcements</h3>
                <a href="dashboard.php" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if(isset($_GET['msg'])): ?>
                <?php 
                $msg = '';
                $alert_class = 'alert-success';
                
                switch($_GET['msg']) {
                    case 'deleted':
                        $msg = 'Announcement has been deleted successfully.';
                        break;
                    case 'activated':
                        $msg = 'Announcement has been activated.';
                        break;
                    case 'deactivated':
                        $msg = 'Announcement has been deactivated.';
                        break;
                }
                ?>
                <div class="alert <?= $alert_class ?> alert-dismissible fade show" role="alert">
                    <?= $msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
                        <tr>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Created On</th>
                            <th>Expires On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($announcements && $announcements->num_rows > 0): ?>
                            <?php while($row = $announcements->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars(substr($row['message'], 0, 100)) ?>...</td>
                                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['expiry_date'])) ?></td>
                                    <td>
                                        <span class="badge <?= $row['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit-announcement.php?id=<?= $row['id'] ?>" class="btn btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if($row['is_active']): ?>
                                                <a href="?action=deactivate&id=<?= $row['id'] ?>" class="btn btn-warning" 
                                                   onclick="return confirm('Are you sure you want to deactivate this announcement?')">
                                                    <i class="fas fa-eye-slash"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="?action=activate&id=<?= $row['id'] ?>" class="btn btn-success"
                                                   onclick="return confirm('Are you sure you want to activate this announcement?')">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this announcement? This action cannot be undone.')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No announcements found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>