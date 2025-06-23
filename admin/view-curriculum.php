<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection, but NOT the header yet
include '../includes/db.php';

// Get the program code from URL parameter or form
$program_code = isset($_GET['program']) ? $_GET['program'] : '';

// If no program is selected yet, show a form to select a program
if (empty($program_code)) {
    include 'header.php';
    // Fetch all available programs
    $programs_query = $conn->query("SELECT program_code, program_name FROM programs ORDER BY program_code");
?>
    <div class="container mt-4">
        <h3>View Program Curriculum</h3>
        <form method="GET" action="">
            <div class="mb-3">
                <label>Select Program:</label>
                <select name="program" class="form-control" required>
                    <option value="">-- Select Program --</option>
                    <?php while ($row = $programs_query->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['program_code']) ?>">
                            <?= htmlspecialchars($row['program_code']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">View Curriculum</button>
        </form>
    </div>
<?php
} else {
    include 'header.php';
    // Fetch all courses for the selected program, grouped by year and term
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
    
    // Organize courses by year and term
    $curriculum = [];
    while ($row = $result->fetch_assoc()) {
        $curriculum[$row['year_level']][$row['academic_term']][] = $row;
    }
?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Curriculum for <?= htmlspecialchars($program_code) ?></h3>
            <a href="view-curriculum.php" class="btn btn-secondary">Change Program</a>
        </div>
        
        <?php if (empty($curriculum)): ?>
            <div class="alert alert-info">No courses have been assigned to this program yet.</div>
        <?php else: ?>
            <?php 
            // Define the order of years and terms for consistent display
            $year_order = ['1st', '2nd', '3rd', '4th'];
            $term_order = ['1st Term', '2nd Term', '3rd Term'];
            
            foreach ($year_order as $year): 
                if (!isset($curriculum[$year])) continue; // Skip if no courses for this year
            ?>
                <div class="mb-5">
                    <h4 class="text-primary"><?= strtoupper($year) ?> YEAR</h4>
                
                    <?php foreach ($term_order as $term): 
                        if (!isset($curriculum[$year][$term])) continue; // Skip if no courses for this term
                    ?>
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><?= strtoupper($term) ?></h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>COURSE TITLE</th>
                                            <th width="10%">UNITS</th>
                                            <th width="30%">PRE-REQUISITES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($curriculum[$year][$term] as $course): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($course['title']) ?></td>
                                                <td class="text-center"><?= htmlspecialchars($course['credits']) ?></td>
                                                <td>
                                                    <?php if ($course['prerequisites']): ?>
                                                        <?= htmlspecialchars($course['prerequisites']) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">None</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php
}
include 'footer.php';
?>