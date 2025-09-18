<?php
require_once 'db.php';
requireLogin();

$workshop_id = isset($_GET['workshop_id']) ? (int)$_GET['workshop_id'] : 0;
$error = '';
$success = '';

// Get workshop details
$stmt = $pdo->prepare("SELECT * FROM workshops WHERE workshop_id = ?");
$stmt->execute([$workshop_id]);
$workshop = $stmt->fetch();

if (!$workshop) {
    header('Location: user_dashboard.php');
    exit();
}

// Check if user attended the workshop
$stmt = $pdo->prepare("SELECT * FROM registrations WHERE user_id = ? AND workshop_id = ? AND status = 'confirmed'");
$stmt->execute([$_SESSION['user_id'], $workshop_id]);
$registration = $stmt->fetch();

if (!$registration) {
    header('Location: user_dashboard.php');
    exit();
}

// Check if feedback already exists
$stmt = $pdo->prepare("SELECT * FROM feedback WHERE user_id = ? AND workshop_id = ?");
$stmt->execute([$_SESSION['user_id'], $workshop_id]);
$existing_feedback = $stmt->fetch();

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = (int)$_POST['rating'];
    $comments = sanitizeInput($_POST['comments']);
    
    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a valid rating (1-5)';
    } else {
        try {
            if ($existing_feedback) {
                // Update existing feedback
                $stmt = $pdo->prepare("UPDATE feedback SET rating = ?, comments = ? WHERE user_id = ? AND workshop_id = ?");
                $stmt->execute([$rating, $comments, $_SESSION['user_id'], $workshop_id]);
                $success = 'Feedback updated successfully!';
            } else {
                // Insert new feedback
                $stmt = $pdo->prepare("INSERT INTO feedback (user_id, workshop_id, rating, comments) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $workshop_id, $rating, $comments]);
                $success = 'Feedback submitted successfully!';
            }
            
            // Refresh existing feedback
            $stmt = $pdo->prepare("SELECT * FROM feedback WHERE user_id = ? AND workshop_id = ?");
            $stmt->execute([$_SESSION['user_id'], $workshop_id]);
            $existing_feedback = $stmt->fetch();
            
        } catch (Exception $e) {
            $error = 'Failed to submit feedback. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - <?php echo htmlspecialchars($workshop['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .feedback-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
        }
        .star-rating {
            display: flex;
            gap: 5px;
            margin: 10px 0;
        }
        .star {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s;
        }
        .star:hover,
        .star.selected {
            color: #ffc107;
        }
        .feedback-card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
                <a class="nav-link" href="user_dashboard.php">Dashboard</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Feedback Header -->
    <div class="feedback-header">
        <div class="container">
            <h1><i class="bi bi-star"></i> Workshop Feedback</h1>
            <p class="lead">Share your experience with: <?php echo htmlspecialchars($workshop['title']); ?></p>
        </div>
    </div>

    <!-- Feedback Form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
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

                <div class="card feedback-card">
                    <div class="card-body p-4">
                        <h3 class="mb-4">
                            <?php echo $existing_feedback ? 'Update Your Feedback' : 'Share Your Feedback'; ?>
                        </h3>
                        
                        <div class="mb-4 p-3 bg-light rounded">
                            <h5><?php echo htmlspecialchars($workshop['title']); ?></h5>
                            <p class="text-muted mb-1">
                                <i class="bi bi-calendar"></i> <?php echo formatDate($workshop['date']); ?> at <?php echo formatTime($workshop['time']); ?>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($workshop['location']); ?>
                            </p>
                        </div>
                        
                        <form method="POST" id="feedbackForm">
                            <div class="mb-4">
                                <label class="form-label">Overall Rating *</label>
                                <div class="star-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo ($existing_feedback && $existing_feedback['rating'] >= $i) ? 'selected' : ''; ?>" 
                                              data-rating="<?php echo $i; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="rating" value="<?php echo $existing_feedback ? $existing_feedback['rating'] : ''; ?>" required>
                                <small class="form-text text-muted">Click on the stars to rate your experience</small>
                            </div>
                            
                            <div class="mb-4">
                                <label for="comments" class="form-label">Comments & Suggestions</label>
                                <textarea class="form-control" id="comments" name="comments" rows="5" 
                                          placeholder="Please share your thoughts about the workshop, what you liked, and suggestions for improvement..."><?php echo $existing_feedback ? htmlspecialchars($existing_feedback['comments']) : ''; ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="user_dashboard.php" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> <?php echo $existing_feedback ? 'Update Feedback' : 'Submit Feedback'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if ($existing_feedback): ?>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5>Your Previous Feedback</h5>
                            <div class="mb-2">
                                <strong>Rating:</strong> 
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span style="color: <?php echo $i <= $existing_feedback['rating'] ? '#ffc107' : '#ddd'; ?>">★</span>
                                <?php endfor; ?>
                                (<?php echo $existing_feedback['rating']; ?>/5)
                            </div>
                            <div class="mb-2">
                                <strong>Comments:</strong> <?php echo $existing_feedback['comments'] ? nl2br(htmlspecialchars($existing_feedback['comments'])) : 'No comments provided'; ?>
                            </div>
                            <div class="text-muted">
                                <small>Submitted on: <?php echo date('M j, Y g:i A', strtotime($existing_feedback['submitted_at'])); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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
    <script>
        // Star rating functionality
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star');
            const ratingInput = document.getElementById('rating');
            
            stars.forEach((star, index) => {
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('data-rating');
                    ratingInput.value = rating;
                    
                    // Update visual feedback
                    stars.forEach((s, i) => {
                        if (i < rating) {
                            s.classList.add('selected');
                        } else {
                            s.classList.remove('selected');
                        }
                    });
                });
                
                star.addEventListener('mouseenter', function() {
                    const rating = this.getAttribute('data-rating');
                    stars.forEach((s, i) => {
                        if (i < rating) {
                            s.style.color = '#ffc107';
                        } else {
                            s.style.color = '#ddd';
                        }
                    });
                });
            });
            
            // Reset hover effect
            document.querySelector('.star-rating').addEventListener('mouseleave', function() {
                const currentRating = ratingInput.value;
                stars.forEach((s, i) => {
                    if (i < currentRating) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        });
    </script>
</body>
</html>