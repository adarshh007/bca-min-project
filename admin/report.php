<?php
require_once '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Summary stats
$completed = (int)$pdo->query("SELECT COUNT(*) FROM workshops WHERE status = 'completed'")->fetchColumn();
$upcoming  = (int)$pdo->query("SELECT COUNT(*) FROM workshops WHERE date >= CURDATE() AND status = 'active'")->fetchColumn();
$active    = (int)$pdo->query("SELECT COUNT(*) FROM workshops WHERE status = 'active'")->fetchColumn();

// Fetch completed workshops list
$completedList = $pdo->query("
    SELECT title, date, time, location
    FROM workshops
    WHERE status = 'completed'
    ORDER BY date DESC
")->fetchAll();

// Fetch upcoming workshops list
$upcomingList = $pdo->query("
    SELECT title, date, time, location
    FROM workshops
    WHERE date >= CURDATE() AND status = 'active'
    ORDER BY date ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand">Reports</span>
    <a href="../admin_dashboard.php" class="btn btn-outline-light">Back to Dashboard</a>
  </div>
</nav>

<div class="container py-4">
  <!-- Summary Cards -->
  <div class="row g-4 mb-4 text-center">
    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <i class="bi bi-check-circle fs-3 text-success"></i>
          <h5>Completed Workshops</h5>
          <p class="display-6"><?php echo $completed; ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <i class="bi bi-calendar-event fs-3 text-primary"></i>
          <h5>Upcoming Workshops</h5>
          <p class="display-6"><?php echo $upcoming; ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <i class="bi bi-lightbulb fs-3 text-warning"></i>
          <h5>Active Workshops</h5>
          <p class="display-6"><?php echo $active; ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Completed Workshops Table -->
  <div class="card mb-4">
    <div class="card-header bg-success text-white">
      <i class="bi bi-check-circle"></i> Completed Workshops
    </div>
    <div class="card-body">
      <?php if ($completedList): ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead><tr><th>Title</th><th>Date</th><th>Time</th><th>Location</th></tr></thead>
            <tbody>
              <?php foreach ($completedList as $w): ?>
                <tr>
                  <td><?php echo htmlspecialchars($w['title']); ?></td>
                  <td><?php echo htmlspecialchars($w['date']); ?></td>
                  <td><?php echo htmlspecialchars($w['time']); ?></td>
                  <td><?php echo htmlspecialchars($w['location']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-muted">No completed workshops yet.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Upcoming Workshops Table -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">
      <i class="bi bi-calendar-event"></i> Upcoming Workshops
    </div>
    <div class="card-body">
      <?php if ($upcomingList): ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead><tr><th>Title</th><th>Date</th><th>Time</th><th>Location</th></tr></thead>
            <tbody>
              <?php foreach ($upcomingList as $w): ?>
                <tr>
                  <td><?php echo htmlspecialchars($w['title']); ?></td>
                  <td><?php echo htmlspecialchars($w['date']); ?></td>
                  <td><?php echo htmlspecialchars($w['time']); ?></td>
                  <td><?php echo htmlspecialchars($w['location']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-muted">No upcoming workshops.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
