<?php


session_start();

if($_SESSION['role'] !== 'student'){
    header("Location: ../login.php");
    exit;
}


echo "<h2>Welcome, Student!</h2>";
echo "<a href='../logout.php'>Logout</a>";

?>