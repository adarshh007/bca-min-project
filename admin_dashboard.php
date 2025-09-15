<?php
require_once 'db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
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
<nav class="navbar navbar-expand-lg navbar-dark bg-">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Admin Dashboard</a>
        <div class="d-flex">
            <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
    </div>
</nav>
<div class="container py-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-plus-circle display-4 text-primary"></i>
                    <h5 class="card-title mt-3">Add Workshop</h5>
                    <p class="card-text">Create a new workshop event.</p>
                    <a href="admin/add_workshop.php" class="btn btn-primary">Add Workshop</a>
                </div>
                
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-people display-4 text-success"></i>
                    <h5 class="card-title mt-3">Manage Participants</h5>
                    <p class="card-text">View and manage participant registrations.</p>
                    <a href="admin/view_registrations.php" class="btn btn-success">Manage Participants</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-calendar-check display-4 text-warning"></i>
                    <h5 class="card-title mt-3">Manage Workshops</h5>
                    <p class="card-text">Edit or delete existing workshops.</p>
                    <a href="admin/manage_workshops.php" class="btn btn-warning text-white">Manage Workshops</a>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
