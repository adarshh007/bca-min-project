<?php
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
	header('Location: ../login.php');
	exit();
}

$workshopFilter = isset($_GET['workshop_id']) ? (int)$_GET['workshop_id'] : 0;

// Workshops for filter
$workshops = $pdo->query('SELECT workshop_id, title FROM workshops ORDER BY title ASC')->fetchAll();

// Feedback query
if ($workshopFilter > 0) {
	$stmt = $pdo->prepare('SELECT f.feedback_id, f.rating, f.comments, f.submitted_at, u.name AS user_name, w.title AS workshop_title
						  FROM feedback f
						  JOIN users u ON f.user_id = u.user_id
						  JOIN workshops w ON f.workshop_id = w.workshop_id
						  WHERE f.workshop_id = ?
						  ORDER BY f.submitted_at DESC');
	$stmt->execute([$workshopFilter]);
} else {
	$stmt = $pdo->query('SELECT f.feedback_id, f.rating, f.comments, f.submitted_at, u.name AS user_name, w.title AS workshop_title
					   FROM feedback f
					   JOIN users u ON f.user_id = u.user_id
					   JOIN workshops w ON f.workshop_id = w.workshop_id
					   ORDER BY f.submitted_at DESC');
}
$feedback = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Feedback - Admin</title>
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
				<li class="nav-item"><a class="nav-link" href="registrations.php">Registrations</a></li>
				<li class="nav-item"><a class="nav-link active" href="feedback.php">Feedback</a></li>
				<li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
			</ul>
		</nav>
		<main class="col-md-10 ms-sm-auto px-4 py-4">
			<div class="d-flex justify-content-between align-items-center mb-4">
				<h2><i class="bi bi-chat-left-text"></i> Feedback</h2>
			</div>
			<form class="row g-2 mb-3" method="get">
				<div class="col-md-6">
					<select class="form-select" name="workshop_id" onchange="this.form.submit()">
						<option value="0">All Workshops</option>
						<?php foreach ($workshops as $w): ?>
							<option value="<?php echo $w['workshop_id']; ?>" <?php echo $workshopFilter===$w['workshop_id']?'selected':''; ?>><?php echo htmlspecialchars($w['title']); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</form>
			<div class="table-responsive">
				<table class="table table-striped table-hover align-middle">
					<thead><tr><th>Workshop</th><th>User</th><th>Rating</th><th>Comments</th><th>Submitted</th></tr></thead>
					<tbody>
					<?php foreach ($feedback as $f): ?>
						<tr>
							<td><?php echo htmlspecialchars($f['workshop_title']); ?></td>
							<td><?php echo htmlspecialchars($f['user_name']); ?></td>
							<td><?php echo (int)$f['rating']; ?>/5</td>
							<td><?php echo nl2br(htmlspecialchars($f['comments'])); ?></td>
							<td><?php echo htmlspecialchars($f['submitted_at']); ?></td>
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


