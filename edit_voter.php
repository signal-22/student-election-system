<?php
session_start();
include 'db_connect.php';

// Admin only
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get voter ID
if (!isset($_GET['id'])) {
    header("Location: voters_list.php");
    exit();
}
$id = $_GET['id'];

// Fetch voter details
$result = mysqli_query($conn, "SELECT * FROM voters WHERE id='$id'");
$voter = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $reg_number = mysqli_real_escape_string($conn, $_POST['reg_number']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);

    $update = mysqli_query($conn, "UPDATE voters SET fullname='$fullname', reg_number='$reg_number', course='$course', year='$year' WHERE id='$id'");
    if ($update) {
        $message = "Voter updated successfully!";
        $voter = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM voters WHERE id='$id'")); // refresh data
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Voter</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>.form-container{max-width:600px;margin:50px auto;}</style>
</head>
<body>
<div class="container form-container">
    <h2>Edit Voter</h2>
    <?php if(isset($message)) echo "<div class='alert alert-info'>$message</div>"; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($voter['fullname']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Registration Number</label>
            <input type="text" name="reg_number" class="form-control" value="<?= htmlspecialchars($voter['reg_number']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Course</label>
            <input type="text" name="course" class="form-control" value="<?= htmlspecialchars($voter['course']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Year</label>
            <input type="text" name="year" class="form-control" value="<?= htmlspecialchars($voter['year']); ?>" required>
        </div>
        <button type="submit" class="btn btn-success">Update Voter</button>
        <a href="voters_list.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
