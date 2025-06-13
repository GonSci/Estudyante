<?php

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

?>
<style>
    .nav-link:hover {
        background-color: black;

    }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="bg-dark text-white p-3" style="width: 220px; min-height: 100vh;">
        <h4>Admin Panel</h4>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="dashboard.php" class="nav-link text-white">Dashboard</a></li>
            <li class="nav-item"><a href="manage-students.php" class="nav-link text-white">Manage Students</a></li>
            <li class="nav-item"><a href="manage-courses.php" class="nav-link text-white">Manage Courses</a></li>
            <li class="nav-item"><a href="view-registrations.php" class="nav-link text-white">View Registrations</a></li>
            <li class="nav-item"><a href="../logout.php" class="nav-link text-white">Logout</a></li>
        </ul>
    </div>
    
    <!-- Main Content -->
         <div class="p-4 w-100">


</body>
</html>