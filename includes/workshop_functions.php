<?php
function check_and_update_workshop_status($conn) {
    // Update workshop status
    $sql = "UPDATE workshops 
            SET status = 'completed' 
            WHERE date < CURDATE() 
            AND status = 'active'";
    
    $conn->query($sql);
}
?>
