<?php
session_start();

include '../includes/db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
    header("Location: ../login.php");
    exit;
}

include 'navbar.php';

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id, first_name, last_name, program, year_level FROM students WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

$student_id = $student['id'];

$payments_query = $conn->prepare("
    SELECT p.*, pt.type_name 
    FROM student_payments p 
    JOIN payment_types pt ON p.payment_type_id = pt.id 
    WHERE p.student_id = ? 
    ORDER BY p.due_date DESC, p.created_at DESC
");
$payments_query->bind_param("i", $student_id);
$payments_query->execute();
$payments_result = $payments_query->get_result();

$total_due = 0;
$total_paid = 0;
$next_due_date = null;

while ($payment = $payments_result->fetch_assoc()) {
    if ($payment['status'] === 'pending') {
        $total_due += $payment['amount'];
        
        // Find the nearest due date among pending payments
        if ($payment['due_date'] && (!$next_due_date || $payment['due_date'] < $next_due_date)) {
            $next_due_date = $payment['due_date'];
        }
    } elseif ($payment['status'] === 'paid') {
        $total_paid += $payment['amount'];
    }
}

$payments_result->data_seek(0);
?>

<link rel="stylesheet" href="css/payments.css">

<div class="container mt-4">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-12">
                <div class="header-content">
                    <div class="d-flex align-items-center mb-2">
                        <div class="header-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="header-text">
                            <h2 class="mb-1">My Payments</h2>
                            <p class="text-muted mb-0">View your payment history and download receipts</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stats-card stats-due">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">₱<?= number_format($total_due, 2) ?></h5>
                            <p class="mb-0">Total Due</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stats-card stats-paid">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">₱<?= number_format($total_paid, 2) ?></h5>
                            <p class="mb-0">Total Paid</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stats-card stats-due-date">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <?php if ($next_due_date): ?>
                                <?php 
                                $due_date_obj = new DateTime($next_due_date);
                                $now = new DateTime();
                                $is_overdue = $due_date_obj < $now;
                                ?>
                                <h5 class="mb-0 <?= $is_overdue ? 'text-danger' : '' ?>">
                                    <?= $due_date_obj->format('M j, Y') ?>
                                </h5>
                                <p class="mb-0">
                                    <?= $is_overdue ? 'Overdue Payment' : 'Next Due Date' ?>
                                </p>
                            <?php else: ?>
                                <h5 class="mb-0 text-muted">No Due Dates</h5>
                                <p class="mb-0">All payments up to date</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Payment History</h5>
        </div>
        <div class="card-body">
            <?php if ($payments_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Payment Type</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Payment Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($payment = $payments_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="payment-type">
                                        <strong><?= htmlspecialchars($payment['type_name']) ?></strong>
                                        <?php if ($payment['description']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($payment['description']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="amount">₱<?= number_format($payment['amount'], 2) ?></span>
                                </td>
                                <td>
                                    <?php if ($payment['due_date']): ?>
                                        <?php 
                                        $due_date = new DateTime($payment['due_date']);
                                        $now = new DateTime();
                                        $is_overdue = $due_date < $now && $payment['status'] === 'pending';
                                        ?>
                                        <span class="<?= $is_overdue ? 'text-danger' : '' ?>">
                                            <?= $due_date->format('M j, Y') ?>
                                            <?php if ($is_overdue): ?>
                                                <i class="fas fa-exclamation-triangle ms-1"></i>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No due date</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_icon = '';
                                    switch($payment['status']) {
                                        case 'paid':
                                            $status_class = 'success';
                                            $status_icon = 'check-circle';
                                            break;
                                        case 'pending':
                                            $status_class = 'warning';
                                            $status_icon = 'clock';
                                            break;
                                        case 'overdue':
                                            $status_class = 'danger';
                                            $status_icon = 'exclamation-triangle';
                                            break;
                                        default:
                                            $status_class = 'secondary';
                                            $status_icon = 'question';
                                    }
                                    ?>
                                    <span class="badge bg-<?= $status_class ?>">
                                        <i class="fas fa-<?= $status_icon ?> me-1"></i>
                                        <?= ucfirst($payment['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($payment['payment_date']): ?>
                                        <?= date('M j, Y', strtotime($payment['payment_date'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not paid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($payment['status'] === 'paid'): ?>
                                        <button class="btn btn-sm btn-outline-secondary" 
                                                onclick="viewReceipt(<?= $payment['id'] ?>)">
                                            <i class="fas fa-receipt me-1"></i>Receipt
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="fas fa-clock me-1"></i>Awaiting Payment
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-credit-card"></i>
                    <h5>No Payment Records</h5>
                    <p>You don't have any payment records yet. Payment obligations will appear here when assigned by the administration.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function viewReceipt(paymentId) {
    window.open(`payment-receipt.php?id=${paymentId}`, '_blank');
}
</script>

<?php include 'footer.php'; ?>
