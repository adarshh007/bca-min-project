<?php
require_once 'db.php';
requireLogin();

// 1️⃣ Auto-update workshop status before fetching
$pdo->query("UPDATE workshops SET status='completed' WHERE date < CURDATE() AND status='active'");

// 2️⃣ Get user's registered ACTIVE workshops only
$stmt = $pdo->prepare("
    SELECT w.*, r.reg_date, r.status AS reg_status
    FROM workshops w
    JOIN registrations r ON w.workshop_id = r.workshop_id
    WHERE r.user_id = ? AND w.status='active'
    ORDER BY w.date ASC
");
$stmt->execute([$_SESSION['user_id']]);
$registered_workshops = $stmt->fetchAll();

// 3️⃣ Get user's COMPLETED workshops
$stmt = $pdo->prepare("
    SELECT w.*, r.reg_date
    FROM workshops w
    JOIN registrations r ON w.workshop_id = r.workshop_id
    WHERE r.user_id = ? AND w.status='completed'
    ORDER BY w.date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$completed_workshops = $stmt->fetchAll();

// 4️⃣ Get upcoming workshops user has NOT registered for
$stmt = $pdo->prepare("
    SELECT * FROM workshops
    WHERE date >= CURDATE()
    AND status = 'active'
    AND workshop_id NOT IN (
        SELECT workshop_id FROM registrations WHERE user_id = ? AND status = 'confirmed'
    )
    ORDER BY date ASC
");
$stmt->execute([$_SESSION['user_id']]);
$available_workshops = $stmt->fetchAll();

$upcoming_events_count = count($registered_workshops);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Workshop Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .dashboard-header {background: linear-gradient(135deg,#667eea,#764ba2);color:white;padding:2rem 0;}
        .stats-card {border:none;box-shadow:0 4px 6px rgba(0,0,0,0.1);transition:transform 0.3s;}
        .stats-card:hover {transform:translateY(-5px);}
        .workshop-card {transition:transform 0.3s;}
        .workshop-card:hover {transform:translateY(-3px);}
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-calendar-event"></i> Workshop Hub</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Home</a>
                <a class="nav-link active" href="user_dashboard.php">Dashboard</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="container">
            <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
            <p class="lead">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card stats-card text-center h-100">
                    <div class="card-body">
                        <div class="text-primary mb-2"><i class="bi bi-calendar-check" style="font-size:2rem;"></i></div>
                        <h3><?php echo count($registered_workshops); ?></h3>
                        <p class="text-muted mb-0">Active Registrations</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card stats-card text-center h-100">
                    <div class="card-body">
                        <div class="text-success mb-2"><i class="bi bi-calendar-plus" style="font-size:2rem;"></i></div>
                        <h3><?php echo count($available_workshops); ?></h3>
                        <p class="text-muted mb-0">Available Workshops</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card stats-card text-center h-100">
                    <div class="card-body">
                        <div class="text-info mb-2"><i class="bi bi-check2-circle" style="font-size:2rem;"></i></div>
                        <h3><?php echo count($completed_workshops); ?></h3>
                        <p class="text-muted mb-0">Completed Workshops</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Registered Workshops -->
    <div class="container mt-4">
        <h2><i class="bi bi-calendar-check"></i> My Registered Workshops</h2>
        <?php if (empty($registered_workshops)): ?>
            <div class="alert alert-info">You have no active workshop registrations.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($registered_workshops as $workshop): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card workshop-card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($workshop['title']); ?></h5>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($workshop['description'], 0, 100)).'...'; ?></p>
                                <div class="mt-auto">
                                    <p><small><i class="bi bi-calendar"></i> <?php echo date('M d, Y', strtotime($workshop['date'])); ?></small></p>
                                    <p><small><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($workshop['location']); ?></small></p>
                                    <span class="badge bg-success">Confirmed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Completed Workshops -->
    <div class="container mt-4">
        <h2><i class="bi bi-check2-circle"></i> Completed Workshops</h2>
        <?php if (empty($completed_workshops)): ?>
            <div class="alert alert-secondary">No workshops completed yet.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($completed_workshops as $workshop): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card workshop-card h-100 border-success">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($workshop['title']); ?></h5>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($workshop['description'], 0, 100)).'...'; ?></p>
                                <div class="mt-auto">
                                    <p><small><i class="bi bi-calendar"></i> <?php echo date('M d, Y', strtotime($workshop['date'])); ?></small></p>
                                    <p><small><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($workshop['location']); ?></small></p>
                                    <span class="badge bg-secondary">Completed</span>
                                    <a href="feedback.php?workshop_id=<?php echo $workshop['workshop_id']; ?>" class="btn btn-sm btn-outline-primary ms-2">Feedback</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Available Workshops -->
    <div class="container mt-5">
        <h2><i class="bi bi-calendar-plus"></i> Available Workshops</h2>
        <?php if (empty($available_workshops)): ?>
            <div class="alert alert-info">No new workshops available right now.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($available_workshops as $workshop): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card workshop-card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($workshop['title']); ?></h5>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars(substr($workshop['description'], 0, 100)).'...'; ?></p>
                                <div class="mt-auto">
                                    <p><small><i class="bi bi-calendar"></i> <?php echo date('M d, Y', strtotime($workshop['date'])); ?></small></p>
                                    <p><small><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($workshop['location']); ?></small></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?php echo $workshop['available_seats'] > 5 ? 'success' : 'warning'; ?>">
                                            <?php echo $workshop['available_seats']; ?> seats left
                                        </span>
                                        <a href="workshop_details.php?id=<?php echo $workshop['workshop_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> Workshop Hub. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
