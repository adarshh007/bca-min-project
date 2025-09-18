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

// Workshops for filter dropdown
$workshops = $pdo->query('SELECT workshop_id, title FROM workshops ORDER BY title ASC')->fetchAll();

// Filters
$workshopFilter = isset($_GET['workshop_id']) ? (int)$_GET['workshop_id'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query with filters
$sql = "SELECT r.registration_id, r.status, r.reg_date, 
               u.name AS user_name, u.email, 
               w.title AS workshop_title, w.date AS workshop_date
        FROM registrations r
        JOIN users u ON r.user_id = u.user_id
        JOIN workshops w ON r.workshop_id = w.workshop_id
        WHERE 1=1";

$params = [];

// Apply workshop filter
if ($workshopFilter > 0) {
    $sql .= " AND w.workshop_id = ?";
    $params[] = $workshopFilter;
}

// Apply search filter
if (!empty($search)) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY r.reg_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
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

            <!-- Filter + Search Form -->
            <form class="row g-2 mb-3" method="get">
                <div class="col-md-4">
                    <select class="form-select" name="workshop_id" onchange="this.form.submit()">
                        <option value="0">All Workshops</option>
                        <?php foreach ($workshops as $w): ?>
                            <option value="<?php echo $w['workshop_id']; ?>" 
                                <?php echo $workshopFilter===$w['workshop_id']?'selected':''; ?>>
                                <?php echo htmlspecialchars($w['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search by name or email" 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>

            <?php if ($message): ?>
                <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Registrations Table -->
            <?php if (count($registrations) === 0): ?>
                <div class="alert alert-warning">No registrations found.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Workshop</th>
                                <th>Workshop Date</th>
                                <th>Status</th>
                                <th>Registered On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($registrations as $r): ?>
                            <tr>
                                <td><?php echo (int)$r['registration_id']; ?></td>
                                <td><?php echo htmlspecialchars($r['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($r['email']); ?></td>
                                <td><?php echo htmlspecialchars($r['workshop_title']); ?></td>
                                <td><?php echo htmlspecialchars($r['workshop_date']); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php echo $r['status']==='confirmed'?'bg-success':
                                            ($r['status']==='cancelled'?'bg-secondary':'bg-info'); ?>">
                                        <?php echo htmlspecialchars($r['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($r['reg_date']); ?></td>
                                <td>
                                    <a href="?cancel=<?php echo $r['registration_id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Cancel/remove this registration?')">
                                       <i class="bi bi-x-circle"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
