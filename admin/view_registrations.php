<?php
require_once '../db.php';
requireAdmin();

// Fetch all registrations with user and workshop info
$stmt = $pdo->prepare('
    SELECT r.registration_id, r.status AS reg_status, r.reg_date, 
           u.user_id, u.name AS user_name, u.email, 
           w.workshop_id, w.title AS workshop_title, w.date AS workshop_date
    FROM registrations r
    JOIN users u ON r.user_id = u.user_id
    JOIN workshops w ON r.workshop_id = w.workshop_id
    ORDER BY w.date DESC, r.reg_date DESC
');
$stmt->execute();
$registrations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Participants - Workshop Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="../admin_dashboard.php">Admin Dashboard</a>
        <div class="d-flex">
            <a href="../logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </div>
</nav>
<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-people"></i> Manage Participants</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Participant Name</th>
                    <th>Email</th>
                    <th>Workshop</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Registered At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registrations)): ?>
                    <tr><td colspan="7" class="text-center">No registrations found.</td></tr>
                <?php else: ?>
                    <?php foreach ($registrations as $i => $reg): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($reg['user_name']) ?></td>
                            <td><?= htmlspecialchars($reg['email']) ?></td>
                            <td><?= htmlspecialchars($reg['workshop_title']) ?></td>
                            <td><?= formatDate($reg['workshop_date']) ?></td>
                            <td>
                                <?php if ($reg['reg_status'] === 'confirmed'): ?>
                                    <span class="badge bg-success">Confirmed</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td><?= formatDate($reg['reg_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <a href="../admin_dashboard.php" class="btn btn-secondary mt-3"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 