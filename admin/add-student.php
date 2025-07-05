<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../includes/db.php';

// Place all form processing and header("Location: ...") logic here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize inputs
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $date_of_birth = $_POST['date_of_birth'];
    $address = trim($_POST['address']);
    $contact_number = trim($_POST['contact_number']);
    $email = trim($_POST['email']);
    $program = trim($_POST['program']);
    $year_level = $_POST['year_level'];
    $academic_term = trim($_POST['academic_term']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validation check
    if (empty($first_name) || empty($last_name) || empty($email) || empty($program) || empty($username) || empty($_POST['password'])) {
        $error_message = '⚠️ Please fill in all required fields.';
    } else {
        // Check if email already exists
        $check_email = $conn->prepare("SELECT id FROM students WHERE email = ?");
        $check_email->bind_param('s', $email);
        $check_email->execute();
        $check_email->store_result();
        if ($check_email->num_rows > 0) {
            $error_message = '✉️ Email already exists. Please use a different email.';
            $check_email->close();
        } else {
            $check_email->close();

            // Check if username already exists in users table
            $check_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check_username->bind_param('s', $username);
            $check_username->execute();
            $check_username->store_result();
            if ($check_username->num_rows > 0) {
                $error_message = '👤 Username already exists. Please choose a different username.';
                $check_username->close();
            } else {
                $check_username->close();

                $sql = "INSERT INTO students 
                        (first_name, middle_name, last_name, date_of_birth, address, contact_number, email, program, year_level, academic_term, username, password, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    $error_message = '❌ Database prepare failed: ' . $conn->error;
                } else {
                    $stmt->bind_param(
                        "ssssssssssss",
                        $first_name, $middle_name, $last_name, $date_of_birth, $address, $contact_number,
                        $email, $program, $year_level, $academic_term, $username, $password
                );
                    if ($stmt->execute()) {
                        // Get the new student's ID
                        $new_student_id = $conn->insert_id;

                        // Prepare user insert (use the same username and password, role = 'student')
                        $user_stmt = $conn->prepare("INSERT INTO users (username, password, role, student_id, created_at) VALUES (?, ?, 'student', ?, NOW())");
                        $user_stmt->bind_param('ssi', $username, $password, $new_student_id);

                        if ($user_stmt->execute()) {
                            header("Location: manage-students.php?success=1");
                            exit;
                        } else {
                            $error_message = '❌ User creation failed: ' . $user_stmt->error;
                        }
                        $user_stmt->close();
                    } else {
                        $error_message = '❌ Execute failed: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

// Only after all PHP logic, include the header and output HTML
include 'header.php';
?>

<link rel="stylesheet" href="css/add-student.css">
<div class="form-container">
    <div class="form-header">
        <h2>Student Registration</h2>
        <p>Fill out the form below to add a new student to the system</p>
    </div>
    
    <div class="form-body">
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?= $error_message ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Personal Information Section -->
            <div class="section-title">
                <div class="section-icon">👤</div>
                Personal Information
            </div>
            
            <div class="form-grid three-cols">
                <div class="form-group">
                    <label class="form-label required">First Name</label>
                    <input type="text" name="first_name" class="form-input" required 
                           placeholder="e.g., Juan Han"
                           value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" class="form-input" 
                           placeholder="e.g., Andrade"
                           value="<?= isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label required">Last Name</label>
                    <input type="text" name="last_name" class="form-input" required 
                           placeholder="e.g., Dela Cruz"
                           value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>">
                </div>
            </div>

            <div class="form-grid two-cols">
                <div class="form-group">
                    <label class="form-label required">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-input" required
                           value="<?= isset($_POST['date_of_birth']) ? htmlspecialchars($_POST['date_of_birth']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="tel" name="contact_number" class="form-input" 
                           placeholder="09123456789" 
                           pattern="^09\d{9}$" 
                           maxlength="11"
                           value="<?= isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : '' ?>">
                    <div class="input-hint">Format: 09XXXXXXXXX (11 digits)</div>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-textarea" 
                              placeholder="e.g., 123 Summit Avenue, Barangay Example, City, Province"><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label required">Email Address</label>
                    <input type="email" name="email" class="form-input" required 
                           placeholder="juanDelacruz@example.com"
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
            </div>

            <!-- Academic Information Section -->
            <div class="section-title">
                <div class="section-icon">🎓</div>
                Academic Information
            </div>

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label required">Program</label>
                    <select name="program" class="form-select" required>
                        <option value="">-- Select Program --</option>
                        <optgroup label="Engineering Programs">
                            <option value="BSCpE" <?= (isset($_POST['program']) && $_POST['program'] == 'BSCpE') ? 'selected' : '' ?>>BSCpE – Bachelor of Science in Computer Engineering</option>
                            <option value="BSECE" <?= (isset($_POST['program']) && $_POST['program'] == 'BSECE') ? 'selected' : '' ?>>BSECE – Bachelor of Science in Electronics Engineering</option>
                            <option value="BSEE" <?= (isset($_POST['program']) && $_POST['program'] == 'BSEE') ? 'selected' : '' ?>>BSEE – Bachelor of Science in Electrical Engineering</option>
                            <option value="BSME" <?= (isset($_POST['program']) && $_POST['program'] == 'BSME') ? 'selected' : '' ?>>BSME – Bachelor of Science in Mechanical Engineering</option>
                            <option value="BSCE" <?= (isset($_POST['program']) && $_POST['program'] == 'BSCE') ? 'selected' : '' ?>>BSCE – Bachelor of Science in Civil Engineering</option>
                        </optgroup>
                        <optgroup label="Computer Science Programs">
                            <option value="BSCS-SE" <?= (isset($_POST['program']) && $_POST['program'] == 'BSCS-SE') ? 'selected' : '' ?>>BSCS-SE – Bachelor of Science in Computer Science major in Software Engineering</option>
                            <option value="BSCS-DS" <?= (isset($_POST['program']) && $_POST['program'] == 'BSCS-DS') ? 'selected' : '' ?>>BSCS-DS – Bachelor of Science in Computer Science major in Data Science</option>
                        </optgroup>
                        <optgroup label="Information Technology Programs">
                            <option value="BSIT-BA" <?= (isset($_POST['program']) && $_POST['program'] == 'BSIT-BA') ? 'selected' : '' ?>>BSIT-BA – Bachelor of Science in Information Technology major in Business Analytics</option>
                            <option value="BSIT-IB" <?= (isset($_POST['program']) && $_POST['program'] == 'BSIT-IB') ? 'selected' : '' ?>>BSIT-IB – Bachelor of Science in Information Technology major in Innovation and Business</option>
                            <option value="BSIT-AGD" <?= (isset($_POST['program']) && $_POST['program'] == 'BSIT-AGD') ? 'selected' : '' ?>>BSIT-AGD – Bachelor of Science in Information Technology major in Animation and Game Development</option>
                            <option value="BSIT-WMA" <?= (isset($_POST['program']) && $_POST['program'] == 'BSIT-WMA') ? 'selected' : '' ?>>BSIT-WMA – Bachelor of Science in Information Technology major in Web and Mobile Applications</option>
                            <option value="BSIT-CY" <?= (isset($_POST['program']) && $_POST['program'] == 'BSIT-CY') ? 'selected' : '' ?>>BSIT-CY – Bachelor of Science in Information Technology major in Cybersecurity</option>
                        </optgroup>
                        <optgroup label="Arts Programs">
                            <option value="BMMA" <?= (isset($_POST['program']) && $_POST['program'] == 'BMMA') ? 'selected' : '' ?>>BMMA – Bachelor of Multimedia Arts</option>
                        </optgroup>
                    </select>
                </div>
            </div>

            <div class="form-grid two-cols">
                <div class="form-group">
                    <label class="form-label">Year Level</label>
                    <select name="year_level" class="form-select">
                        <option value="">-- Select Year --</option>
                        <option value="1st" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '1st') ? 'selected' : '' ?>>1st Year</option>
                        <option value="2nd" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '2nd') ? 'selected' : '' ?>>2nd Year</option>
                        <option value="3rd" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '3rd') ? 'selected' : '' ?>>3rd Year</option>
                        <option value="4th" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '4th') ? 'selected' : '' ?>>4th Year</option>
                        <option value="5th" <?= (isset($_POST['year_level']) && $_POST['year_level'] == '5th') ? 'selected' : '' ?>>5th Year</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required">Academic Term</label>
                    <select name="academic_term" class="form-select" required>
                        <option value="">-- Select Term --</option>
                        <option value="1st Term" <?= (isset($_POST['academic_term']) && $_POST['academic_term'] == '1st Term') ? 'selected' : '' ?>>1st Term</option>
                        <option value="2nd Term" <?= (isset($_POST['academic_term']) && $_POST['academic_term'] == '2nd Term') ? 'selected' : '' ?>>2nd Term</option>
                        <option value="3rd Term" <?= (isset($_POST['academic_term']) && $_POST['academic_term'] == '3rd Term') ? 'selected' : '' ?>>3rd Term</option>
                    </select>
                </div>
            </div>

            <!-- Account Information Section -->
            <div class="section-title">
                <div class="section-icon">🔐</div>
                Account Information
            </div>

            <div class="form-grid two-cols">
                <div class="form-group">
                    <label class="form-label required">Username</label>
                    <input type="text" name="username" class="form-input" required
                           placeholder="Choose a unique username"
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    <div class="input-hint">Username must be unique across the system</div>
                </div>

                <div class="form-group">
                    <label class="form-label required">Password</label>
                    <input type="password" name="password" class="form-input" required
                           placeholder="Create a secure password">
                    <div class="input-hint">Use a strong password with letters, numbers, and symbols</div>
                </div>
            </div>

            <button type="submit" class="submit-button">
                Add Student to System
            </button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>





