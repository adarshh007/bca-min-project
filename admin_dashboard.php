<?php
require_once 'db.php';
require_once 'includes/workshop_functions.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Auto-update workshop statuses
check_and_update_workshop_status($pdo);

// Dashboard stats
$totalWorkshops = (int)$pdo->query("SELECT COUNT(*) FROM workshops")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalRegistrations = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
$totalFeedback = (int)$pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn();

// Recent registrations
$recentStmt = $pdo->query("
    SELECT u.name AS user_name, w.title AS workshop_title, r.reg_date
    FROM registrations r
    JOIN users u ON r.user_id = u.user_id
    JOIN workshops w ON r.workshop_id = w.workshop_id
    ORDER BY r.reg_date DESC
    LIMIT 5
");
$recentRegs = $recentStmt->fetchAll();

// Upcoming workshops
$upStmt = $pdo->query("
    SELECT title, date, time, location
    FROM workshops
    WHERE date >= CURDATE() AND status <> 'completed'
    ORDER BY date ASC, time ASC
    LIMIT 5
");
$upcoming = $upStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Workshop Hub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand">Admin Dashboard</span>
    <a href="logout.php" class="btn btn-outline-light">Logout</a>
  </div>
</nav>

<div class="container py-4">

  <!-- Top Navigation as Cards -->
  <div class="row g-3 mb-4 text-center">
    <div class="col-6 col-md-2">
      <a href="admin_dashboard.php" class="text-decoration-none">
        <div class="card shadow-sm">
          <div class="card-body">
            <i class="bi bi-speedometer2 fs-3 text-primary"></i>
            <div>Dashboard</div>
          </div>
        </div>
      </a>
    </div>
    <div class="col-6 col-md-2">
      <a href="admin/add_workshop.php" class="text-decoration-none">
        <div class="card shadow-sm">
          <div class="card-body">
            <i class="bi bi-plus-square fs-3 text-success"></i>
            <div>Add Workshop</div>
          </div>
        </div>
      </a>
    </div>
    <div class="col-6 col-md-2">
      <a href="admin/manage_workshops.php" class="text-decoration-none">
        <div class="card shadow-sm">
          <div class="card-body">
            <i class="bi bi-calendar-event fs-3 text-warning"></i>
            <div>Manage</div>
          </div>
        </div>
      </a>
    </div>
    <div class="col-6 col-md-2">
      <a href="admin/users.php" class="text-decoration-none">
        <div class="card shadow-sm">
          <div class="card-body">
            <i class="bi bi-people fs-3 text-info"></i>
            <div>Users</div>
          </div>
        </div>
      </a>
    </div>
    <div class="col-6 col-md-2">
      <a href="admin/registrations.php" class="text-decoration-none">
        <div class="card shadow-sm">
          <div class="card-body">
            <i class="bi bi-person-check fs-3 text-danger"></i>
            <div>Registrations</div>
          </div>
        </div>
      </a>
    </div>
    <div class="col-6 col-md-2">
      <a href="admin/feedback.php" class="text-decoration-none">
        <div class="card shadow-sm">
          <div class="card-body">
            <i class="bi bi-chat-dots fs-3 text-secondary"></i>
            <div>Feedback</div>
          </div>
        </div>
      </a>
    </div>
  </div>

  <!-- Stats Section -->
  <div class="row g-4 mb-4">
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <div class="text-muted">Total Workshops</div>
          <div class="display-6"><?php echo $totalWorkshops; ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <div class="text-muted">Total Users</div>
          <div class="display-6"><?php echo $totalUsers; ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <div class="text-muted">Registrations</div>
          <div class="display-6"><?php echo $totalRegistrations; ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <div class="text-muted">Feedback</div>
          <div class="display-6"><?php echo $totalFeedback; ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Registrations + Upcoming Workshops -->
  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header">Recent Registrations</div>
        <div class="card-body">
          <?php if ($recentRegs): ?>
            <table class="table table-sm">
              <thead><tr><th>User</th><th>Workshop</th><th>Date</th></tr></thead>
              <tbody>
                <?php foreach ($recentRegs as $r): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($r['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['workshop_title']); ?></td>
                    <td><?php echo htmlspecialchars($r['reg_date']); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="text-muted">No registrations yet.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card">
        <div class="card-header">Upcoming Workshops</div>
        <div class="card-body">
          <?php if ($upcoming): ?>
            <ul class="list-group list-group-flush">
              <?php foreach ($upcoming as $u): ?>
                <li class="list-group-item">
                  <div class="fw-bold"><?php echo htmlspecialchars($u['title']); ?></div>
                  <div class="small text-muted">
                    <?php echo htmlspecialchars($u['date'].' '.$u['time'].' @ '.$u['location']); ?>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <div class="text-muted">No upcoming workshops.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
