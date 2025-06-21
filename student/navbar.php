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
            background: #343a40;
            color: #fff;
            min-height: 100vh;
            padding-top: 30px;
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
        <nav class="col-md-3 col-lg-2 sidebar">
            <h4 class="px-3">Student Menu</h4>
            <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
            <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">Profile</a>
            <a href="view-courses.php" class="<?= basename($_SERVER['PHP_SELF']) == 'view-courses.php' ? 'active' : '' ?>">My Courses</a>
            <a href="register-course.php" class="<?= basename($_SERVER['PHP_SELF']) == 'register-course.php' ? 'active' : '' ?>">Register Course</a>
            <a href="../logout.php">Logout</a>
        </nav>
        <!-- Main content starts here - don't close this div -->
        <main class="col-md-9 col-lg-10 main-content">