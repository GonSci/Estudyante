<?php

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/header.css">
</head>
<body>
<div class="d-flex">
    <!-- Hamburger Menu Button -->
    <button class="hamburger" id="hamburgerBtn">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="school-header">
            <div class="school-logo">
                <img src="../assets/login_logo_white.png" alt="School Logo">
                <h3 class="school-name">Summit Crest Academy</h3>
            </div>
        </div>
        <div class="sidebar-header">
            <h4>Admin Panel</h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i>Dashboard</a></li>
            <li class="nav-item"><a href="calendar.php" class="nav-link"><i class="fas fa-calendar-alt"></i>Task Calendar</a></li>
            <li class="nav-item"><a href="manage-announcements.php" class="nav-link"><i class="fas fa-bullhorn"></i>Manage Announcements</a></li>
            <li class="nav-item"><a href="manage-students.php" class="nav-link"><i class="fas fa-user-graduate"></i>Manage Students</a></li>
            <li class="nav-item"><a href="manage-courses.php" class="nav-link"><i class="fas fa-book"></i>Manage Courses</a></li>
            <li class="nav-item"><a href="view-curriculum.php" class="nav-link"><i class="fas fa-clipboard-list"></i>View Curriculum</a></li>

        </ul>
        <div class="logout-section">
            <ul class="nav flex-column">
                <li class="nav-item"><a href="../logout.php" class="nav-link logout-link"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">


<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        
        // Change hamburger icon
        const icon = hamburgerBtn.querySelector('i');
        if (sidebar.classList.contains('active')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
        } else {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        }
    }

    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        const icon = hamburgerBtn.querySelector('i');
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }

    hamburgerBtn.addEventListener('click', toggleSidebar);

    overlay.addEventListener('click', closeSidebar);

    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });

    const navLinks = sidebar.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });
});
</script>

</body>
</html>