<?php
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
	header('Location: ../login.php');
	exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

// Load existing
$stmt = $pdo->prepare('SELECT * FROM workshops WHERE workshop_id = ?');
$stmt->execute([$id]);
$workshop = $stmt->fetch();

if (!$workshop) {
	header('Location: manage_workshops.php');
	exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$title = sanitizeInput($_POST['title'] ?? '');
	$description = trim($_POST['description'] ?? '');
	$date = $_POST['date'] ?? '';
	$time = $_POST['time'] ?? '';
	$location = sanitizeInput($_POST['location'] ?? '');
	$seats = (int)($_POST['seats'] ?? 0);
	$available_seats = (int)($_POST['available_seats'] ?? 0);
	$status = $_POST['status'] ?? 'active';

	if ($title === '' || $date === '' || $time === '' || $seats <= 0) {
		$error = 'Please fill all required fields and ensure seats > 0';
	} else if ($available_seats > $seats) {
		$error = 'Available seats cannot exceed total seats';
	} else {
		try {
			$stmt = $pdo->prepare('UPDATE workshops SET title = ?, description = ?, date = ?, time = ?, location = ?, seats = ?, available_seats = ?, status = ? WHERE workshop_id = ?');
			$stmt->execute([$title, $description, $date, $time, $location, $seats, $available_seats, $status, $id]);
			$success = 'Workshop updated successfully';
			// reload
			$stmt = $pdo->prepare('SELECT * FROM workshops WHERE workshop_id = ?');
			$stmt->execute([$id]);
			$workshop = $stmt->fetch();
		} catch (Exception $e) {
			$error = 'Failed to update workshop';
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Edit Workshop - Admin</title>
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
				<h2><i class="bi bi-pencil-square"></i> Edit Workshop</h2>
				<a class="btn btn-secondary" href="manage_workshops.php"><i class="bi bi-list"></i> Back</a>
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
								<input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($workshop['title']); ?>">
							</div>
							<div class="col-md-6">
								<label class="form-label">Location</label>
								<input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($workshop['location']); ?>">
							</div>
							<div class="col-md-6">
								<label class="form-label">Date *</label>
								<input type="date" name="date" class="form-control" required value="<?php echo htmlspecialchars($workshop['date']); ?>">
							</div>
							<div class="col-md-6">
								<label class="form-label">Time *</label>
								<input type="time" name="time" class="form-control" required value="<?php echo htmlspecialchars($workshop['time']); ?>">
							</div>
							<div class="col-md-6">
								<label class="form-label">Total Seats *</label>
								<input type="number" name="seats" class="form-control" min="1" required value="<?php echo (int)$workshop['seats']; ?>">
							</div>
							<div class="col-md-6">
								<label class="form-label">Available Seats</label>
								<input type="number" name="available_seats" class="form-control" min="0" value="<?php echo (int)$workshop['available_seats']; ?>">
							</div>
							<div class="col-12">
								<label class="form-label">Description</label>
								<textarea name="description" rows="5" class="form-control"><?php echo htmlspecialchars($workshop['description']); ?></textarea>
							</div>
							<div class="col-md-6">
								<label class="form-label">Status</label>
								<select class="form-select" name="status">
									<option value="active" <?php echo $workshop['status']==='active'?'selected':''; ?>>active</option>
									<option value="completed" <?php echo $workshop['status']==='completed'?'selected':''; ?>>completed</option>
								</select>
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


