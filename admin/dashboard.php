<?php


session_start();

if($_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}


echo "<h2>Welcome, Admin!</h2>";
echo "<a href='../logout.php'>Logout</a>";







?>