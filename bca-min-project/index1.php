<?php
require_once 'db.php';

// Get upcoming workshops
$stmt = $pdo->prepare("SELECT * FROM workshops WHERE date >= CURDATE() AND status = 'active' ORDER BY date ASC");
$stmt->execute();
$workshops = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workshop Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .workshop-card {
            transition: transform 0.3s;
        }
        .workshop-card:hover {
            transform: translateY(-5px);
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-calendar-event"></i> Workshop Hub
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="user_dashboard.php">Dashboard</a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/admin_dashboard.php">Admin</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                          <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 mb-4">Welcome to Workshop Hub</h1>
            <p class="lead mb-4">Discover and register for amazing workshops and bootcamps</p>
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-light btn-lg me-3">Get Started</a>
                <a href="login.php" class="btn btn-outline-light btn-lg">Login</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Workshops Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Upcoming Workshops</h2>
  
            <?php if (empty($workshops)): ?>
                <div class="text-center">
                    <p class="text-muted">No upcoming workshops at the moment.</p>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($workshops as $workshop): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card workshop-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($workshop['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($workshop['description'], 0, 100)) . '...'; ?></p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i> <?php echo formatDate($workshop['date']); ?>
                                        </small>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> <?php echo formatTime($workshop['time']); ?>
                                        </small>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($workshop['location']); ?>
                                        </small>
                                        <span class="badge bg-<?php echo $workshop['available_seats'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $workshop['available_seats']; ?> seats left
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="workshop_details.php?id=<?php echo $workshop['workshop_id']; ?>" 
                                       class="btn btn-primary w-100">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>&copy; 2025 Workshop Hub. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>