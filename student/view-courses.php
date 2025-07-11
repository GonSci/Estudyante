<?php
session_start();

include '../includes/db.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
    header("Location: ../login.php");
    exit;
}

include 'navbar.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="css/view-courses.css">

<?php

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id, program, year_level FROM students WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

$program = $student['program'];

function getNormalizedProgramCode($conn, $programName) {
    $query = $conn->prepare("SELECT program_code FROM programs WHERE program_name = ?");
    $query->bind_param("s", $programName);
    $query->execute();
    $result = $query->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['program_code'];
    }
    
    $query = $conn->prepare("SELECT program_code FROM programs WHERE 
                            program_name LIKE ? OR ? LIKE CONCAT('%', program_name, '%')");
    $like_param = "%" . $programName . "%";
    $query->bind_param("ss", $like_param, $programName);
    $query->execute();
    $result = $query->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['program_code'];
    }
    
    $first = $conn->query("SELECT program_code FROM programs LIMIT 1");
    return ($first->num_rows > 0) ? $first->fetch_assoc()['program_code'] : '';
}

$program_code = getNormalizedProgramCode($conn, $student['program']);

$query = "SELECT c.id, c.title, c.credits, pc.year_level, pc.academic_term, 
                 GROUP_CONCAT(pr.title SEPARATOR ', ') as prerequisites
          FROM program_course pc
          JOIN courses c ON pc.course_id = c.id
          LEFT JOIN course_prerequisites cp ON cp.course_id = c.id
          LEFT JOIN courses pr ON cp.prerequisite_id = pr.id
          WHERE pc.program_code = ?
          GROUP BY c.id, pc.year_level, pc.academic_term
          ORDER BY CASE 
                      WHEN pc.year_level = '1st' THEN 1
                      WHEN pc.year_level = '2nd' THEN 2
                      WHEN pc.year_level = '3rd' THEN 3
                      WHEN pc.year_level = '4th' THEN 4
                      ELSE 5
                   END,
                   CASE 
                      WHEN pc.academic_term = '1st Term' THEN 1
                      WHEN pc.academic_term = '2nd Term' THEN 2
                      WHEN pc.academic_term = '3rd Term' THEN 3
                      ELSE 4
                   END,
                   c.title";

$courses_query = $conn->prepare($query);
$courses_query->bind_param("s", $program_code);
$courses_query->execute();
$courses_result = $courses_query->get_result();


try {
    $enrolled_query = $conn->prepare("SELECT course_id FROM student_courses WHERE student_id = ?");
    $enrolled_query->bind_param("i", $student['id']);
    $enrolled_query->execute();
    $enrolled_result = $enrolled_query->get_result();

    $enrolled_courses = [];
    while ($enrolled_row = $enrolled_result->fetch_assoc()) {
        $enrolled_courses[] = $enrolled_row['course_id'];
    }
} catch (Exception $e) {
    echo "<div class='alert alert-warning'>Note: Unable to fetch enrollment status. " . $e->getMessage() . "</div>";
    $enrolled_courses = [];
}
?>

<div class="container mt-4">
    <div class="card mb-4 shadow-sm">
        <div class="card-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-book-open me-2"></i>Program Curriculum</h4>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-light text-dark"><?= htmlspecialchars($program) ?></span>
                    <button onclick="printCurriculum()" class="btn btn-light btn-sm no-print" id="printBtn">
                        <i class="fas fa-print me-1"></i>Print Curriculum
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <p class="no-print">Below is the curriculum for your program: <strong><?= htmlspecialchars($program) ?></strong>. 
               Your current year level is <strong><?= htmlspecialchars($student['year_level']) ?></strong>.</p>
            
            <div class="print-only print-header">
                <div class="text-center mb-4">
                    <div class="institution-header">
                        <h2>CURRICULUM - <?= htmlspecialchars($program) ?></h2>
                    </div>
                    <hr class="header-divider">
                </div>
            </div>
            
            <?php if ($courses_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
                                <th>Year</th>
                                <th>Term</th>
                                <th>Course Title</th>
                                <th>Units</th>
                                <th>Prerequisites</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $current_year = '';
                            $current_term = '';
                            $year_row_counts = [];
                            $term_row_counts = [];
                            
                            $courses_copy = [];
                            while ($row = $courses_result->fetch_assoc()) {
                                $courses_copy[] = $row;
                                
                                if (!isset($year_row_counts[$row['year_level']])) {
                                    $year_row_counts[$row['year_level']] = 0;
                                }
                                $year_row_counts[$row['year_level']]++;
                                
                                $term_key = $row['year_level'] . '_' . $row['academic_term'];
                                if (!isset($term_row_counts[$term_key])) {
                                    $term_row_counts[$term_key] = 0;
                                }
                                $term_row_counts[$term_key]++;
                            }
                            
                            foreach ($courses_copy as $row): 
                                $year_changed = ($current_year != $row['year_level']);
                                $term_changed = ($current_term != $row['academic_term'] || $year_changed);
                                $term_key = $row['year_level'] . '_' . $row['academic_term'];
                                
                                $is_new_term = $term_changed && !$year_changed;
                            ?>
                                <tr <?= $is_new_term ? 'class="term-transition"' : '' ?>>
                                    <?php if ($year_changed): ?>
                                        <td class="year-header" rowspan="<?= $year_row_counts[$row['year_level']] ?>">
                                            <i class="fas fa-calendar-alt me-1"></i> <?= htmlspecialchars($row['year_level']) ?> Year
                                        </td>
                                        <?php $current_year = $row['year_level']; ?>
                                    <?php endif; ?>
                                    
                                    <?php if ($term_changed): ?>
                                        <td class="term-header" rowspan="<?= $term_row_counts[$term_key] ?>">
                                            <?= htmlspecialchars($row['academic_term']) ?>
                                        </td>
                                        <?php $current_term = $row['academic_term']; ?>
                                    <?php endif; ?>
                                    
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['credits']) ?></td>
                                    <td>
                                        <?php if ($row['prerequisites']): ?>
                                            <?= htmlspecialchars($row['prerequisites']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (in_array($row['id'], $enrolled_courses)): ?>
                                            <span class="badge bg-success">Enrolled</span>
                                        <?php elseif ($student['year_level'] === $row['year_level']): ?>
                                            <span class="badge bg-warning">Current Year</span>

                                        <?php elseif (compareYearLevels($student['year_level'], $row['year_level']) > 0): ?>
                                            <span class="badge bg-light text-dark">Completed Year</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Yet Available</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (empty($program_code)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> Could not find a matching program in the system. Please contact the administrator.
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No courses are available for your program at this time.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function printCurriculum() {
    const printBtn = document.getElementById('printBtn');
    
    if (printBtn) {
        const originalText = printBtn.innerHTML;
        printBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Preparing Print...';
        printBtn.disabled = true;
        
        setTimeout(() => {
            printBtn.innerHTML = originalText;
            printBtn.disabled = false;
        }, 3000);
    }
    
    document.body.classList.add('printing');
    
    preparePrintDocument();
    
    setTimeout(() => {
        window.print();
        
        document.body.classList.remove('printing');
    }, 200);
}

function preparePrintDocument() {
    const originalTitle = document.title;
    document.title = `Curriculum_${<?= json_encode($program) ?>}_StudentID_${<?= json_encode($student['id']) ?>}_${new Date().toISOString().split('T')[0]}`;
    
    console.log('Document prepared for printing');
}

window.addEventListener('beforeprint', function() {
    document.body.classList.add('print-mode');
    
    const dynamicElements = document.querySelectorAll('.dynamic-content');
    dynamicElements.forEach(el => el.style.display = 'none');
});

window.addEventListener('afterprint', function() {
    document.body.classList.remove('print-mode');
    document.title = 'View Courses - Student Registration System';
    
    const dynamicElements = document.querySelectorAll('.dynamic-content');
    dynamicElements.forEach(el => el.style.display = '');
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Print Complete!',
            text: 'Your curriculum has been sent to the printer.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
});

document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        printCurriculum();
    }
    
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'P') {
        e.preventDefault();
        window.print();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const printStyles = document.querySelector('style');
    if (printStyles && printStyles.textContent.includes('@media print')) {
        console.log('Print styles loaded successfully');
    }
    
    const printBtn = document.getElementById('printBtn');
    if (printBtn) {
        printBtn.title = 'Print curriculum (Ctrl+P)';
        
        printBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        printBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    }
});

function optimizePrintLayout() {
    const tables = document.querySelectorAll('.table');
    tables.forEach(table => {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            if (index > 0 && index % 15 === 0) {
                row.style.pageBreakBefore = 'auto';
            }
        });
    });
}

window.addEventListener('beforeprint', optimizePrintLayout);
</script>

<?php include 'footer.php'; ?>

<?php
function compareYearLevels($studentYear, $courseYear) {
    $yearOrder = [
        '1st' => 1,
        '2nd' => 2,
        '3rd' => 3,
        '4th' => 4,
        'First' => 1,
        'Second' => 2, 
        'Third' => 3,
        'Fourth' => 4
    ];
    
    if (is_numeric($studentYear)) $studentYear = intval($studentYear);
    if (is_numeric($courseYear)) $courseYear = intval($courseYear);
    
    if (is_int($studentYear)) {
        $studentYear = match($studentYear) {
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            4 => '4th',
            default => '1st'
        };
    }
    
    if (is_int($courseYear)) {
        $courseYear = match($courseYear) {
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            4 => '4th',
            default => '1st'
        };
    }
    
    $studentYearValue = $yearOrder[$studentYear] ?? 1;
    $courseYearValue = $yearOrder[$courseYear] ?? 1;
    
    return $studentYearValue <=> $courseYearValue;
}