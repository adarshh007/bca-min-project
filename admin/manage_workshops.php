<?php
require_once '../db.php';
require_once '../includes/workshop_functions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
	header('Location: ../login.php');
	exit();
}

check_and_update_workshop_status($pdo);

$message = '';

// Delete workshop
if (isset($_GET['delete'])) {
	$id = (int)$_GET['delete'];
	try {
		$stmt = $pdo->prepare('DELETE FROM workshops WHERE workshop_id = ?');
		$stmt->execute([$id]);
		$message = 'Workshop deleted';
	} catch (Exception $e) {
		$message = 'Failed to delete workshop';
	}
}

// Fetch all workshops
$stmt = $pdo->query('SELECT * FROM workshops ORDER BY date DESC, time DESC');
$workshops = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Manage Workshops - Admin</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
	<div class="row">
		<nav class="col-md-2 d-none d-md-block bg-light sidebar min-vh-100 p-3">
			<h5 class="mb-3"><i class="bi bi-speedometer2"></i> Admin</h5>
			<ul class="nav flex-column">
				<li class="nav-item"><a class="nav-link" href="../admin_dashboard.php">Dashboard</a></li>
				<li class="nav-item"><a class="nav-link" href="add_workshop.php">Add Workshop</a></li>
				<li class="nav-item"><a class="nav-link active" href="manage_workshops.php">Manage Workshops</a></li>
				<li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
				<li class="nav-item"><a class="nav-link" href="registrations.php">Registrations</a></li>
				<li class="nav-item"><a class="nav-link" href="feedback.php">Feedback</a></li>
				<li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
			</ul>
		</nav>
		<main class="col-md-10 ms-sm-auto px-4 py-4">
			<div class="d-flex justify-content-between align-items-center mb-4">
				<h2><i class="bi bi-calendar-check"></i> Manage Workshops</h2>
				<a class="btn btn-primary" href="add_workshop.php"><i class="bi bi-plus-circle"></i> Add</a>
			</div>
			<?php if ($message): ?>
				<div class="alert alert-info"><?php echo $message; ?></div>
			<?php endif; ?>
			<div class="table-responsive">
				<table class="table table-striped table-hover align-middle">
					<thead>
					<tr>
						<th>Title</th>
						<th>Date</th>
						<th>Time</th>
						<th>Location</th>
						<th>Seats</th>
						<th>Available</th>
						<th>Status</th>
						<th>Actions</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($workshops as $w): ?>
						<tr>
							<td><?php echo htmlspecialchars($w['title']); ?></td>
							<td><?php echo htmlspecialchars($w['date']); ?></td>
							<td><?php echo htmlspecialchars($w['time']); ?></td>
							<td><?php echo htmlspecialchars($w['location']); ?></td>
							<td><?php echo (int)$w['seats']; ?></td>
							<td><?php echo (int)$w['available_seats']; ?></td>
							<td>
								<span class="badge <?php echo $w['status'] === 'completed' ? 'bg-secondary' : 'bg-success'; ?>">
									<?php echo htmlspecialchars($w['status']); ?>
								</span>
							</td>
							<td>
								<a href="edit_workshop.php?id=<?php echo $w['workshop_id']; ?>" class="btn btn-sm btn-warning text-white"><i class="bi bi-pencil"></i></a>
								<a href="?delete=<?php echo $w['workshop_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this workshop?')"><i class="bi bi-trash"></i></a>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</main>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


