<?php

include '../includes/db.php';
include 'navbar.php';


$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT * FROM students WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

$student = $result->fetch_assoc();
$stmt->close();

echo $student['program'];





?>