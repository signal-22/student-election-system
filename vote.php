<?php
session_start();
include 'db_connect.php';

// Redirect if voter not logged in
if (!isset($_SESSION['voter_id'])) {
    header("Location: voter_login.php");
    exit();
}

$voter_id = $_SESSION['voter_id'];

// Prevent multiple voting
$stmt = $conn->prepare("SELECT has_voted FROM voters WHERE id = ?");
$stmt->bind_param("i", $voter_id);
$stmt->execute();
$stmt->bind_result($has_voted);
$stmt->fetch();
$stmt->close();

if ($has_voted) {
    die("You have already voted.");
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch candidates securely
$positions = [];
$pos_stmt = $conn->prepare("SELECT id, position_name FROM positions ORDER BY position_name ASC");
$pos_stmt->execute();
$pos_result = $pos_stmt->get_result();

while ($pos = $pos_result->fetch_assoc()) {
    $position_id = $pos['id'];

    $cand_stmt = $conn->prepare("SELECT id, name, photo FROM candidates WHERE position_id = ?");
    $cand_stmt->bind_param("i", $position_id);
    $cand_stmt->execute();
    $cand_result = $cand_stmt->get_result();

    $candidates = [];
    while ($cand = $cand_result->fetch_assoc()) {
        // Fix photo path safely
        $photo = $cand['photo'] ?: 'candidate_photos/default.jpg';
        $photo = str_replace('candidate_photos/candidate_photos/', 'candidate_photos/', $photo);
        $cand['photo'] = htmlspecialchars($photo, ENT_QUOTES, 'UTF-8');
        $cand['name'] = htmlspecialchars($cand['name'], ENT_QUOTES, 'UTF-8');
        $candidates[] = $cand;
    }

    $positions[] = [
        'id' => $position_id,
        'name' => htmlspecialchars($pos['position_name'], ENT_QUOTES, 'UTF-8'),
        'candidates' => $candidates
    ];

    $cand_stmt->close();
}

$pos_stmt->close();

// Handle vote submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    if (!isset($_POST['vote']) || !is_array($_POST['vote'])) {
        die("Invalid vote submission.");
    }

    $conn->begin_transaction();
    try {
        $vote_stmt = $conn->prepare("INSERT INTO votes (voter_id, position_id, candidate_id) VALUES (?, ?, ?)");

        foreach ($_POST['vote'] as $position_id => $candidate_id) {
            $position_id = (int)$position_id;
            $candidate_id = (int)$candidate_id;

            // Optional: Verify that candidate belongs to the position
            $check_stmt = $conn->prepare("SELECT id FROM candidates WHERE id = ? AND position_id = ?");
            $check_stmt->bind_param("ii", $candidate_id, $position_id);
            $check_stmt->execute();
            $check_stmt->store_result();
            if ($check_stmt->num_rows === 0) {
                throw new Exception("Invalid candidate selection.");
            }
            $check_stmt->close();

            $vote_stmt->bind_param("iii", $voter_id, $position_id, $candidate_id);
            $vote_stmt->execute();
        }
        $vote_stmt->close();

        // Mark voter as having voted
        $update_stmt = $conn->prepare("UPDATE voters SET has_voted = 1 WHERE id = ?");
        $update_stmt->bind_param("i", $voter_id);
        $update_stmt->execute();
        $update_stmt->close();

        $conn->commit();
        header("Location: vote_success.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Error processing vote: " . htmlspecialchars($e->getMessage()));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vote</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* ... keep your existing CSS ... */
</style>
</head>
<body>
<div class="container">
    <h2>Cast Your Vote</h2>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <?php foreach ($positions as $pos): ?>
            <div class="position-card">
                <h4><?= $pos['name']; ?></h4>
                <div class="row">
                    <?php foreach ($pos['candidates'] as $cand): ?>
                        <div class="col-md-4">
                            <label>
                                <input type="radio" name="vote[<?= $pos['id']; ?>]" value="<?= $cand['id']; ?>" required>
                                <div class="candidate-card">
                                    <img src="<?= $cand['photo']; ?>" alt="<?= $cand['name']; ?>">
                                    <p><?= $cand['name']; ?></p>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="submit-btn">
            <button type="submit">Submit Vote</button>
        </div>
    </form>
</div>
</body>
</html>
