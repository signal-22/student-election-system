<?php
session_start();
include 'db_connect.php';

// If voter is not logged in, redirect
if (!isset($_SESSION['voter_id'])) {
    header("Location: voter_login.php");
    exit();
}

$voter_id = mysqli_real_escape_string($conn, $_SESSION['voter_id']);

// Fetch voter details
$result = mysqli_query($conn, "SELECT fullname, has_voted FROM voters WHERE id='$voter_id'");
$voter = mysqli_fetch_assoc($result);

$voter_name = htmlspecialchars($voter['fullname']);
$has_voted = $voter['has_voted'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Voter Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background: #f8f9fa; }
    .dashboard-card { max-width: 450px; margin: auto; margin-top: 60px; }
    .vote-btn { font-size: 1.1rem; padding: 12px; }
</style>
</head>
<body>

<div class="container">
    <div class="card shadow dashboard-card">
        <div class="card-body text-center">
            <h3 class="mb-3">Welcome, <?= $voter_name; ?> 👋</h3>

            <?php if ($has_voted == 0): ?>
                <p class="text-muted">You have not voted yet. Click below to start voting.</p>
                <a href="vote.php" class="btn btn-primary w-100 vote-btn">Start Voting</a>

            <?php else: ?>
                <p class="text-success"><strong>✔ You have already voted!</strong></p>
                <a href="vote_success.php" class="btn btn-success w-100 vote-btn">View Your Vote</a>
            <?php endif; ?>

            <hr>
            <a href="logout.php" class="btn btn-outline-danger w-100">Logout</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
