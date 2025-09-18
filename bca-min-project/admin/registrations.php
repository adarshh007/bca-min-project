<?php
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
	header('Location: ../login.php');
	exit();
}

$message = '';

// Cancel/remove a registration
if (isset($_GET['cancel'])) {
	$id = (int)$_GET['cancel'];
	try {
		$stmt = $pdo->prepare('DELETE FROM registrations WHERE registration_id = ?');
		$stmt->execute([$id]);
		$message = 'Registration removed';
	} catch (Exception $e) {
		$message = 'Failed to remove registration';
	}
}

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename="registrations.csv"');
	$out = fopen('php://output', 'w');
	fputcsv($out, ['Registration ID','User Name','Email','Workshop','Status','Reg Date']);
	$sth = $pdo->query('SELECT r.registration_id, u.name, u.email, w.title, r.status, r.reg_date
					   FROM registrations r
					   JOIN users u ON r.user_id = u.user_id
					   JOIN workshops w ON r.workshop_id = w.workshop_id
					   ORDER BY r.reg_date DESC');
	while ($row = $sth->fetch(PDO::FETCH_NUM)) {
		fputcsv($out, $row);
	}
	fclose($out);
	exit();
}

// List registrations
$stmt = $pdo->query('SELECT r.registration_id, r.status, r.reg_date, u.name AS user_name, u.email, w.title AS workshop_title
					  FROM registrations r
					  JOIN users u ON r.user_id = u.user_id
					  JOIN workshops w ON r.workshop_id = w.workshop_id
					  ORDER BY r.reg_date DESC');
$registrations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Registrations - Admin</title>
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
				<li class="nav-item"><a class="nav-link" href="manage_workshops.php">Manage Workshops</a></li>
				<li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
				<li class="nav-item"><a class="nav-link active" href="registrations.php">Registrations</a></li>
				<li class="nav-item"><a class="nav-link" href="feedback.php">Feedback</a></li>
				<li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
			</ul>
		</nav>
		<main class="col-md-10 ms-sm-auto px-4 py-4">
			<div class="d-flex justify-content-between align-items-center mb-4">
				<h2><i class="bi bi-clipboard-check"></i> Registrations</h2>
				<div class="d-flex gap-2">
					<a class="btn btn-outline-primary" href="?export=csv"><i class="bi bi-download"></i> Export CSV</a>
					<button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i> Print/PDF</button>
				</div>
			</div>
			<?php if ($message): ?>
				<div class="alert alert-info"><?php echo $message; ?></div>
			<?php endif; ?>
			<div class="table-responsive">
				<table class="table table-striped table-hover align-middle">
					<thead><tr><th>ID</th><th>User</th><th>Email</th><th>Workshop</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
					<tbody>
					<?php foreach ($registrations as $r): ?>
						<tr>
							<td><?php echo (int)$r['registration_id']; ?></td>
							<td><?php echo htmlspecialchars($r['user_name']); ?></td>
							<td><?php echo htmlspecialchars($r['email']); ?></td>
							<td><?php echo htmlspecialchars($r['workshop_title']); ?></td>
							<td><span class="badge <?php echo $r['status']==='confirmed'?'bg-success':($r['status']==='cancelled'?'bg-secondary':'bg-info'); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td>
							<td><?php echo htmlspecialchars($r['reg_date']); ?></td>
							<td>
								<a href="?cancel=<?php echo $r['registration_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancel/remove this registration?')"><i class="bi bi-x-circle"></i></a>
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


