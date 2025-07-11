<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/db.php';

$program_code = isset($_GET['program']) ? $_GET['program'] : '';

if (empty($program_code)) {
    include 'header.php';
    $programs_query = $conn->query("SELECT program_code, program_name FROM programs ORDER BY program_code");
?>
    <div class="card shadow-sm mb-4">
        <div class="card-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
            <h3 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>View Program Curriculum</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fas fa-university me-1 text-primary"></i> Select Program:</label>
                    <select name="program" class="form-select" required>
                        <option value="">-- Select Program --</option>
                        <?php while ($row = $programs_query->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['program_code']) ?>">
                                <?= htmlspecialchars($row['program_code']) ?> - <?= htmlspecialchars($row['program_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> View Curriculum</button>
                </div>
            </form>
        </div>
    </div>
<?php
} else {
    include 'header.php';
    $query = "SELECT c.title, c.credits, pc.year_level, pc.academic_term, 
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
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $program_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $program_query = $conn->prepare("SELECT program_name FROM programs WHERE program_code = ?");
    $program_query->bind_param("s", $program_code);
    $program_query->execute();
    $program_result = $program_query->get_result();
    $program_name = ($program_result->num_rows > 0) ? $program_result->fetch_assoc()['program_name'] : '';
?>
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
            <h3 class="mb-0"><i class="fas fa-book me-2"></i>Curriculum for <?= htmlspecialchars($program_name) ?></h3>
            <a href="view-curriculum.php" class="btn btn-light btn-sm"><i class="fas fa-sync-alt me-1"></i> Change Program</a>
        </div>
        <div class="card-body">
            <?php if ($program_name): ?>
                <h4 class="text-center mb-4"><?= htmlspecialchars($program_name) ?></h4>
            <?php endif; ?>
            
            <?php if ($result->num_rows == 0): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> No courses have been assigned to this program yet.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
                                <th>Year</th>
                                <th>Term</th>
                                <th>Course Title</th>
                                <th width="8%">Units</th>
                                <th width="25%">Pre-requisites</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $current_year = '';
                            $current_term = '';
                            $year_row_counts = [];
                            $term_row_counts = [];
                            
                            $result_copy = $result;
                            while ($row = $result_copy->fetch_assoc()) {
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
                            
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            while ($row = $result->fetch_assoc()): 
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
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 d-flex justify-content-between">
                    <a href="view-curriculum.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print me-1"></i> Print Curriculum
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
}
include 'footer.php';
?>
<link rel="stylesheet" href="css/view-curriculum.css">