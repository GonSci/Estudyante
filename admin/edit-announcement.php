<?php
session_start();


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


include '../includes/db.php';


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage-announcements.php");
    exit;
}

$announcement_id = (int)$_GET['id'];
$error_message = '';
$success_message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['announcement_submit'])) {
    
    $title = trim($_POST['announcement_title']);
    $message = trim($_POST['announcement_message']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $expiry_date = $_POST['expiry_date'];
    
    if (empty($title)) {
        $error_message = "Title is required.";
    } elseif (empty($message)) {
        $error_message = "Message is required.";
    } elseif (empty($expiry_date)) {
        $error_message = "Expiry date is required.";
    } else {
        // Update announcement in database
        $query = "UPDATE announcements SET title = ?, message = ?, is_active = ?, expiry_date = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("ssisi", $title, $message, $is_active, $expiry_date, $announcement_id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $stmt->close();
                    header("Location: manage-announcements.php?msg=updated");
                    exit;
                } else {
                    $error_message = "No changes were made or announcement not found.";
                }
            } else {
                $error_message = "Error updating announcement: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Database error: " . $conn->error;
        }
    }
}

$query = "SELECT * FROM announcements WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $announcement_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $announcement = $result->fetch_assoc();
} else {
    header("Location: manage-announcements.php");
    exit;
}
$stmt->close();

include 'header.php';
?>

<link rel="stylesheet" href="css/edit-announcement.css">

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit text-primary me-2"></i>Edit Announcement
        </h1>
        <a href="manage-announcements.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Announcements
        </a>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Announcement Status Info -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info d-flex align-items-center">
                <i class="fas fa-info-circle me-2"></i>
                <div>
                    <strong>Current Status:</strong> 
                    <span class="badge <?= $announcement['is_active'] ? 'bg-success' : 'bg-secondary' ?> ms-2">
                        <?= $announcement['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                    <span class="ms-3">
                        <strong>Created:</strong> <?= date('M d, Y g:i A', strtotime($announcement['created_at'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Announcement Form -->
    <div class="card shadow mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-pencil-alt me-2"></i>Edit Announcement Details
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="" id="editAnnouncementForm">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="announcement_title" class="form-label required">Title</label>
                        <input type="text" class="form-control" id="announcement_title" 
                               name="announcement_title" placeholder="Enter announcement title..." 
                               value="<?= htmlspecialchars($announcement['title']) ?>" required>
                        <div class="form-text">A clear, descriptive title for your announcement</div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label for="announcement_message" class="form-label required">Message</label>
                        <textarea class="form-control" id="announcement_message" 
                                  name="announcement_message" rows="6" 
                                  placeholder="Write your announcement message here..." required><?= htmlspecialchars($announcement['message']) ?></textarea>
                        <div class="form-text">
                            <span id="char-count">0</span> characters | 
                            <span id="word-count">0</span> words
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="expiry_date" class="form-label required">Expiry Date</label>
                        <input type="date" class="form-control" id="expiry_date" 
                               name="expiry_date" 
                               value="<?= date('Y-m-d', strtotime($announcement['expiry_date'])) ?>" required>
                        <div class="form-text">When this announcement should stop being displayed</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status</label>
                        <div class="status-options">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" <?= $announcement['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    <i class="fas fa-eye me-1"></i>Active Announcement
                                </label>
                                <div class="form-text">Uncheck to hide this announcement from students</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="announcement_submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-1"></i>Update Announcement
                    </button>
                    <a href="manage-announcements.php" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <button type="button" class="btn btn-info preview-btn" onclick="previewAnnouncement()">
                        <i class="fas fa-eye me-1"></i>Preview
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageTextarea = document.getElementById('announcement_message');
    const charCount = document.getElementById('char-count');
    const wordCount = document.getElementById('word-count');

    // Character and word count function
    function updateCounts() {
        if (!messageTextarea || !charCount || !wordCount) return;
        
        const text = messageTextarea.value;
        const chars = text.length;
        const words = text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
        
        charCount.textContent = chars;
        wordCount.textContent = words;
        
        // Color coding for character count
        if (chars > 500) {
            charCount.style.color = '#dc3545'; 
        } else if (chars > 300) {
            charCount.style.color = '#ffc107'; 
        } else {
            charCount.style.color = '#28a745';
        }
    }

    // Update counts on page load and when typing
    if (messageTextarea) {
        updateCounts();
        messageTextarea.addEventListener('input', updateCounts);
    }

    // Simple preview function
    window.previewAnnouncement = function() {
        const title = document.getElementById('announcement_title').value.trim() || 'Untitled Announcement';
        const message = document.getElementById('announcement_message').value.trim() || 'No message content';
        const expiry = document.getElementById('expiry_date').value;
        const isActive = document.getElementById('is_active').checked;
        
        const expiryText = expiry ? new Date(expiry).toLocaleDateString() : 'No expiry date';
        const statusText = isActive ? 'Active' : 'Inactive';
        
        alert(`PREVIEW:\n\nTitle: ${title}\n\nMessage: ${message.substring(0, 200)}${message.length > 200 ? '...' : ''}\n\nExpiry: ${expiryText}\nStatus: ${statusText}`);
    };

    // Form validation
    const form = document.getElementById('editAnnouncementForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const title = document.getElementById('announcement_title').value.trim();
            const message = document.getElementById('announcement_message').value.trim();
            const expiry = document.getElementById('expiry_date').value;

            if (title.length < 3) {
                alert('Title must be at least 3 characters long.');
                document.getElementById('announcement_title').focus();
                e.preventDefault();
                return false;
            }

            if (message.length < 10) {
                alert('Message must be at least 10 characters long.');
                document.getElementById('announcement_message').focus();
                e.preventDefault();
                return false;
            }

            if (!expiry) {
                alert('Please select an expiry date.');
                document.getElementById('expiry_date').focus();
                e.preventDefault();
                return false;
            }

            // Confirmation
            if (!confirm('Are you sure you want to update this announcement?')) {
                e.preventDefault();
                return false;
            }

            return true;
        });
    }

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('msg') === 'updated') {
        alert('Announcement updated successfully!');
        window.history.replaceState({}, document.title, window.location.pathname + '?id=' + urlParams.get('id'));
    }
});
</script>

<?php include 'footer.php'; ?>