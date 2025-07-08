<?php
session_start();

include '../includes/db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
    header("Location: ../login.php");
    exit;
}

include 'navbar.php';
?>

<link rel="stylesheet" href="css/dashboard.css">

<?php
$announcements = $conn->query("
    SELECT * FROM announcements
    WHERE is_active = 1 
    AND (expiry_date >= CURDATE() OR expiry_date IS NULL)
    ORDER BY created_at DESC
");
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card h-100 shadow-sm">
                <div class="card-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
                    <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>My Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div style="width: 100px; height: 100px; background-color: hsl(217, 65.90%, 25.30%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; color: white; font-size: 40px;">
                            <?php 
                            $username = $_SESSION['username']; 

                            $stmt = $conn->prepare("SELECT first_name, last_name FROM students WHERE username = ?");
                            $stmt->bind_param("s", $username);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result->num_rows > 0) {
                                $student_info = $result->fetch_assoc();
                                $initials = strtoupper(substr($student_info['first_name'], 0, 1) . substr($student_info['last_name'], 0, 1));
                            } else if (isset($_SESSION['username'])) {
                                $initials = strtoupper(substr($_SESSION['username'], 0, 2));
                            } else {
                                $initials = 'ST';
                            }

                            echo $initials;
                            $stmt->close();
                            ?>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-graduation-cap fa-2x text-primary"></i>
                        </div>
                        <div>
                            <?php
                            $username = $_SESSION['username'];
                            $stmt = $conn->prepare("SELECT academic_term, year_level FROM students WHERE username = ?");
                            $stmt->bind_param("s", $username);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if($result->num_rows > 0) {
                                $student_info = $result->fetch_assoc();
                                $academic_term = htmlspecialchars($student_info['academic_term']);
                                $year_level = htmlspecialchars($student_info['year_level']);
                            } else {
                                $academic_term = 'N/A';
                                $year_level = 'N/A';
                            }
                            $stmt->close();
                            ?>
                            <h6 class="mb-0">Status</h6>
                            <p class="text-muted mb-0"><?= $year_level . ' year (' . $academic_term . ')'?></p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <i class="fas fa-book fa-2x text-success"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Program</h6>
                            <p class="text-muted mb-0">Computer Science</p>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <a href="profile.php" class="btn btn-outline-primary">
                            <i class="fas fa-id-card me-1"></i> View Full Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <?php if($announcements && $announcements->num_rows > 0): ?>
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"> 📣 Announcements</h5>
                            <span class="badge bg-danger"><?= $announcements->num_rows ?> New</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php while($row = $announcements->fetch_assoc()): ?>
                                <div class="list-group-item">
                                    <h5 class="mb-1"><?= htmlspecialchars($row['title']) ?></h5>
                                    <p class="mb-1"><?= nl2br(htmlspecialchars($row['message'])) ?></p>
                                    <small class="text-muted">
                                        <i class="far fa-calendar-alt me-1"></i> Posted on <?= date('F j, Y', strtotime($row['created_at'])) ?>
                                    </small>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Announcements</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0">There are no active announcements at this time.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-3">
                        <i class="fas fa-credit-card fa-3x text-primary"></i>
                    </div>
                    <h5>Payments & Fees</h5>
                    <p class="text-muted">View and manage your tuition fees and payment records</p>
                    <a href="payments.php" class="btn btn-primary">View Payments</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4 mb-md-0">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-3">
                        <i class="fas fa-book-open fa-3x text-success"></i>
                    </div>
                    <h5>My Courses</h5>
                    <p class="text-muted">View your enrolled courses and academic progress</p>
                    <a href="view-courses.php" class="btn btn-success">View Courses</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <div class="icon-wrapper mb-3">
                        <i class="fas fa-calendar-alt fa-3x text-info"></i>
                    </div>
                    <h5>Academic Calendar</h5>
                    <p class="text-muted">View important dates and upcoming events</p>
                    <a href="#" class="btn btn-info text-white">View Calendar</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4 shadow-sm">
        <div class="card-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">About Summit Crest Academy</h5>
                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#schoolInfo" aria-expanded="true">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <div class="collapse show" id="schoolInfo">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <img src="../assets/login_logo.webp" alt="School Logo" style="max-width: 150px; margin-bottom: 15px;" class="img-fluid">
                        <div class="mt-3">
                            <blockquote class="blockquote">
                                <p class="mb-0 fs-6 fst-italic">"We rise by knowledge, We lead by heart"</p>
                            </blockquote>
                        </div>
                    </div>
                    
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h5 class="text-primary">Our Vision</h5>
                                <p class="small">Summit Crest Academy envisions being a premier educational institution that nurtures leaders and innovators who will make significant contributions to society through excellence in academia, character, and service.</p>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="text-primary">Our Mission</h5>
                                <p class="small mb-1">Our mission is to provide a transformative educational experience that develops well-rounded individuals by:</p>
                                <ul class="small">
                                    <li>Fostering critical thinking and intellectual curiosity</li>
                                    <li>Cultivating moral integrity and ethical leadership</li>
                                    <li>Encouraging innovation and creative problem-solving</li>
                                    <li>Promoting inclusivity and global citizenship</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
            <h5 class="mb-0"><i class="fas fa-calendar-week me-2"></i>Upcoming Events</h5>
        </div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <div>
                        <span class="badge bg-primary rounded-pill me-2">July 30</span>
                        <span class="fw-medium">Code Quest Event</span>
                    </div>
                    <i class="fas fa-calendar-check text-primary"></i>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <div>
                        <span class="badge bg-success rounded-pill me-2">July 15</span>
                        <span class="fw-medium">Summer Workshop Begins</span>
                    </div>
                    <i class="fas fa-chalkboard-teacher text-success"></i>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <div>
                        <span class="badge bg-info rounded-pill me-2">August 1</span>
                        <span class="fw-medium">Enrollment Period</span>
                    </div>
                    <i class="fas fa-clipboard-list text-info"></i>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
