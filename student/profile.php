<?php include 'navbar.php'; ?>

<div class="container mt-0">
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
                    <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i>My Profile</h4>
                </div>
                <div class="card-body">
                    <?php
                    include '../includes/db.php';

                    $username = $_SESSION['username'];

                    $stmt = $conn->prepare("SELECT * FROM students WHERE username = ?");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $student = $result->fetch_assoc();
                    $stmt->close();

                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = 'student';
                    ?>

                    <div class="row">
                        <div class="col-md-4 mb-4 mb-md-0">
                            <div class="text-center">
                                <div class="profile-pic-wrapper mb-3">
                                    <div style="width: 150px; height: 150px; background-color: hsl(217, 65.90%, 25.30%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; color: white; font-size: 50px;">
                                        <?php 
                                        $initials = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));
                                        echo $initials;
                                        ?>
                                    </div>
                                </div>
                                <h4 class="mb-1"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h4>
                                <p class="text-muted mb-3">Student ID: <?= htmlspecialchars($student['id']) ?></p>
                                <p class="badge bg-primary"><?= htmlspecialchars($student['program']) ?></p>
                                
                                <div class="d-grid gap-2 mt-4">
                                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                        <i class="fas fa-key me-1"></i> Change Password
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#updateProfileModal">
                                        <i class="fas fa-edit me-1"></i> Update Information
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Personal Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table profile-table table-hover mb-0">
                                        <tbody>
                                            <tr>
                                                <th style="width: 35%">First Name</th>
                                                <td><?= htmlspecialchars($student['first_name']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Middle Name</th>
                                                <td><?= htmlspecialchars($student['middle_name']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Last Name</th>
                                                <td><?= htmlspecialchars($student['last_name']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Date of Birth</th>
                                                <td><?= htmlspecialchars($student['date_of_birth']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Address</th>
                                                <td><?= htmlspecialchars($student['address']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Contact Number</th>
                                                <td><?= htmlspecialchars($student['contact_number']) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="card border-0 shadow-sm mt-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Academic Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table profile-table table-hover mb-0">
                                        <tbody>
                                            <tr>
                                                <th style="width: 35%">Program</th>
                                                <td><?= htmlspecialchars($student['program']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Year Level</th>
                                                <td><?= htmlspecialchars($student['year_level']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Current Semester</th>
                                                <td><?= htmlspecialchars($student['academic_term']) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Username</th>
                                                <td><?= htmlspecialchars($student['username']) ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Change Password</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Profile Modal -->
<div class="modal fade" id="updateProfileModal" tabindex="-1" aria-labelledby="updateProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: hsl(217, 65.90%, 25.30%); color: white;">
                <h5 class="modal-title" id="updateProfileModalLabel">Update Profile Information</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateProfileForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contactNumber" class="form-label">Contact Number</label>
                            <input type="text" class="form-control" id="contactNumber" value="<?= htmlspecialchars($student['contact_number']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="emailAddress" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="emailAddress" value="student@example.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" rows="3"><?= htmlspecialchars($student['address']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Emergency Contact</label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Name" id="emergencyName">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Relationship" id="emergencyRelationship">
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Contact Number" id="emergencyContact">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom styling for profile page */
.profile-table {
    margin-bottom: 0;
}

.profile-table th {
    background-color: rgba(59, 130, 246, 0.1);
    font-weight: 600;
    color: #374151;
    border-bottom-width: 0;
}

.profile-table td {
    color: #4b5563;
}

.profile-table tr:hover {
    background-color: rgba(59, 130, 246, 0.05);
}

.profile-pic-wrapper {
    transition: transform 0.3s ease;
}

.profile-pic-wrapper:hover {
    transform: scale(1.05);
}
</style>

<?php include 'footer.php'; ?>