<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($payment_id <= 0) {
    header("Location: manage-payments.php?error=invalid_id");
    exit;
}

try {
    if ($action === 'mark_paid') {
        $update_stmt = $conn->prepare("
            UPDATE student_payments 
            SET status = 'paid', payment_date = NOW() 
            WHERE id = ? AND status = 'pending'
        ");
        $update_stmt->bind_param("i", $payment_id);
        
        if ($update_stmt->execute() && $update_stmt->affected_rows > 0) {
            header("Location: manage-payments.php?success=payment_marked_paid");
        } else {
            header("Location: manage-payments.php?error=payment_not_found");
        }
        $update_stmt->close();
        
    } elseif ($action === 'delete') {
        $delete_stmt = $conn->prepare("DELETE FROM student_payments WHERE id = ?");
        $delete_stmt->bind_param("i", $payment_id);
        
        if ($delete_stmt->execute() && $delete_stmt->affected_rows > 0) {
            header("Location: manage-payments.php?success=payment_deleted");
        } else {
            header("Location: manage-payments.php?error=payment_not_found");
        }
        $delete_stmt->close();
        
    } else {
        header("Location: manage-payments.php?error=invalid_action");
    }
    
} catch (Exception $e) {
    header("Location: manage-payments.php?error=database_error");
}

$conn->close();
?>
