<?php
require_once 'db.php';

// Modify workshops table to update status ENUM
$sql = "ALTER TABLE workshops MODIFY COLUMN status ENUM('active','completed','inactive') DEFAULT 'active'";
if ($conn->query($sql) === TRUE) {
    echo "Successfully updated workshops table status column<br>";
} else {
    echo "Error updating table: " . $conn->error . "<br>";
}

// Create function to update workshop status
$create_function = "
CREATE FUNCTION IF NOT EXISTS update_workshop_status()
RETURNS INT
BEGIN
    UPDATE workshops 
    SET status = 'completed' 
    WHERE date < CURDATE() AND status = 'active';
    RETURN ROW_COUNT();
END;
";

if ($conn->multi_query($create_function)) {
    echo "Successfully created update_workshop_status function<br>";
} else {
    echo "Error creating function: " . $conn->error . "<br>";
}

$conn->close();
?>
