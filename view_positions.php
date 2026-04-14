<?php
session_start();
include 'db_connect.php';

// Only admin can access
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Delete position if requested
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM positions WHERE id='$id'");
    $message = "Position deleted successfully!";
}

// Fetch all positions
$positions_query = mysqli_query($conn, "SELECT * FROM positions ORDER BY position_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Positions</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>All Positions</h2>
    <?php if(isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>
    
    <a href="add_position.php" class="btn btn-primary mb-3">Add New Position</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Position Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($pos = mysqli_fetch_assoc($positions_query)) { ?>
                <tr>
                    <td><?= $pos['id']; ?></td>
                    <td><?= htmlspecialchars($pos['position_name']); ?></td>
                    <td>
                        <a href="edit_position.php?id=<?= $pos['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="view_positions.php?delete=<?= $pos['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this position?')">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>
</body>
</html>
