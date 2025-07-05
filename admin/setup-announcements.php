<?php
// Create announcements table if it doesn't exist
include '../includes/db.php';

// SQL to create announcements table
$create_table_sql = "
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    expiry_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($create_table_sql)) {
    echo "Announcements table created or already exists successfully.<br>";
    
    // Check if table structure is correct
    $result = $conn->query("DESCRIBE announcements");
    if ($result) {
        echo "<h3>Current table structure:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Insert sample data if table is empty
    $count_result = $conn->query("SELECT COUNT(*) as count FROM announcements");
    $count = $count_result->fetch_assoc()['count'];
    
    if ($count == 0) {
        $sample_data = "
        INSERT INTO announcements (title, message, is_active, expiry_date) VALUES
        ('Welcome to the New Semester', 'We hope you have a great semester ahead! Please check your schedules and make sure to attend orientation.', 1, '2025-12-31'),
        ('Library Hours Update', 'The library will be open extended hours during finals week. Monday-Friday: 7AM-11PM, Saturday-Sunday: 9AM-9PM.', 1, '2025-08-15'),
        ('Campus Maintenance Notice', 'There will be scheduled maintenance in Building A this weekend. Some areas may be temporarily inaccessible.', 0, '2025-07-20')
        ";
        
        if ($conn->query($sample_data)) {
            echo "<br>Sample announcements added successfully.";
        } else {
            echo "<br>Error adding sample data: " . $conn->error;
        }
    } else {
        echo "<br>Table already has $count announcements.";
    }
    
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>

<style>
table { border-collapse: collapse; margin: 20px 0; }
th, td { padding: 8px 12px; text-align: left; }
th { background-color: #f2f2f2; }
</style>
