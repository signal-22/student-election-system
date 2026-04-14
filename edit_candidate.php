<?php
session_start();
include 'db_connect.php';

// Only admin can access
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get candidate ID
if (!isset($_GET['id'])) {
    header("Location: view_candidates.php");
    exit();
}
$id = $_GET['id'];

// Fetch candidate details
$result = mysqli_query($conn, "SELECT * FROM candidates WHERE id='$id'");
$cand = mysqli_fetch_assoc($result);

// Fetch all positions
$positions_query = mysqli_query($conn, "SELECT * FROM positions ORDER BY position_name ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $position_id = $_POST['position_id'];

    // Photo upload
    $photo = $cand['photo']; // keep existing photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('cand_') . '.' . $ext;
        $target = "candidate_photos/" . $filename;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            $photo = $filename;
        }
    }

    // Update candidate
    $update = mysqli_query($conn, "UPDATE candidates SET name='$name', position_id='$position_id', photo='$photo' WHERE id='$id'");
    if ($update) {
        $message = "Candidate updated successfully!";
        $cand = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM candidates WHERE id='$id'")); // refresh data
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Candidate</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>.form-container{max-width:600px;margin:50px auto;}</style>
</head>
<body>
<div class="container form-container">
    <h2>Edit Candidate</h2>
    <?php if(isset($message)) echo "<div class='alert alert-info'>$message</div>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($cand['name']); ?>" required>
        </div>
        <div class="mb-3">
            <label>Position</label>
            <select name="position_id" class="form-control" required>
                <option value="">Select Position</option>
                <?php while($pos = mysqli_fetch_assoc($positions_query)): ?>
                    <option value="<?= $pos['id']; ?>" <?= $cand['position_id']==$pos['id']?'selected':''; ?>>
                        <?= htmlspecialchars($pos['position_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Photo (optional)</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
            <?php if($cand['photo']): ?>
                <img src="candidate_photos/<?= $cand['photo']; ?>" style="width:100px;margin-top:10px;">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-success">Update Candidate</button>
        <a href="view_candidates.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
