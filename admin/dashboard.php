
<?php


include 'header.php';
session_start();

if($_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}
echo "<h2>Welcome to the Admin Dashboard</h2>";
echo "<p>Use the sidebar to manage students, courses, and view registrations.</p>";
echo "<a href='../logout.php'>Logout</a>";



include 'footer.php';

?>


