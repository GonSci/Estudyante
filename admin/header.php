<?php

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

?>

<style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: #f8fafc;
        margin: 0;
    }

    .sidebar {
        background:hsl(217, 65.90%, 25.30%);
        width: 220px;
        min-height: 100vh;
        padding: 1.5rem 0;
    }

    .school-header {
        padding: 0 1.5rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        margin-bottom: 1rem;
    }

    .school-logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .school-logo img {
        width: 80px;
        height: 40px;
        border-radius: 6px;
    }

    .school-name {
        color: white;
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
        line-height: 1.2;
    }

    .sidebar-header {
        padding: 0 1.5rem 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        margin-bottom: 1rem;
    }

    .sidebar-header h4 {
        color: white;
        font-size: 25px;
        font-weight: 600;
        margin: 0;
        letter-spacing: -0.025em;
    }

    .nav-item {
        margin-bottom: 2px;
    }

    .nav-link {
        display: block;
        padding: 0.75rem 1.5rem;
        color: rgba(255, 255, 255, 0.8) !important;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.15s ease;
        border-left: 3px solid transparent;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        border-left-color: white;
    }

    .nav-link.active {
        background: rgba(255, 255, 255, 0.15);
        color: white !important;
        border-left-color: white;
    }

    .nav-link i {
        margin-right: 0.75rem;
        width: 16px;
        text-align: center;
    }

    .logout-section {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    .logout-link {
        color: rgba(255, 255, 255, 0.7) !important;
    }

    .logout-link:hover {
        background: rgba(251, 7, 7, 0.74) !important;
        color:rgb(255, 255, 255) !important;
        border-left-color:rgb(255, 15, 15);
    }

    .main-content {
        flex: 1;
        padding: 1.5rem;
        background: #f8fafc;
    }

    .hamburger {
        display: none;
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1001;
        background: hsl(217, 65.90%, 25.30%);
        color: white;
        border: none;
        padding: 0.75rem;
        border-radius: 6px;
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .hamburger:hover {
        background: hsl(217, 65.90%, 30%);
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    @media (max-width: 768px) {
        .hamburger {
            display: block;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: -220px;
            width: 220px;
            height: 100vh;
            z-index: 1000;
            transition: left 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar-overlay.active {
            display: block;
        }

        .main-content {
            width: 100%;
            padding-top: 4rem;
        }
        
        .d-flex {
            flex-direction: row;
        }
    }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

    // Toggle sidebar when hamburger is clicked
    hamburgerBtn.addEventListener('click', toggleSidebar);

    // Close sidebar when overlay is clicked
    overlay.addEventListener('click', closeSidebar);

    // Close sidebar when window is resized to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });

    // Close sidebar when any nav link is clicked (on mobile)
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