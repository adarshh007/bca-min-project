<?php
function check_and_update_workshop_status($pdo) {
	// Mark past workshops as completed
	$sql = "UPDATE workshops 
	        SET status = 'completed' 
	        WHERE date < CURDATE() 
	        AND status <> 'completed'";
	
	$pdo->exec($sql);
}
?>
