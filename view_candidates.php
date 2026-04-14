<?php
session_start();
include 'db_connect.php';

// Only admin can access
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';

// Delete candidate if requested
if (isset($_GET['delete']) && isset($_GET['csrf_token'])) {
    if ($_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Candidate deleted successfully!";
    } else {
        $message = "Error deleting candidate: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
}

// Fetch candidates securely
$stmt = $conn->prepare("
    SELECT c.id, c.name AS fullname, c.photo, p.position_name
    FROM candidates c
    LEFT JOIN positions p ON c.position_id = p.id
    ORDER BY p.position_name ASC, c.name ASC
");
$stmt->execute();
$candidates_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Candidates</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: #1e1e2f; 
    color: #f0f0f0;
    font-family: 'Fira Code', monospace;
}
.container {
    margin-top: 40px;
}
h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #00ffcc;
    text-shadow: 0 0 5px #00ffcc, 0 0 10px #00ffcc;
}
.table thead {
    background-color: #28283d;
}
.table tbody tr:hover {
    background-color: #38385c;
    transform: scale(1.02);
    transition: 0.2s;
}
.candidate-photo {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #00ffcc;
    box-shadow: 0 0 8px #00ffcc;
}
.btn-custom {
    border-radius: 8px;
    transition: 0.2s;
}
.btn-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,255,204,0.5);
}
.search-box {
    max-width: 300px;
    margin-bottom: 20px;
}
</style>
<script>
function filterCandidates() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const table = document.getElementById("candidatesTable");
    const trs = table.getElementsByTagName("tr");
    for (let i = 1; i < trs.length; i++) {
        const name = trs[i].getElementsByTagName("td")[1].innerText.toLowerCase();
        const position = trs[i].getElementsByTagName("td")[2].innerText.toLowerCase();
        trs[i].style.display = (name.includes(input) || position.includes(input)) ? "" : "none";
    }
}
</script>
</head>
<body>

<div class="container">
    <h2>All Candidates</h2>
    <?php if($message): ?>
        <div class='alert alert-success'><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mb-3 flex-wrap">
        <a href="add_candidate.php" class="btn btn-success btn-custom mb-2">Add New Candidate</a>
        <input type="text" id="searchInput" class="form-control search-box" onkeyup="filterCandidates()" placeholder="Search by name or position...">
    </div>

    <table class="table table-bordered table-hover text-center" id="candidatesTable">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Position</th>
                <th>Photo</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($cand = $candidates_result->fetch_assoc()): 
                $photo = str_replace('candidate_photos/candidate_photos/', 'candidate_photos/', $cand['photo']);
                $photo = $photo ?: 'candidate_photos/default.jpg';
            ?>
                <tr>
                    <td><?= $cand['id']; ?></td>
                    <td><?= htmlspecialchars($cand['fullname']); ?></td>
                    <td><?= htmlspecialchars($cand['position_name'] ?: 'N/A'); ?></td>
                    <td><img src="<?= $photo; ?>" class="candidate-photo" alt="Photo"></td>
                    <td>
                        <a href="edit_candidate.php?id=<?= $cand['id']; ?>" class="btn btn-warning btn-sm btn-custom">Edit</a>
                        <a href="view_candidates.php?delete=<?= $cand['id']; ?>&csrf_token=<?= $_SESSION['csrf_token']; ?>" 
                           class="btn btn-danger btn-sm btn-custom" 
                           onclick="return confirm('Are you sure you want to delete this candidate?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="admin_dashboard.php" class="btn btn-secondary btn-custom mt-3">Back to Dashboard</a>
</div>

</body>
</html>
