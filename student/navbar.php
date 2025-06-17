<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            min-height: 100vh;
            background: #f8f9fa;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px; /* set sidebar width */
            height: 100vh;
            background: #343a40;
            color: #fff;
            padding-top: 30px;
            z-index: 1000;
        }

        .sidebar a {
            color: #fff;
            display: block;
            padding: 12px 20px;
            text-decoration: none;
        }

        .sidebar a.active, .sidebar a:hover {
            background: #495057;
        }

        .main-content {
            margin-left: 220px; /* same as sidebar width */
            padding: 30px;
            background: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 sidebar">
            <h4 class="px-3">Student Menu</h4>
            <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
            <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">Profile</a>
            <a href="courses.php" class="<?= basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : '' ?>">My Courses</a>
            <a href="register-course.php" class="<?= basename($_SERVER['PHP_SELF']) == 'register-course.php' ? 'active' : '' ?>">Register Course</a>
            <a href="../logout.php">Logout</a>
        </nav>