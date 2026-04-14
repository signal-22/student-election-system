<?php
session_start();
include 'db_connect.php';

// Make sure voter is logged in
if (!isset($_SESSION['voter_id'])) {
    header("Location: voter_login.php");
    exit();
}

$voter_id = $_SESSION['voter_id'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote'])) {
    $votes = $_POST['vote']; // Array of position_id => candidate_id
    $error = '';
    
    // Begin transaction to ensure all-or-nothing
    mysqli_begin_transaction($conn);

    try {
        foreach ($votes as $position_id => $candidate_id) {
            // Insert vote
            $stmt = mysqli_prepare($conn, "INSERT INTO votes (voter_id, position_id, candidate_id) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'iii', $voter_id, $position_id, $candidate_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        // Mark voter as has_voted
        mysqli_query($conn, "UPDATE voters SET has_voted = 1 WHERE id = '$voter_id'");

        mysqli_commit($conn);

        // Redirect to vote success page
        header("Location: vote_success.php");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Error submitting vote: " . $e->getMessage();
    }
} else {
    $error = "No votes submitted!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vote Submission</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center text-danger">Vote Submission Error</h2>
    <p class="text-center"><?= htmlspecialchars($error); ?></p>
    <div class="text-center mt-3">
        <a href="vote.php" class="btn btn-primary">Go Back to Voting</a>
    </div>
</div>
</body>
</html>
