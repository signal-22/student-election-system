<?php
session_start();
include 'db_connect.php';

// Redirect if voter not logged in
if (!isset($_SESSION['voter_id'])) {
    header("Location: voter_login.php");
    exit();
}

$voter_id = $_SESSION['voter_id'];

// Fetch votes cast by this voter
$votes_query = mysqli_query($conn, "
    SELECT v.*, c.name AS candidate_name, c.photo AS candidate_photo, p.position_name
    FROM votes v
    INNER JOIN candidates c ON v.candidate_id = c.id
    INNER JOIN positions p ON v.position_id = p.id
    WHERE v.voter_id='$voter_id'
");

$votes = [];
while ($vote = mysqli_fetch_assoc($votes_query)) {
    // Fix photo path
    $vote['candidate_photo'] = str_replace('candidate_photos/candidate_photos/', 'candidate_photos/', $vote['candidate_photo']);
    $vote['candidate_photo'] = $vote['candidate_photo'] ?: 'candidate_photos/default.jpg';
    $votes[] = $vote;
}

// If voter hasn't voted, redirect to voting page
if (empty($votes)) {
    header("Location: vote.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vote Summary</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #1e1e1e; /* Dark coding background */
        color: #f0f0f0;
        font-family: 'Fira Code', monospace;
    }
    .container {
        max-width: 700px;
        margin-top: 50px;
    }
    .vote-card {
        background-color: #2d2d2d;
        border-left: 5px solid #28a745;
        padding: 15px;
        margin-bottom: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .vote-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.7);
    }
    .candidate-photo {
        width: 70px;
        height: 70px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #28a745;
    }
    h2 {
        text-align: center;
        color: #28a745;
        margin-bottom: 20px;
    }
    .back-btn {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
    .back-btn a {
        text-decoration: none;
        color: #1e1e1e;
        background-color: #28a745;
        padding: 10px 25px;
        border-radius: 5px;
        font-weight: bold;
        transition: 0.2s;
    }
    .back-btn a:hover {
        background-color: #1fa12e;
        color: #fff;
    }
    .vote-details p {
        margin: 0;
        font-size: 0.95rem;
    }

    /* Terminal-style blinking cursor */
    .cursor {
        display: inline-block;
        width: 10px;
        background-color: #28a745;
        margin-left: 5px;
        animation: blink 1s infinite;
        vertical-align: bottom;
    }

    @keyframes blink {
        0%, 50%, 100% { background-color: #28a745; }
        25%, 75% { background-color: transparent; }
    }
</style>
</head>
<body>
<div class="container">
    <h2>Thank You for Voting!</h2>
    <p class="text-center mb-4">Here’s a summary of your selections:</p>

    <?php foreach ($votes as $vote): ?>
        <div class="vote-card d-flex align-items-center">
            <img src="<?= $vote['candidate_photo']; ?>" class="candidate-photo me-3" alt="<?= htmlspecialchars($vote['candidate_name']); ?>">
            <div class="vote-details">
                <p><strong>Candidate:</strong> <?= htmlspecialchars($vote['candidate_name']); ?></p>
                <p><strong>Position:</strong> <?= htmlspecialchars($vote['position_name']); ?></p>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="text-center mt-4">
        <span>Return to Dashboard<span class="cursor"></span></span>
    </div>

    <div class="back-btn">
        <a href="voter_dashboard.php">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
