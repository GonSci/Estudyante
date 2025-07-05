<?php
session_start();

// Set JSON content type and security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Check if user is logged in as student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['currentPassword']) || !isset($input['newPassword']) || !isset($input['confirmPassword'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$currentPassword = $input['currentPassword'];
$newPassword = $input['newPassword'];
$confirmPassword = $input['confirmPassword'];
$username = $_SESSION['username'];

// Validate password requirements
if (strlen($newPassword) < 6) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
    exit;
}

if ($currentPassword === $newPassword) {
    echo json_encode(['success' => false, 'message' => 'New password must be different from current password']);
    exit;
}

// Include database connection
include '../includes/db.php';

try {
    // Get current password hash from database
    $stmt = $conn->prepare("SELECT password FROM students WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }
    
    $student = $result->fetch_assoc();
    $stmt->close();
    
    // Verify current password
    if (!password_verify($currentPassword, $student['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
    
    // Hash new password
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password in students table
    $updateStmt = $conn->prepare("UPDATE students SET password = ? WHERE username = ?");
    $updateStmt->bind_param("ss", $newPasswordHash, $username);
    
    if ($updateStmt->execute()) {
        if ($updateStmt->affected_rows > 0) {
            // Also update the users table for consistency
            $updateUserStmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
            $updateUserStmt->bind_param("ss", $newPasswordHash, $username);
            $updateUserStmt->execute();
            $updateUserStmt->close();
            
            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made to password']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password']);
    }
    
    $updateStmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred while changing password']);
} finally {
    $conn->close();
}
?>
