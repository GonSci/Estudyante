<?php session_start(); ?>  <!--session_start() starts or resumes a session-->

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form action="login-process.php" method="POST">
        <label>Username or Email</label><br>
        <input type="text" name="username" required></input><br><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <input type="submit" value="login">
    </form>

    <?php
        if (isset($_GET['error']) && $_GET['error'] === 'invalid') : ?> 
            <div style="color: red; margin-bottom:10px;">Invalid username or password.</div>
    <?php endif; ?>




    
</body>
</html>