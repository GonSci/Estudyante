<?php session_start(); ?> <!-- Start the session -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Page</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      margin: 0;
      background: linear-gradient(to bottom right, #ebf4ff, #ffffff, #dbeafe);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }

    .wrapper {
      width: 100%;
      max-width: 420px;
    }

    .logo-section {
      text-align: center;
      margin-bottom: 30px;
    }

    .logo-circle {
      background-color: #2563eb;
      width: 64px;
      height: 64px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }

    .logo-circle img {
      width: 45px;
      height: 30px;
      filter: brightness(0) invert(1); /* Make icon white */
    }

    .logo-section h1 {
      font-size: 28px;
      font-weight: 600;
      color: #111827;
      margin: 0;
    }

    .logo-section p {
      font-size: 14px;
      color: #6b7280;
    }

    .card {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(10px);
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .card h2 {
      text-align: center;
      font-size: 22px;
      font-weight: 600;
      margin-bottom: 5px;
      color: #111827;
    }

    .card p {
      text-align: center;
      font-size: 14px;
      color: #6b7280;
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #374151;
      margin-bottom: 5px;
    }

    .form-group input[type="text"],
    .form-group input[type="password"] {
      width: 100%;
      height: 44px;
      padding: 10px 12px 10px 38px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      outline: none;
      background-color: #fff;
      transition: border-color 0.3s;
    }

    .form-group input:focus {
      border-color: #3b82f6;
    }

    .form-icon {
      position: relative;
    }

    .form-icon::before {
      content: "";
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      width: 16px;
      height: 16px;
      background-size: cover;
      opacity: 0.6;
    }

    .form-icon.username::before {
      background-image: url('./assets/user-icon.svg');
    }

    .form-icon.password::before {
      background-image: url('./assets/lock-icon.svg');
    }

    .forgot {
      text-align: right;
      margin-bottom: 20px;
    }

    .forgot a {
      font-size: 13px;
      color: #2563eb;
      text-decoration: none;
    }

    .forgot a:hover {
      text-decoration: underline;
    }

    .login-btn {
      width: 100%;
      background-color: #2563eb;
      color: white;
      border: none;
      border-radius: 30px;
      padding: 12px;
      font-weight: 500;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .login-btn:hover {
      background-color: #1d4ed8;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
    }

    .error-msg {
      color: red;
      text-align: center;
      font-size: 13px;
      margin-top: 10px;
    }

    .support-text {
      text-align: center;
      font-size: 13px;
      margin-top: 25px;
      color: #6b7280;
    }

    .support-text a {
      color: #2563eb;
      text-decoration: none;
    }

    .support-text a:hover {
      text-decoration: underline;
    }

    .footer {
      text-align: center;
      font-size: 12px;
      color: #9ca3af;
      margin-top: 30px;
    }

    .input-icon {
      position: relative;
    }

    .icon-img {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      width: 16px;
      height: 16px;
      opacity: 0.6;
    }

  </style>
</head>

<body>
  <div class="wrapper">
    <!-- Logo + Title -->
    <div class="logo-section">
      <div class="logo-circle">
        <img src="./assets/login_logo_white.png" alt="Cap Icon">
      </div>
      <h1>Summit Crest Academy</h1>
      <p>Welcome back to your learning journey</p>
    </div>

    <!-- Login Card -->
    <div class="card">
      <h2>Log In</h2>
      <p>Enter your credentials to access your account</p>

      <form action="login-process.php" method="POST">
        <!-- Username Field -->
        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-icon">
            <img src="./assets/person.svg" alt="" class="icon-img">
            <input type="text" name="username" id="username" required>
          </div>
        </div>

        <!-- Password Field -->
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-icon">
            <img src="./assets/lock.svg" alt="" class="icon-img">
            <input type="password" name="password" id="password" required>
          </div>
        </div>

        <div class="forgot">
          <a href="#">Forgot password?</a>
        </div>

        <button type="submit" class="login-btn">Log In</button>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid'): ?>
          <div class="error-msg">Invalid username or password.</div>
        <?php endif; ?>
      </form>

      <div class="support-text">
        Need help? <a href="#">Contact Support</a>
      </div>
    </div>

    <!-- Footer -->
    <div class="footer">
      © 2024 Summit Crest Academy. All rights reserved.
    </div>
  </div>
</body>
</html>
