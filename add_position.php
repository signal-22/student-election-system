<?php
session_start();
include 'db_connect.php';

// Only admin can access
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $position_name = mysqli_real_escape_string($conn, $_POST['position_name']);

    // Insert into positions table
    $insert = mysqli_query($conn, "INSERT INTO positions (position_name) VALUES ('$position_name')");

    if ($insert) {
        $message = "Position added successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Position</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .form-container { max-width: 500px; margin: 50px auto; }
</style>
</head>
<body>
<div class="container form-container">
    <h2>Add New Position</h2>
    <?php if(isset($message)) echo "<div class='alert alert-info'>$message</div>"; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Position Name</label>
            <input type="text" name="position_name" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Add Position</button>
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </form>
</div>
</body>
</html>
