<?php
require_once '../db.php';
require_once '../includes/workshop_functions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
	header('Location: ../login.php');
	exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$title = sanitizeInput($_POST['title'] ?? '');
	$description = trim($_POST['description'] ?? '');
	$date = $_POST['date'] ?? '';
	$time = $_POST['time'] ?? '';
	$location = sanitizeInput($_POST['location'] ?? '');
	$seats = (int)($_POST['seats'] ?? 0);
	$available_seats = (int)($_POST['available_seats'] ?? $seats);

	if ($title === '' || $date === '' || $time === '' || $seats <= 0) {
		$error = 'Please fill all required fields and ensure seats > 0';
	} else {
		try {
			$stmt = $pdo->prepare('INSERT INTO workshops (title, description, date, time, location, seats, available_seats, status) VALUES (?, ?, ?, ?, ?, ?, ?, "active")');
			$stmt->execute([$title, $description, $date, $time, $location, $seats, $available_seats]);
			$success = 'Workshop added successfully';
		} catch (Exception $e) {
			$error = 'Failed to add workshop';
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Add Workshop - Admin</title>
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
				<li class="nav-item"><a class="nav-link active" href="add_workshop.php">Add Workshop</a></li>
				<li class="nav-item"><a class="nav-link" href="manage_workshops.php">Manage Workshops</a></li>
				<li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
				<li class="nav-item"><a class="nav-link" href="registrations.php">Registrations</a></li>
				<li class="nav-item"><a class="nav-link" href="feedback.php">Feedback</a></li>
				<li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
			</ul>
		</nav>
		<main class="col-md-10 ms-sm-auto px-4 py-4">
			<div class="d-flex justify-content-between align-items-center mb-4">
				<h2><i class="bi bi-plus-circle"></i> Add Workshop</h2>
				<a class="btn btn-secondary" href="manage_workshops.php"><i class="bi bi-list"></i> Manage</a>
			</div>
			<?php if ($error): ?>
				<div class="alert alert-danger"><?php echo $error; ?></div>
			<?php endif; ?>
			<?php if ($success): ?>
				<div class="alert alert-success"><?php echo $success; ?></div>
			<?php endif; ?>
			<div class="card">
				<div class="card-body">
					<form method="post">
						<div class="row g-3">
							<div class="col-md-6">
								<label class="form-label">Title *</label>
								<input type="text" name="title" class="form-control" required>
							</div>
							<div class="col-md-6">
								<label class="form-label">Location</label>
								<input type="text" name="location" class="form-control">
							</div>
							<div class="col-md-6">
								<label class="form-label">Date *</label>
								<input type="date" name="date" class="form-control" required>
							</div>
							<div class="col-md-6">
								<label class="form-label">Time *</label>
								<input type="time" name="time" class="form-control" required>
							</div>
							<div class="col-md-6">
								<label class="form-label">Total Seats *</label>
								<input type="number" name="seats" class="form-control" min="1" required>
							</div>
							<div class="col-md-6">
								<label class="form-label">Available Seats</label>
								<input type="number" name="available_seats" class="form-control" min="0">
								<small class="text-muted">Defaults to total seats</small>
							</div>
							<div class="col-12">
								<label class="form-label">Description</label>
								<textarea name="description" rows="5" class="form-control"></textarea>
							</div>
						</div>
						<div class="mt-3 d-flex gap-2">
							<button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Save</button>
							<a href="manage_workshops.php" class="btn btn-outline-secondary">Cancel</a>
						</div>
					</form>
				</div>
			</div>
		</main>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


