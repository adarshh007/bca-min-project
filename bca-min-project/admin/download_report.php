<?php
session_start();
require_once '../db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get current date
$current_date = date('Y-m-d');

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="workshop_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel handling
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write headers for Completed Workshops
fputcsv($output, array('Completed Workshops'));
fputcsv($output, array('Title', 'Date', 'Location', 'Total Seats', 'Registrations'));

// Query and write completed workshops
$completed_query = "SELECT w.*, 
    (SELECT COUNT(*) FROM registrations r WHERE r.workshop_id = w.id) as registrations
    FROM workshops w 
    WHERE w.date < '$current_date'
    ORDER BY w.date DESC";
$completed_result = mysqli_query($conn, $completed_query);

while ($row = mysqli_fetch_assoc($completed_result)) {
    fputcsv($output, array(
        $row['title'],
        date('d M Y', strtotime($row['date'])),
        $row['location'],
        $row['total_seats'],
        $row['registrations']
    ));
}

// Add blank line between sections
fputcsv($output, array(''));

// Write headers for Upcoming Workshops
fputcsv($output, array('Upcoming Workshops'));
fputcsv($output, array('Title', 'Date', 'Location', 'Available Seats'));

// Query and write upcoming workshops
$upcoming_query = "SELECT w.*,
    (w.total_seats - (SELECT COUNT(*) FROM registrations r WHERE r.workshop_id = w.id)) as available_seats
    FROM workshops w 
    WHERE w.date > '$current_date'
    ORDER BY w.date ASC";
$upcoming_result = mysqli_query($conn, $upcoming_query);

while ($row = mysqli_fetch_assoc($upcoming_result)) {
    fputcsv($output, array(
        $row['title'],
        date('d M Y', strtotime($row['date'])),
        $row['location'],
        $row['total_seats'] - $row['registrations']
    ));
}

// Close the output stream
fclose($output);
exit();