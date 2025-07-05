<?php
// Use output buffering to prevent "headers already sent" errors
ob_start();

// Check if session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debug session data (as HTML comment so it doesn't affect headers)
echo "<!-- Debug: Session data = " . print_r($_SESSION, true) . " -->";

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../login.php");
    exit;
}

include 'header.php';
include '../includes/db.php';

// Dashboard Statistics Queries
$stats = [];

// Total Students
$student_count = $conn->query("SELECT COUNT(*) as count FROM students");
$stats['total_students'] = $student_count ? $student_count->fetch_assoc()['count'] : 0;

// Active Announcements
$active_announcements = $conn->query("SELECT COUNT(*) as count FROM announcements WHERE is_active = 1 AND (expiry_date >= CURDATE() OR expiry_date IS NULL)");
$stats['active_announcements'] = $active_announcements ? $active_announcements->fetch_assoc()['count'] : 0;

// Pending Tasks (if tasks table exists)
$tasks_table_check = $conn->query("SHOW TABLES LIKE 'tasks'");
if ($tasks_table_check && $tasks_table_check->num_rows > 0) {
    $pending_tasks = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'pending'");
    $stats['pending_tasks'] = $pending_tasks ? $pending_tasks->fetch_assoc()['count'] : 0;
    
    // Completed Tasks
    $completed_tasks = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE status = 'completed'");
    $stats['completed_tasks'] = $completed_tasks ? $completed_tasks->fetch_assoc()['count'] : 0;
} else {
    $stats['pending_tasks'] = 0;
    $stats['completed_tasks'] = 0;
}

// Students by Year Level
$year_level_data = $conn->query("SELECT year_level, COUNT(*) as count FROM students GROUP BY year_level ORDER BY year_level");
$year_levels = [];
while($year_level_data && $row = $year_level_data->fetch_assoc()) {
    $year_levels[] = $row;
}

// Students by Academic Term
$term_data = $conn->query("SELECT academic_term, COUNT(*) as count FROM students GROUP BY academic_term");
$academic_terms = [];
while($term_data && $row = $term_data->fetch_assoc()) {
    $academic_terms[] = $row;
}

// Recent Student Registrations (last 7 days)
$recent_registrations = $conn->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date");
$registration_data = [];
while($recent_registrations && $row = $recent_registrations->fetch_assoc()) {
    $registration_data[] = $row;
}

// Monthly Registration Trends (last 6 months)
$monthly_registrations = $conn->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count FROM students WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month");
$monthly_data = [];
while($monthly_registrations && $row = $monthly_registrations->fetch_assoc()) {
    $monthly_data[] = $row;
}
?>

<!-- Add Chart.js CDN in the head section -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom CSS for Modern Dashboard -->
<style>
    /* Modern Dashboard Enhancements */
    .dashboard-header {
        background: linear-gradient(135deg, hsl(217, 65.90%, 25.30%) 0%, hsl(217, 65.90%, 35.30%) 100%);
        padding: 2rem 0;
        margin: -1.5rem -1.5rem 2rem -1.5rem;
        color: white;
        border-radius: 0 0 20px 20px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    
    .stat-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
        background: white;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--accent-color);
    }
    
    .stat-card.primary::before { --accent-color: #4e73df; }
    .stat-card.success::before { --accent-color: #1cc88a; }
    .stat-card.warning::before { --accent-color: #f6c23e; }
    .stat-card.info::before { --accent-color: #36b9cc; }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-bottom: 1rem;
    }
    
    .stat-icon.primary { background: linear-gradient(135deg, #4e73df, #224abe); }
    .stat-icon.success { background: linear-gradient(135deg, #1cc88a, #17a673); }
    .stat-icon.warning { background: linear-gradient(135deg, #f6c23e, #f4b619); }
    .stat-icon.info { background: linear-gradient(135deg, #36b9cc, #2c9faf); }
    
    .modern-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .modern-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .modern-card-header {
        background: linear-gradient(135deg, hsl(217, 65.90%, 25.30%) 0%, hsl(217, 65.90%, 30.30%) 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        border-bottom: none;
        position: relative;
    }
    
    .modern-card-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4e73df, #1cc88a, #f6c23e, #36b9cc);
    }
    
    .quick-action-btn {
        border: none;
        border-radius: 12px;
        padding: 1rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        display: block;
        color: white;
        font-weight: 500;
    }
    
    .quick-action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .quick-action-btn:hover::before {
        left: 100%;
    }
    
    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        color: white;
        text-decoration: none;
    }
    
    .quick-action-btn.calendar { background: linear-gradient(135deg, #4e73df, #224abe); }
    .quick-action-btn.announcements { background: linear-gradient(135deg, #6c757d, #495057); }
    .quick-action-btn.students { background: linear-gradient(135deg, #1cc88a, #17a673); }
    .quick-action-btn.courses { background: linear-gradient(135deg, #f6c23e, #f4b619); }
    .quick-action-btn.curriculum { background: linear-gradient(135deg, #36b9cc, #2c9faf); }
    
    .system-activity {
        text-align: center;
        padding: 2rem;
    }
    
    .system-logo {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin: 0 auto 1rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .system-logo:hover {
        transform: scale(1.05);
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.875rem;
        font-weight: 500;
        background: linear-gradient(135deg, #1cc88a, #17a673);
        color: white;
        box-shadow: 0 2px 10px rgba(28, 200, 138, 0.3);
    }
    
    .status-badge::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: white;
        margin-right: 0.5rem;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .chart-container {
        position: relative;
        padding: 1.5rem;
        height: 300px; /* Fixed height for consistency */
        min-height: 250px; /* Minimum height for mobile */
    }
    
    /* Chart responsiveness */
    @media (max-width: 768px) {
        .chart-container {
            padding: 1rem;
            height: 250px;
        }
    }
    
    @media (max-width: 576px) {
        .chart-container {
            padding: 0.75rem;
            height: 200px;
        }
    }
    
    .welcome-text {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }
    
    .dashboard-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .dashboard-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .dashboard-header {
            margin: -1.5rem -1.5rem 1rem -1.5rem;
            padding: 1.5rem 0;
        }
        
        .dashboard-title {
            font-size: 2rem;
        }
    }
</style>

<!-- Enhanced Dashboard Layout -->
<div class="container-fluid mt-4">
    <!-- Modern Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="text-center">
                <p class="welcome-text mb-2">Welcome back, Admin!</p>
                <h1 class="dashboard-title">Dashboard Overview</h1>
                <p class="dashboard-subtitle">Monitor your Student Registration System</p>
            </div>
        </div>
    </div>
    <!-- Enhanced Statistics Cards Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card primary shadow h-100">
                <div class="card-body text-center">
                    <div class="stat-icon primary mx-auto">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Total Students</div>
                    <div class="h3 mb-0 font-weight-bold text-gray-800"><?= $stats['total_students'] ?></div>
                    <small class="text-muted">Registered in system</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card success shadow h-100">
                <div class="card-body text-center">
                    <div class="stat-icon success mx-auto">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Active Announcements</div>
                    <div class="h3 mb-0 font-weight-bold text-gray-800"><?= $stats['active_announcements'] ?></div>
                    <small class="text-muted">Currently visible</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card warning shadow h-100">
                <div class="card-body text-center">
                    <div class="stat-icon warning mx-auto">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Pending Tasks</div>
                    <div class="h3 mb-0 font-weight-bold text-gray-800"><?= $stats['pending_tasks'] ?></div>
                    <small class="text-muted">Awaiting completion</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card info shadow h-100">
                <div class="card-body text-center">
                    <div class="stat-icon info mx-auto">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Completed Tasks</div>
                    <div class="h3 mb-0 font-weight-bold text-gray-800"><?= $stats['completed_tasks'] ?></div>
                    <small class="text-muted">Successfully finished</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Charts Row -->
    <div class="row mb-4">
        <!-- Student Distribution by Year Level -->
        <div class="col-xl-6 col-lg-6">
            <div class="card modern-card shadow mb-4">
                <div class="modern-card-header">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-pie me-2"></i>Students by Year Level
                    </h6>
                </div>
                <div class="card-body chart-container">
                    <canvas id="yearLevelChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Academic Terms Distribution -->
        <div class="col-xl-6 col-lg-6">
            <div class="card modern-card shadow mb-4">
                <div class="modern-card-header">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-calendar-alt me-2"></i>Students by Academic Term
                    </h6>
                </div>
                <div class="card-body chart-container">
                    <canvas id="academicTermChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Registration Trends -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card modern-card shadow mb-4">
                <div class="modern-card-header">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-line me-2"></i>Monthly Registration Trends
                    </h6>
                </div>
                <div class="card-body chart-container">
                    <canvas id="monthlyRegistrationsChart" width="400" height="160"></canvas>
                </div>
            </div>
        </div>

        <!-- Enhanced System Activity -->
        <div class="col-xl-4 col-lg-5">
            <div class="card modern-card shadow mb-4">
                <div class="modern-card-header">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-desktop me-2"></i>System Activity
                    </h6>
                </div>
                <div class="card-body system-activity">
                    <img src="../assets/login_logo.webp" alt="System Logo" class="system-logo img-fluid rounded-circle mb-3">
                    <h5 class="font-weight-bold text-dark">Student Registration System</h5>
                    <p class="text-muted mb-3">Admin Dashboard</p>
                    <div class="mt-3">
                        <span class="status-badge mb-3">System Online</span><br>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Last updated: <?= date('M d, Y h:i A') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Task Progress and Quick Actions -->
    <?php if($stats['pending_tasks'] > 0 || $stats['completed_tasks'] > 0): ?>
    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6">
            <div class="card modern-card shadow mb-4">
                <div class="modern-card-header">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-donut me-2"></i>Task Progress
                    </h6>
                </div>
                <div class="card-body chart-container">
                    <canvas id="taskProgressChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Enhanced Quick Actions -->
        <div class="col-xl-6 col-lg-6">
            <div class="card modern-card shadow mb-4">
                <div class="modern-card-header">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="calendar.php" class="quick-action-btn calendar">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <div class="fw-bold">Calendar</div>
                                <small class="opacity-75">Manage tasks</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="manage-announcements.php" class="quick-action-btn announcements">
                                <i class="fas fa-bullhorn me-2"></i>
                                <div class="fw-bold">Announcements</div>
                                <small class="opacity-75">Post updates</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="manage-students.php" class="quick-action-btn students">
                                <i class="fas fa-users me-2"></i>
                                <div class="fw-bold">Students</div>
                                <small class="opacity-75">Manage records</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="manage-courses.php" class="quick-action-btn courses">
                                <i class="fas fa-book me-2"></i>
                                <div class="fw-bold">Courses</div>
                                <small class="opacity-75">Course catalog</small>
                            </a>
                        </div>
                        <div class="col-12">
                            <a href="view-curriculum.php" class="quick-action-btn curriculum">
                                <i class="fas fa-clipboard-list me-2"></i>
                                <div class="fw-bold">Curriculum</div>
                                <small class="opacity-75">Academic programs</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Quick Actions Only (when no tasks) -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-8 mx-auto">
            <div class="card modern-card shadow mb-4">
                <div class="modern-card-header">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-2 col-md-4 col-6">
                            <a href="calendar.php" class="quick-action-btn calendar">
                                <i class="fas fa-calendar-alt mb-2" style="font-size: 1.5rem;"></i>
                                <div class="fw-bold">Calendar</div>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <a href="manage-announcements.php" class="quick-action-btn announcements">
                                <i class="fas fa-bullhorn mb-2" style="font-size: 1.5rem;"></i>
                                <div class="fw-bold">Announcements</div>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <a href="manage-students.php" class="quick-action-btn students">
                                <i class="fas fa-users mb-2" style="font-size: 1.5rem;"></i>
                                <div class="fw-bold">Students</div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 col-6">
                            <a href="manage-courses.php" class="quick-action-btn courses">
                                <i class="fas fa-book mb-2" style="font-size: 1.5rem;"></i>
                                <div class="fw-bold">Courses</div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 col-12">
                            <a href="view-curriculum.php" class="quick-action-btn curriculum">
                                <i class="fas fa-clipboard-list mb-2" style="font-size: 1.5rem;"></i>
                                <div class="fw-bold">Curriculum</div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Chart.js Scripts -->
<script>
// Modern, Simple, and Responsive Chart Implementation

// Color palette - easily customizable
const CHART_COLORS = {
    primary: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
    gradients: {
        blue: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        green: 'linear-gradient(135deg, #1cc88a 0%, #14a76c 100%)',
        orange: 'linear-gradient(135deg, #f6c23e 0%, #e4a92b 100%)',
        red: 'linear-gradient(135deg, #e74a3b 0%, #d32f2f 100%)'
    }
};

// Common chart options for consistency
const COMMON_OPTIONS = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: '#5a5c69',
                font: { size: 12, weight: '500' },
                usePointStyle: true,
                padding: 15
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0,0,0,0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            cornerRadius: 8,
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 }
        }
    },
    animation: {
        duration: 1500,
        easing: 'easeInOutQuart'
    }
};

// 1. Year Level Distribution Chart (Doughnut)
const yearLevelChart = new Chart(document.getElementById('yearLevelChart'), {
    type: 'doughnut',
    data: {
        labels: [<?php foreach($year_levels as $level) echo "'" . $level['year_level'] . "',"; ?>],
        datasets: [{
            data: [<?php foreach($year_levels as $level) echo $level['count'] . ","; ?>],
            backgroundColor: CHART_COLORS.primary,
            borderColor: '#fff',
            borderWidth: 2,
            hoverBorderWidth: 3,
            hoverOffset: 8
        }]
    },
    options: {
        ...COMMON_OPTIONS,
        cutout: '60%',
        plugins: {
            ...COMMON_OPTIONS.plugins,
            legend: {
                ...COMMON_OPTIONS.plugins.legend,
                position: 'bottom'
            },
            tooltip: {
                ...COMMON_OPTIONS.plugins.tooltip,
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed * 100) / total).toFixed(1);
                        return `${context.label}: ${context.parsed} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// 2. Academic Terms Chart (Bar)
const termChart = new Chart(document.getElementById('academicTermChart'), {
    type: 'bar',
    data: {
        labels: [<?php foreach($academic_terms as $term) echo "'" . $term['academic_term'] . "',"; ?>],
        datasets: [{
            label: 'Students',
            data: [<?php foreach($academic_terms as $term) echo $term['count'] . ","; ?>],
            backgroundColor: CHART_COLORS.primary.slice(0, 3),
            borderColor: CHART_COLORS.primary.slice(0, 3),
            borderWidth: 1,
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        ...COMMON_OPTIONS,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(234, 236, 244, 0.3)'
                },
                ticks: {
                    color: '#858796',
                    font: { size: 11 }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#858796',
                    font: { size: 11 }
                }
            }
        },
        plugins: {
            ...COMMON_OPTIONS.plugins,
            legend: {
                display: false
            }
        }
    }
});

// 3. Monthly Registration Trends (Line with Area)
const monthlyChart = new Chart(document.getElementById('monthlyRegistrationsChart'), {
    type: 'line',
    data: {
        labels: [<?php foreach($monthly_data as $month) echo "'" . date('M Y', strtotime($month['month'])) . "',"; ?>],
        datasets: [{
            label: 'New Registrations',
            data: [<?php foreach($monthly_data as $month) echo $month['count'] . ","; ?>],
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#4e73df',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        ...COMMON_OPTIONS,
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(234, 236, 244, 0.3)',
                    borderDash: [2, 2]
                },
                ticks: {
                    color: '#858796',
                    font: { size: 11 },
                    callback: function(value) {
                        return value + ' students';
                    }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#858796',
                    font: { size: 11 }
                }
            }
        },
        plugins: {
            ...COMMON_OPTIONS.plugins,
            legend: {
                display: false
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});

<?php if($stats['pending_tasks'] > 0 || $stats['completed_tasks'] > 0): ?>
// 4. Task Progress Chart (Doughnut)
const taskChart = new Chart(document.getElementById('taskProgressChart'), {
    type: 'doughnut',
    data: {
        labels: ['Pending Tasks', 'Completed Tasks'],
        datasets: [{
            data: [<?= $stats['pending_tasks'] ?>, <?= $stats['completed_tasks'] ?>],
            backgroundColor: ['#f6c23e', '#1cc88a'],
            borderColor: '#fff',
            borderWidth: 2,
            hoverBorderWidth: 3,
            hoverOffset: 6
        }]
    },
    options: {
        ...COMMON_OPTIONS,
        cutout: '65%',
        plugins: {
            ...COMMON_OPTIONS.plugins,
            legend: {
                ...COMMON_OPTIONS.plugins.legend,
                position: 'bottom'
            },
            tooltip: {
                ...COMMON_OPTIONS.plugins.tooltip,
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed * 100) / total).toFixed(1);
                        return `${context.label}: ${context.parsed} (${percentage}%)`;
                    }
                }
            }
        }
    }
});
<?php endif; ?>

// Chart responsiveness enhancement
function handleChartResize() {
    [yearLevelChart, termChart, monthlyChart<?php if($stats['pending_tasks'] > 0 || $stats['completed_tasks'] > 0): ?>, taskChart<?php endif; ?>].forEach(chart => {
        if (chart) chart.resize();
    });
}

// Handle window resize
let resizeTimeout;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(handleChartResize, 100);
});
</script>

<!-- Keep existing footer -->
<?php include 'footer.php'; ?>


