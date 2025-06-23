<?php
session_start();

if($_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

include 'header.php';
?>

<div class="container mt-4">
    <div class="jumbotron">
        <h2>Welcome to the Admin Dashboard</h2>
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


