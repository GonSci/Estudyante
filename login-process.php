<?php

session_start();
require_once 'includes/db.php'; // Update the path as needed

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $user = $result->fetch_assoc();

        if($password === $user['password']) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_id'] = $user['id'];


            if($user['role'] === 'admin'){
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] === 'student'){
                header("Location: student/dashboard.php");
            } else {
                echo "Unknown role.";
            }
            exit;
        } else {
            echo "Invalif password";
        }
    } else {
        echo "User not found";
    }
}



?>