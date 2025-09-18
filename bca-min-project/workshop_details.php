<?php
require_once 'db.php';

$workshop_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

// Get workshop details
$stmt = $pdo->prepare("SELECT * FROM workshops WHERE workshop_id = ?");
$stmt->execute([$workshop_id]);
$workshop = $stmt->fetch();

if (!$workshop) {
    header('Location: index.php');
    exit();
}

// Check if user is already registered
$is_registered = false;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM registrations WHERE user_id = ? AND workshop_id = ? AND status = 'confirmed'");
    $stmt->execute([$_SESSION['user_id'], $workshop_id]);
    $is_registered = $stmt->fetch() !== false;
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    if (!isLoggedIn()) {
        $error = 'Please login to register for workshops';
    } elseif ($is_registered) {
        $error = 'You are already registered for this workshop';
    } elseif ($workshop['available_seats'] <= 0) {
        $error = 'Sorry, this workshop is fully booked';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Insert registration
            $stmt = $pdo->prepare("INSERT INTO registrations (user_id, workshop_id, status) VALUES (?, ?, 'confirmed')");
            $stmt->execute([$_SESSION['user_id'], $workshop_id]);
            
            // Update available seats
            $stmt = $pdo->prepare("UPDATE workshops SET available_seats = available_seats - 1 WHERE workshop_id = ?");
            $stmt->execute([$workshop_id]);
            
            $pdo->commit();
            $success = 'Registration successful! You will receive a confirmation email shortly.';
            $is_registered = true;
            
            // Refresh workshop data
            $stmt = $pdo->prepare("SELECT * FROM workshops WHERE workshop_id = ?");
            $stmt->execute([$workshop_id]);
            $workshop = $stmt->fetch();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($workshop['title']); ?> - Workshop Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .workshop-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
        }
        .info-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .registration-card {
            position: sticky;
            top: 20px;
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
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Home</a>
                <?php if (isLoggedIn()): ?>
                    <a class="nav-link" href="user_dashboard.php">Dashboard</a>
                    <a class="nav-link" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Login</a>
                    <a class="nav-link" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Workshop Header -->
    <div class="workshop-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1><?php echo htmlspecialchars($workshop['title']); ?></h1>
                    <p class="lead"><?php echo htmlspecialchars($workshop['description']); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Workshop Details -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-8">
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <div class="card info-card mb-4">
                    <div class="card-body">
                        <h3>Workshop Details</h3>
                        <p><?php echo nl2br(htmlspecialchars($workshop['description'])); ?></p>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5><i class="bi bi-calendar"></i> Date & Time</h5>
                                <p><?php echo formatDate($workshop['date']); ?> at <?php echo formatTime($workshop['time']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="bi bi-geo-alt"></i> Location</h5>
                                <p><?php echo htmlspecialchars($workshop['location']); ?></p>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h5><i class="bi bi-people"></i> Capacity</h5>
                                <p><?php echo $workshop['seats']; ?> total seats</p>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="bi bi-person-check"></i> Available</h5>
                                <p><?php echo $workshop['available_seats']; ?> seats remaining</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card info-card">
                    <div class="card-body">
                        <h3>What You'll Learn</h3>
                        <ul>
                            <li>Comprehensive understanding of the workshop topic</li>
                            <li>Hands-on practical experience</li>
                            <li>Best practices and industry standards</li>
                            <li>Networking opportunities with peers</li>
                            <li>Certificate of completion</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card registration-card">
                    <div class="card-body">
                        <h4>Registration</h4>
                        
                        <?php if ($is_registered): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> You are registered for this workshop!
                            </div>
                        <?php elseif ($workshop['available_seats'] <= 0): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-x-circle"></i> Workshop is fully booked
                            </div>
                        <?php elseif ($workshop['date'] < date('Y-m-d')): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-clock"></i> Registration closed
                            </div>
                        <?php else: ?>
                            <?php if (!isLoggedIn()): ?>
                                <p class="text-muted">Please login to register for this workshop.</p>
                                <a href="login.php" class="btn btn-primary w-100">Login to Register</a>
                            <?php else: ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <strong>Available Seats:</strong> <?php echo $workshop['available_seats']; ?>
                                    </div>
                                    <button type="submit" name="register" class="btn btn-primary w-100">
                                        <i class="bi bi-person-plus"></i> Register Now
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <div class="workshop-info">
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="bi bi-calendar"></i> Date:</span>
                                <span><?php echo formatDate($workshop['date']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="bi bi-clock"></i> Time:</span>
                                <span><?php echo formatTime($workshop['time']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="bi bi-geo-alt"></i> Location:</span>
                                <span><?php echo htmlspecialchars($workshop['location']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><i class="bi bi-people"></i> Seats:</span>
                                <span><?php echo $workshop['available_seats']; ?>/<?php echo $workshop['seats']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2025 Workshop Hub. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>