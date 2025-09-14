<?php
require_once '../db.php';
//session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $workshop_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM workshops WHERE workshop_id = ?");
    $stmt->execute([$workshop_id]);
    header('Location: manage_workshops.php');
    exit();
}

// Handle status update
if (isset($_POST['update_status'])) {
    $workshop_id = (int)$_POST['workshop_id'];
    $status = $_POST['status'] === 'active' ? 'active' : 'inactive';
    $stmt = $pdo->prepare("UPDATE workshops SET status = ? WHERE workshop_id = ?");
    $stmt->execute([$status, $workshop_id]);
    header('Location: manage_workshops.php');
    exit();
}

// Fetch all workshops
$stmt = $pdo->query("SELECT * FROM workshops ORDER BY date DESC");
$workshops = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Workshops - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php">Admin Dashboard</a>
        <div class="d-flex">
            <a href="../logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </div>
</nav>
<div class="container py-5">
    <h2 class="mb-4">Manage Workshops</h2>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($workshops): ?>
                <?php foreach ($workshops as $workshop): ?>
                    <tr>
                        <td><?= $workshop['workshop_id'] ?></td>
                        <td><?= htmlspecialchars($workshop['title']) ?></td>
                        <td><?= htmlspecialchars($workshop['date']) ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="workshop_id" value="<?= $workshop['workshop_id'] ?>">
                                <select name="status" class="form-select form-select-sm d-inline w-auto">
                                    <option value="active" <?= $workshop['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $workshop['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    <option value="completed" <?= $workshop['status'] === 'completed' ? 'selected' : '' ?>>completed</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                        <td>
                            <a href="edit_workshop.php?id=<?= $workshop['workshop_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="manage_workshops.php?delete=<?= $workshop['workshop_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this workshop?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No workshops found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
