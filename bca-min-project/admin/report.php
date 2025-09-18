<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get current date for comparison
$current_date = date('Y-m-d');

// Query for total workshops count
$total_query = "SELECT COUNT(*) as total FROM workshops";
$total_result = mysqli_query($conn, $total_query);
$total_workshops = mysqli_fetch_assoc($total_result)['total'];

// Query for completed workshops count
$completed_query = "SELECT COUNT(*) as completed FROM workshops WHERE date < '$current_date'";
$completed_result = mysqli_query($conn, $completed_query);
$completed_workshops = mysqli_fetch_assoc($completed_result)['completed'];

// Query for upcoming workshops count
$upcoming_query = "SELECT COUNT(*) as upcoming FROM workshops WHERE date > '$current_date'";
$upcoming_result = mysqli_query($conn, $upcoming_query);
$upcoming_workshops = mysqli_fetch_assoc($upcoming_result)['upcoming'];

// Query for active workshops count (workshops happening today)
$active_query = "SELECT COUNT(*) as active FROM workshops WHERE date = '$current_date'";
$active_result = mysqli_query($conn, $active_query);
$active_workshops = mysqli_fetch_assoc($active_result)['active'];

// Query for completed workshops list
$completed_workshops_query = "SELECT w.*, 
    (SELECT COUNT(*) FROM registrations r WHERE r.workshop_id = w.id) as registrations
    FROM workshops w 
    WHERE w.date < '$current_date'
    ORDER BY w.date DESC";
$completed_workshops_result = mysqli_query($conn, $completed_workshops_query);

// Query for upcoming workshops list
$upcoming_workshops_query = "SELECT w.*,
    (w.total_seats - (SELECT COUNT(*) FROM registrations r WHERE r.workshop_id = w.id)) as available_seats
    FROM workshops w 
    WHERE w.date > '$current_date'
    ORDER BY w.date ASC";
$upcoming_workshops_result = mysqli_query($conn, $upcoming_workshops_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workshop Reports - Admin Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Download CSV Button -->
        <div class="mb-4 text-end">
            <a href="download_report.php" class="btn btn-primary">
                <i class="bi bi-download"></i> Download CSV Report
            </a>
        </div>

        <!-- Statistics Cards Section -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Workshops</h5>
                        <h2 class="card-text"><?php echo $total_workshops; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Completed Workshops</h5>
                        <h2 class="card-text"><?php echo $completed_workshops; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Upcoming Workshops</h5>
                        <h2 class="card-text"><?php echo $upcoming_workshops; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Active Workshops</h5>
                        <h2 class="card-text"><?php echo $active_workshops; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Workshop Tables Section -->
        <div class="row">
            <div class="col-12 mb-4">
                <h3>Completed Workshops</h3>
                <div class="table-responsive">
                    <?php if (mysqli_num_rows($completed_workshops_result) > 0): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Total Seats</th>
                                    <th>Registrations</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($workshop = mysqli_fetch_assoc($completed_workshops_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($workshop['title']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($workshop['date'])); ?></td>
                                        <td><?php echo htmlspecialchars($workshop['location']); ?></td>
                                        <td><?php echo $workshop['total_seats']; ?></td>
                                        <td><?php echo $workshop['registrations']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">No completed workshops yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12">
                <h3>Upcoming Workshops</h3>
                <div class="table-responsive">
                    <?php if (mysqli_num_rows($upcoming_workshops_result) > 0): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Location</th>
                                    <th>Available Seats</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($workshop = mysqli_fetch_assoc($upcoming_workshops_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($workshop['title']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($workshop['date'])); ?></td>
                                        <td><?php echo htmlspecialchars($workshop['location']); ?></td>
                                        <td><?php echo $workshop['available_seats']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">No upcoming workshops.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>