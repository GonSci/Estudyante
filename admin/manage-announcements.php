<?php

session_start();

// Check authentication first
if($_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

// Include database connection first (no output)
include '../includes/db.php';

// Process announcement form submission
if(isset($_POST['announcement_submit'])) {
    $title = mysqli_real_escape_string($conn, $_POST['announcement_title']);
    $message = mysqli_real_escape_string($conn, $_POST['announcement_message']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $expiry_date = mysqli_real_escape_string($conn, $_POST['expiry_date']);
    
    // Check if $_SESSION['id'] exists, use 1 as default admin ID if not
    $admin_id = isset($_SESSION['id']) ? $_SESSION['id'] : 1;
    
    // Insert announcement into database
    $query = "INSERT INTO announcements (title, message, created_by, is_active, expiry_date) 
              VALUES ('$title', '$message', $admin_id, $is_active, '$expiry_date')";
    
    if($conn->query($query)) {
        header("Location: manage-announcements.php?msg=created");
        exit;
    } else {
        $error_message = "Error creating announcement: " . $conn->error;
    }
}

// Handle announcement actions (delete, activate, deactivate)
if(isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if($_GET['action'] == 'delete') {
        $conn->query("DELETE FROM announcements WHERE id = $id");
        header("Location: manage-announcements.php?msg=deleted");
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

<!-- Custom CSS for announcement management -->
<link rel="stylesheet" href="css/manage-announcement.css">

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-bullhorn text-primary me-2"></i>Manage Announcements
        </h1>
        <a href="dashboard.php" class="btn btn-primary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>
    <!-- Alert Messages -->
    <?php if(isset($_GET['msg'])): ?>
        <?php 
        $msg = '';
        $alert_class = 'alert-success';
        
        switch($_GET['msg']) {
            case 'created':
                $msg = 'Announcement has been created successfully!';
                break;
            case 'updated':
                $msg = 'Announcement has been updated successfully!';
                break;
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
    
    <?php if(isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Row -->
    <div class="row mb-4">
        <?php 
        $total_announcements = $announcements ? $announcements->num_rows : 0;
        $active_count = 0;
        $expired_count = 0;
        
        if($announcements) {
            $announcements->data_seek(0);
            while($row = $announcements->fetch_assoc()) {
                if($row['is_active'] && (strtotime($row['expiry_date']) >= time() || !$row['expiry_date'])) {
                    $active_count++;
                } elseif(strtotime($row['expiry_date']) < time()) {
                    $expired_count++;
                }
            }
            $announcements->data_seek(0);
        }
        ?>
        
        <!-- Total Announcements Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100 stats-card total-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-primary bg-gradient me-3">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stats-label">Total Announcements</div>
                        <div class="stats-number"><?= $total_announcements ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Active Announcements Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100 stats-card active-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-success bg-gradient me-3">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stats-label">Active Announcements</div>
                        <div class="stats-number"><?= $active_count ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Expired Announcements Card -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100 stats-card expired-card">
                <div class="card-body d-flex align-items-center">
                    <div class="stats-icon bg-warning bg-gradient me-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="stats-label">Expired Announcements</div>
                        <div class="stats-number"><?= $expired_count ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Create New Announcement Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-white">
                <i class="fas fa-plus me-2"></i>Create New Announcement
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="announcement_title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="announcement_title" 
                               name="announcement_title" placeholder="Enter announcement title..." required>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label for="announcement_message" class="form-label">Message</label>
                        <textarea class="form-control" id="announcement_message" 
                                  name="announcement_message" rows="4" 
                                  placeholder="Write your announcement message here..." required></textarea>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" id="expiry_date" 
                               name="expiry_date" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" 
                                   name="is_active" checked>
                            <label class="form-check-label" for="is_active">
                                Active Announcement
                            </label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="announcement_submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i>Create Announcement
                </button>
            </form>
        </div>
    </div>
    <!-- Announcements List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-white">
                <i class="fas fa-list me-2"></i>All Announcements (<?= $total_announcements ?>)
            </h6>
        </div>
        <div class="card-body">
            <?php if($announcements && $announcements->num_rows > 0): ?>
                <?php while($row = $announcements->fetch_assoc()): ?>
                    <?php 
                    $is_expired = strtotime($row['expiry_date']) < time();
                    $status_class = $row['is_active'] && !$is_expired ? 'active' : 'inactive';
                    ?>
                    <div class="announcement-item <?= $status_class ?>">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-2 text-dark"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="text-muted mb-2"><?= htmlspecialchars(substr($row['message'], 0, 150)) ?><?= strlen($row['message']) > 150 ? '...' : '' ?></p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>Created: <?= date('M d, Y', strtotime($row['created_at'])) ?>
                                    <span class="ms-3"><i class="fas fa-clock me-1"></i>Expires: <?= date('M d, Y', strtotime($row['expiry_date'])) ?></span>
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="mb-2">
                                    <span class="badge <?= $row['is_active'] && !$is_expired ? 'bg-success' : ($is_expired ? 'bg-danger' : 'bg-secondary') ?>">
                                        <?= $row['is_active'] && !$is_expired ? 'Active' : ($is_expired ? 'Expired' : 'Inactive') ?>
                                    </span>
                                </div>
                                <div class="action-buttons">
                                    <a href="edit-announcement.php?id=<?= $row['id'] ?>" 
                                       class="btn btn-primary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <?php if($row['is_active']): ?>
                                        <button onclick="deactivateAnnouncement(<?= $row['id'] ?>)" 
                                           class="btn btn-warning btn-sm" title="Deactivate">
                                            <i class="fas fa-eye-slash"></i>
                                        </button>
                                    <?php else: ?>
                                        <button onclick="activateAnnouncement(<?= $row['id'] ?>)" 
                                           class="btn btn-success btn-sm" title="Activate">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button onclick="deleteAnnouncement(<?= $row['id'] ?>)" 
                                       class="btn btn-danger btn-sm" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Announcements Yet</h5>
                    <p class="text-muted">Create your first announcement using the form above.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Function to activate announcement
function activateAnnouncement(id) {
    Swal.fire({
        title: 'Activate Announcement?',
        text: 'This announcement will become visible to students.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-eye me-1"></i>Yes, Activate',
        cancelButtonText: '<i class="fas fa-times me-1"></i>Cancel',
        customClass: {
            confirmButton: 'btn btn-success me-2',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Activating...',
                text: 'Please wait while we activate the announcement.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Redirect to activate
            window.location.href = '?action=activate&id=' + id;
        }
    });
}

// Function to deactivate announcement
function deactivateAnnouncement(id) {
    Swal.fire({
        title: 'Deactivate Announcement?',
        text: 'This announcement will be hidden from students.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-eye-slash me-1"></i>Yes, Deactivate',
        cancelButtonText: '<i class="fas fa-times me-1"></i>Cancel',
        customClass: {
            confirmButton: 'btn btn-warning me-2',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deactivating...',
                text: 'Please wait while we deactivate the announcement.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Redirect to deactivate
            window.location.href = '?action=deactivate&id=' + id;
        }
    });
}

// Function to delete announcement
function deleteAnnouncement(id) {
    Swal.fire({
        title: 'Delete Announcement?',
        text: 'This action cannot be undone! The announcement will be permanently removed.',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash me-1"></i>Yes, Delete',
        cancelButtonText: '<i class="fas fa-times me-1"></i>Cancel',
        customClass: {
            confirmButton: 'btn btn-danger me-2',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while we delete the announcement.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Redirect to delete
            window.location.href = '?action=delete&id=' + id;
        }
    });
}

// Show success messages with SweetAlert2
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const msg = urlParams.get('msg');
    
    if (msg) {
        let title = 'Success!';
        let text = '';
        let icon = 'success';
        
        switch(msg) {
            case 'created':
                text = 'Announcement has been created successfully!';
                break;
            case 'updated':
                text = 'Announcement has been updated successfully!';
                break;
            case 'deleted':
                text = 'Announcement has been deleted successfully.';
                break;
            case 'activated':
                text = 'Announcement has been activated.';
                break;
            case 'deactivated':
                text = 'Announcement has been deactivated.';
                break;
        }
        
        if (text) {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonColor: '#4e73df',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: true,
                confirmButtonText: 'Great!'
            }).then(() => {
                // Clean URL after showing success message
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }
    }
});
</script>

<?php include 'footer.php'; ?>