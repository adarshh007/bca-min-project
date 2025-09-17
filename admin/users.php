<?php
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
	header('Location: ../login.php');
	exit();
}

$message = '';

// Delete user (except self)
if (isset($_GET['delete'])) {
	$uid = (int)$_GET['delete'];
	if ($uid === (int)$_SESSION['user_id']) {
		$message = 'You cannot delete your own account';
	} else {
		try {
			$stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
			$stmt->execute([$uid]);
			$message = 'User deleted';
		} catch (Exception $e) {
			$message = 'Failed to delete user';
		}
	}
}

// List users
$users = $pdo->query('SELECT user_id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Users - Admin</title>
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
				<li class="nav-item"><a class="nav-link active" href="users.php">Users</a></li>
				<li class="nav-item"><a class="nav-link" href="registrations.php">Registrations</a></li>
				<li class="nav-item"><a class="nav-link" href="feedback.php">Feedback</a></li>
				<li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
			</ul>
		</nav>
		<main class="col-md-10 ms-sm-auto px-4 py-4">
			<div class="d-flex justify-content-between align-items-center mb-4">
				<h2><i class="bi bi-people"></i> Users</h2>
			</div>
			<?php if ($message): ?>
				<div class="alert alert-info"><?php echo $message; ?></div>
			<?php endif; ?>
			<div class="table-responsive">
				<table class="table table-striped table-hover align-middle">
					<thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
					<tbody>
					<?php foreach ($users as $u): ?>
						<tr>
							<td><?php echo htmlspecialchars($u['name']); ?></td>
							<td><?php echo htmlspecialchars($u['email']); ?></td>
							<td><span class="badge <?php echo $u['role']==='admin'?'bg-primary':'bg-secondary'; ?>"><?php echo htmlspecialchars($u['role']); ?></span></td>
							<td><?php echo htmlspecialchars($u['created_at']); ?></td>
							<td>
								<?php if ($u['user_id'] !== (int)$_SESSION['user_id']): ?>
									<a href="?delete=<?php echo $u['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user? This will remove their registrations and feedback.')"><i class="bi bi-trash"></i></a>
								<?php endif; ?>
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


