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
<div class="container mt-0">


        <!-- School Information Section -->
        <div class="card mb-4 shadow-sm">
        <div class="card-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
            <h5 class="mb-0">About Summit Crest Academy</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- School Logo and Motto -->
                <div class="col-md-4 text-center mb-3 mb-md-0">
                    <img src="../assets/login_logo.webp" alt="School Logo" style="max-width: 180px; margin-bottom: 15px;" class="img-fluid">
                    <div class="mt-3">
                        <blockquote class="blockquote">
                            <p class="mb-0 fs-5 fst-italic">"We rise by knowledge, We lead by heart"</p>
                        </blockquote>
                    </div>
                </div>
                
                <!-- Vision and Mission -->
                <div class="col-md-8">
                    <div class="mb-4">
                        <h4 class="text-primary">Our Vision</h4>
                        <p>Summit Crest Academy envisions being a premier educational institution that nurtures leaders and innovators who will make significant contributions to society through excellence in academia, character, and service.</p>
                    </div>
                    
                    <div>
                        <h4 class="text-primary">Our Mission</h4>
                        <p>Our mission is to provide a transformative educational experience that develops well-rounded individuals by:</p>
                        <ul>
                            <li>Fostering critical thinking and intellectual curiosity</li>
                            <li>Cultivating moral integrity and ethical leadership</li>
                            <li>Encouraging innovation and creative problem-solving</li>
                            <li>Promoting inclusivity and global citizenship</li>
                            <li>Building resilience and adaptability for an ever-changing world</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>








    <!-- Display announcements to students -->
    <?php if($announcements && $announcements->num_rows > 0): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">📢 Announcements</h5>
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



    <!-- Academic Performance Summary (Optional) -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
                    <h5 class="mb-0">My Academic Journey</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-graduation-cap fa-3x text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Current Semester</h6>
                            <p class="text-muted mb-0">Fall 2025</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-book fa-3x text-success"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Program</h6>
                            <p class="text-muted mb-0">Computer Science</p>
                        </div>
                    </div>
                    <a href="view-courses.php" class="btn btn-outline-primary">View My Courses</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
                    <h5 class="mb-0">Upcoming Events</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary rounded-pill me-2">Jun 30</span>
                                End of Spring Semester
                            </div>
                            <i class="fas fa-calendar-check text-primary"></i>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-success rounded-pill me-2">Jul 15</span>
                                Summer Workshop Begins
                            </div>
                            <i class="fas fa-chalkboard-teacher text-success"></i>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-info rounded-pill me-2">Aug 20</span>
                                Fall Registration Deadline
                            </div>
                            <i class="fas fa-clipboard-list text-info"></i>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rest of your content -->
</div>

<?php include 'footer.php'; ?>
