<?php
session_start();
require_once 'db.php';
require_once 'includes/workshop_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Update workshop status
check_and_update_workshop_status($conn);

$user_id = $_SESSION['user_id'];

// Get user's completed workshops
$sql = "SELECT w.*, r.registration_id, 
        CASE WHEN f.feedback_id IS NULL THEN 1 ELSE 0 END as can_give_feedback
        FROM workshops w
        INNER JOIN registrations r ON w.workshop_id = r.workshop_id
        LEFT JOIN feedback f ON w.workshop_id = f.workshop_id AND f.user_id = ?
        WHERE w.status = 'completed' 
        AND r.user_id = ?
        ORDER BY w.date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Workshops</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Workshops You Attended</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td>
                                    <?php if ($row['can_give_feedback']): ?>
                                        <a href="feedback.php?workshop_id=<?php echo $row['workshop_id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            Give Feedback
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-success">Feedback Submitted</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                You haven't attended any completed workshops yet.
            </div>
        <?php endif; ?>
        
        <a href="user_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
