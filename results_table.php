<?php
session_start();
include 'db_connect.php';

// Only admin can access
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch total voters and votes cast
$total_voters_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM voters");
$total_voters = mysqli_fetch_assoc($total_voters_query)['total'];

$total_votes_query = mysqli_query($conn, "SELECT COUNT(DISTINCT voter_id) AS total_cast FROM votes");
$total_votes_cast = mysqli_fetch_assoc($total_votes_query)['total_cast'];

// Fetch positions
$positions_query = mysqli_query($conn, "SELECT * FROM positions ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Election Results - Table</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background-color: #1e1e1e;
    color: #f5f5f5;
    font-family: 'Fira Code', monospace;
}
.container {
    max-width: 900px;
    margin-top: 50px;
}
h2 {
    text-align: center;
    color: #00ff99;
    margin-bottom: 20px;
}
.results-info {
    text-align: center;
    margin-bottom: 30px;
    font-size: 1.1rem;
}
table {
    background-color: #2d2d2d;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 0 15px rgba(0,0,0,0.5);
    width: 100%;
}
thead {
    background-color: #00ff99;
    color: #000;
}
tbody tr:nth-child(even) {
    background-color: #2a2a2a;
}
tbody tr:hover {
    background-color: #3a3a3a;
}
th, td {
    text-align: center;
    padding: 12px;
    vertical-align: middle;
}
.candidate-photo {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 50%;
    margin-right: 10px;
    border: 2px solid #00ff99;
}
.candidate-info {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.vote-bar-container {
    background-color: #444;
    border-radius: 8px;
    overflow: hidden;
    height: 18px;
    width: 100%;
    margin-top: 5px;
}
.vote-bar {
    height: 100%;
    background-color: #00ff99;
    text-align: right;
    padding-right: 5px;
    font-size: 0.8rem;
    color: #000;
    font-weight: bold;
    width: 0%;
    transition: width 2s ease-in-out;
}
.back-btn {
    text-align: center;
    margin-top: 20px;
}
.back-btn a {
    text-decoration: none;
    color: #1e1e1e;
    background-color: #00ff99;
    padding: 10px 25px;
    border-radius: 5px;
    font-weight: bold;
    transition: 0.2s;
}
.back-btn a:hover {
    background-color: #00cc77;
    color: #fff;
}
</style>
</head>
<body>
<div class="container">
    <h2>Election Results</h2>
    <div class="results-info">
        Votes Cast: <?= $total_votes_cast; ?> / <?= $total_voters; ?> voters
    </div>

    <?php while($pos = mysqli_fetch_assoc($positions_query)): ?>
        <h4><?= htmlspecialchars($pos['position_name']); ?></h4>
        <table class="table table-borderless table-striped">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Votes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $candidates_query = mysqli_query($conn, "
                    SELECT c.name AS candidate_name, c.photo AS candidate_photo,
                           (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.id) AS vote_count
                    FROM candidates c
                    WHERE c.position_id = '{$pos['id']}'
                    ORDER BY vote_count DESC
                ");
                while ($cand = mysqli_fetch_assoc($candidates_query)):
                    // Fix photo path
                    $photo = str_replace('candidate_photos/candidate_photos/', 'candidate_photos/', $cand['candidate_photo']);
                    $photo = $photo ?: 'candidate_photos/default.jpg';

                    $vote_percent = $total_votes_cast > 0 ? round(($cand['vote_count'] / $total_votes_cast) * 100) : 0;
                ?>
                <tr>
                    <td class="candidate-info">
                        <img src="<?= $photo; ?>" class="candidate-photo" alt="Candidate">
                        <?= htmlspecialchars($cand['candidate_name']); ?>
                        <div class="vote-bar-container">
                            <div class="vote-bar" data-percent="<?= $vote_percent; ?>%"><?= $vote_percent; ?>%</div>
                        </div>
                    </td>
                    <td><?= $cand['vote_count']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endwhile; ?>

    <div class="back-btn">
        <a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
</div>

<script>
// Animate vote bars on page load
document.addEventListener("DOMContentLoaded", function() {
    const bars = document.querySelectorAll('.vote-bar');
    bars.forEach(bar => {
        const percent = bar.getAttribute('data-percent');
        setTimeout(() => {
            bar.style.width = percent;
        }, 100);
    });
});
</script>
</body>
</html>
