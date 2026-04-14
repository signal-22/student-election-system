<?php
session_start();
include 'db_connect.php';

// Only officer can access
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'officer') {
    header("Location: login.php");
    exit();
}

// Fetch statistics
$total_voters_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM voters");
$total_voters = mysqli_fetch_assoc($total_voters_query)['total'];

$total_votes_query = mysqli_query($conn, "SELECT COUNT(DISTINCT voter_id) AS total_cast FROM votes");
$total_votes_cast = mysqli_fetch_assoc($total_votes_query)['total_cast'];

$remaining_voters = $total_voters - $total_votes_cast;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
        }
        .stats-card {
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .menu a {
            margin-bottom: 15px;
            font-size: 16px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <span class="navbar-brand">Officer Dashboard - Student Election</span>
        <a href="logout.php" class="btn btn-light">Logout</a>
    </div>
</nav>

<div class="container mt-4">

    <!-- Statistics -->
    <div class="row text-center mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stats-card p-3">
                <h5>Total Registered Voters</h5>
                <p class="fs-3 text-primary"><?= $total_voters; ?></p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stats-card p-3">
                <h5>Total Votes Cast</h5>
                <p class="fs-3 text-success"><?= $total_votes_cast; ?></p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stats-card p-3">
                <h5>Voters Remaining</h5>
                <p class="fs-3 text-warning"><?= $remaining_voters; ?></p>
            </div>
        </div>
    </div>

    <!-- Menu Buttons -->
    <div class="row menu justify-content-center">
        <div class="col-md-3">
            <a href="voters_list.php" class="btn btn-info w-100">View Voters</a>
        </div>
        <div class="col-md-3">
            <a href="vote_monitor.php" class="btn btn-primary w-100">Manage Votes</a>
        </div>
        <div class="col-md-3">
            <a href="results_table.php" class="btn btn-success w-100">View Results (Table)</a>
        </div>
        <div class="col-md-3">
            <a href="results_graph.php" class="btn btn-success w-100">View Results (Charts)</a>
        </div>
    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
