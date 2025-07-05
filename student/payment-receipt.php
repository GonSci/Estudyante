<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

include '../includes/db.php';

$payment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($payment_id <= 0) {
    header("Location: payments.php");
    exit;
}

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM students WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

$payment_query = $conn->prepare("
    SELECT p.*, pt.type_name, s.first_name, s.last_name, s.program, s.year_level
    FROM student_payments p
    JOIN payment_types pt ON p.payment_type_id = pt.id
    JOIN students s ON p.student_id = s.id
    WHERE p.id = ? AND p.student_id = ? AND p.status = 'paid'
");
$payment_query->bind_param("ii", $payment_id, $student['id']);
$payment_query->execute();
$payment_result = $payment_query->get_result();

if ($payment_result->num_rows === 0) {
    header("Location: payments.php");
    exit;
}

$payment = $payment_result->fetch_assoc();
$payment_query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .receipt { background: white; max-width: 600px; margin: 2rem auto; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .receipt-header { background: hsl(217, 65.90%, 25.30%); color: white; padding: 2rem; text-align: center; }
        .receipt-body { padding: 2rem; }
        @media print {
            body { background: white; }
            .receipt { box-shadow: none; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="receipt-header">
            <h2><i class="fas fa-receipt me-2"></i>Payment Receipt</h2>
            <p class="mb-0">Summit Crest Academy</p>
        </div>
        
        <div class="receipt-body">
            <div class="row mb-4">
                <div class="col-6">
                    <strong>Receipt #:</strong> PAY-<?= str_pad($payment['id'], 6, '0', STR_PAD_LEFT) ?><br>
                    <strong>Date:</strong> <?= date('F j, Y', strtotime($payment['payment_date'])) ?>
                </div>
                <div class="col-6 text-end">
                    <strong>Student:</strong> <?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) ?><br>
                    <strong>Program:</strong> <?= htmlspecialchars($payment['program']) ?>
                </div>
            </div>
            
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($payment['type_name']) ?></strong>
                            <?php if ($payment['description']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($payment['description']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">₱<?= number_format($payment['amount'], 2) ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="table-success">
                        <th>Total Paid</th>
                        <th class="text-end">₱<?= number_format($payment['amount'], 2) ?></th>
                    </tr>
                </tfoot>
            </table>
            
            <div class="text-center mt-4 no-print">
                <button class="btn btn-primary me-2" onclick="window.print()">
                    <i class="fas fa-print me-1"></i>Print Receipt
                </button>
                <a href="payments.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Payments
                </a>
            </div>
        </div>
    </div>
</body>
</html>
