<?php include 'header.php'; ?>
<?php include '../includes/db.php'; ?>

<link rel="stylesheet" href="css/admin-common.css">

<?php
$student_filter = isset($_GET['student']) ? trim($_GET['student']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$students_query = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) as full_name, username FROM students ORDER BY first_name, last_name");

if (isset($_POST['create_payment'])) {
    $student_id = $_POST['student_id'];
    $payment_type_id = $_POST['payment_type_id'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    
    $insert_stmt = $conn->prepare("
        INSERT INTO student_payments (student_id, payment_type_id, amount, description, due_date, status, created_at) 
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $insert_stmt->bind_param("iidss", $student_id, $payment_type_id, $amount, $description, $due_date);
    
    if ($insert_stmt->execute()) {
        $success_message = "Payment created successfully";
    } else {
        $error_message = "Failed to create payment";
    }
    $insert_stmt->close();
}

$records_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

$where_conditions = [];
$params = [];
$param_types = "";

if (!empty($student_filter)) {
    $where_conditions[] = "s.id = ?";
    $params[] = $student_filter;
    $param_types .= "i";
}

if (!empty($status_filter)) {
    $where_conditions[] = "p.status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$count_query = "SELECT COUNT(*) as total FROM student_payments p JOIN students s ON p.student_id = s.id $where_clause";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param($param_types, ...$params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_records = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $count_result = $conn->query($count_query);
    $total_records = $count_result->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $records_per_page);

$query = "SELECT p.*, pt.type_name, CONCAT(s.first_name, ' ', s.last_name) as student_name, s.program 
          FROM student_payments p 
          JOIN students s ON p.student_id = s.id 
          JOIN payment_types pt ON p.payment_type_id = pt.id 
          $where_clause 
          ORDER BY p.created_at DESC 
          LIMIT $offset, $records_per_page";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}
?>

<div class="container-fluid">
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-credit-card me-2"></i>Student Payments Management</h2>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-success-simple" data-bs-toggle="modal" data-bs-target="#createPaymentModal">
                        <i class="fas fa-plus me-1"></i>Create Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i><?= $error_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="search-section">
            <form method="GET" action="" class="row align-items-end">
                <div class="col-md-5">
                    <label for="student_filter" class="form-label">Filter by Student:</label>
                    <select name="student" id="student_filter" class="form-select">
                        <option value="">All Students</option>
                        <?php
                        $students_query = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) as full_name FROM students ORDER BY first_name, last_name");
                        while ($student = $students_query->fetch_assoc()):
                        ?>
                            <option value="<?= $student['id'] ?>" <?= ($student_filter == $student['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($student['full_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="status_filter" class="form-label">Filter by Status:</label>
                    <select name="status" id="status_filter" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" <?= ($status_filter == 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="paid" <?= ($status_filter == 'paid') ? 'selected' : '' ?>>Paid</option>
                        <option value="overdue" <?= ($status_filter == 'overdue') ? 'selected' : '' ?>>Overdue</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-simple">
                            <i class="fas fa-filter me-1"></i>Filter
                        </button>
                        <a href="manage-payments.php" class="btn btn-secondary-simple">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="courses-table-container">
                <div class="table-header">
                    <h5><i class="fas fa-table me-2"></i>Payment Records (<?= $total_records ?>)</h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table modern-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Payment Type</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Payment Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($payment = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($payment['student_name']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($payment['program']) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($payment['type_name']) ?></strong>
                                    <?php if ($payment['description']): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($payment['description']) ?></small>
                                    <?php endif; ?>
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
                                    <div class="action-buttons">
                                        <?php if ($payment['status'] === 'pending'): ?>
                                            <button class="btn-action btn-success" 
                                                    onclick="markAsPaid(<?= $payment['id'] ?>, '<?= htmlspecialchars($payment['student_name']) ?>', '<?= htmlspecialchars($payment['type_name']) ?>')">
                                                <i class="fas fa-check me-1"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn-action btn-delete" 
                                                onclick="deletePayment(<?= $payment['id'] ?>, '<?= htmlspecialchars($payment['student_name']) ?>', '<?= htmlspecialchars($payment['type_name']) ?>')">
                                            <i class="fas fa-trash me-1"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-credit-card"></i>
                <h5>No Payment Records Found</h5>
                <p>No payment records match your current filters. Try adjusting your search criteria or create a new payment.</p>
                <button class="btn btn-success-simple" data-bs-toggle="modal" data-bs-target="#createPaymentModal">
                    <i class="fas fa-plus me-1"></i>Create First Payment
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Payment Modal -->
<div class="modal fade" id="createPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Student</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">Select student...</option>
                                <?php
                                $students_modal_query = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) as full_name, username FROM students ORDER BY first_name, last_name");
                                while ($student = $students_modal_query->fetch_assoc()):
                                ?>
                                    <option value="<?= $student['id'] ?>">
                                        <?= htmlspecialchars($student['full_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Type</label>
                            <select name="payment_type_id" class="form-select" id="paymentTypeSelect" required>
                                <option value="">Select payment type...</option>
                                <?php
                                $types_query = $conn->query("SELECT * FROM payment_types WHERE is_active = 1 ORDER BY type_name");
                                while ($type = $types_query->fetch_assoc()):
                                ?>
                                    <option value="<?= $type['id'] ?>">
                                        <?= htmlspecialchars($type['type_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" name="amount" class="form-control" id="amountInput" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Additional notes about this payment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_payment" class="btn btn-success">Create Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function markAsPaid(paymentId, studentName, typeName) {
    Swal.fire({
        title: 'Mark as Paid?',
        html: `
            <p>Mark this payment as paid?</p>
            <div class="alert alert-info">
                <strong>Student:</strong> ${studentName}<br>
                <strong>Payment:</strong> ${typeName}
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Mark as Paid',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `process-admin-payment.php?action=mark_paid&id=${paymentId}`;
        }
    });
}

function deletePayment(paymentId, studentName, typeName) {
    Swal.fire({
        title: 'Delete Payment?',
        html: `
            <p>This will permanently delete this payment record:</p>
            <div class="alert alert-warning">
                <strong>Student:</strong> ${studentName}<br>
                <strong>Payment:</strong> ${typeName}
            </div>
            <p class="text-danger">This action cannot be undone!</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74a3b',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `process-admin-payment.php?action=delete&id=${paymentId}`;
        }
    });
}
</script>

<?php include 'footer.php'; ?>
