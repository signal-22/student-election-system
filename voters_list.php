<?php
session_start();
include 'db_connect.php';

// Only admin can access
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Delete voter
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM voters WHERE id='$id'");
    $message = "Voter deleted successfully!";
}

// Reset voter vote status
if (isset($_GET['reset'])) {
    $id = $_GET['reset'];
    mysqli_query($conn, "UPDATE voters SET has_voted=0 WHERE id='$id'");
    $message = "Voter voting status reset successfully!";
}

// Fetch all voters
$voters_query = mysqli_query($conn, "SELECT * FROM voters ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Voters</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: #1e1e2f;
    color: #f0f0f0;
    font-family: 'Fira Code', monospace;
}
.container {
    margin-top: 50px;
}
h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #00ffcc;
    text-shadow: 0 0 5px #00ffcc;
}
.table {
    background: #2d2d3d;
    border-radius: 10px;
    overflow: hidden;
}
.table th {
    background: #00ffcc;
    color: #1e1e2f;
}
.table td, .table th {
    vertical-align: middle;
    text-align: center;
}
.status-yes { 
    color: #28a745; 
    font-weight: bold; 
}
.status-no { 
    color: #ff4d4d; 
    font-weight: bold; 
}
.btn-custom {
    background-color: #00ffcc;
    color: #1e1e2f;
    font-weight: bold;
    border-radius: 8px;
    transition: 0.2s;
}
.btn-custom:hover {
    background-color: #00e6b8;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,255,204,0.6);
}
.alert-info {
    background-color: #00ffcc;
    color: #1e1e2f;
    border-radius: 8px;
    text-align: center;
    font-weight: bold;
}
</style>
</head>
<body>
<div class="container">
    <h2>All Registered Voters</h2>
    <?php if(isset($message)) echo "<div class='alert alert-info'>$message</div>"; ?>
    <a href="admin_dashboard.php" class="btn btn-custom mb-3">Back to Dashboard</a>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Reg Number</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Has Voted?</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($voter = mysqli_fetch_assoc($voters_query)) { ?>
                    <tr>
                        <td><?= $voter['id']; ?></td>
                        <td><?= htmlspecialchars($voter['fullname']); ?></td>
                        <td><?= htmlspecialchars($voter['reg_number']); ?></td>
                        <td><?= htmlspecialchars($voter['course']); ?></td>
                        <td><?= htmlspecialchars($voter['year']); ?></td>
                        <td>
                            <?php if($voter['has_voted']) { ?>
                                <span class="status-yes">Yes</span>
                            <?php } else { ?>
                                <span class="status-no">No</span>
                            <?php } ?>
                        </td>
                        <td>
                            <a href="edit_voter.php?id=<?= $voter['id']; ?>" class="btn btn-sm btn-warning btn-custom">Edit</a>
                            <a href="voters_list.php?reset=<?= $voter['id']; ?>" class="btn btn-sm btn-info btn-custom" onclick="return confirm('Reset voting status?')">Reset Vote</a>
                            <a href="voters_list.php?delete=<?= $voter['id']; ?>" class="btn btn-sm btn-danger btn-custom" onclick="return confirm('Delete voter?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
