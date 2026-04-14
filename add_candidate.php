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

// Fetch positions securely
$positions = [];
$pos_stmt = $conn->prepare("SELECT id, position_name FROM positions ORDER BY position_name ASC");
$pos_stmt->execute();
$pos_result = $pos_stmt->get_result();
while ($pos = $pos_result->fetch_assoc()) {
    $positions[] = $pos;
}
$pos_stmt->close();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    // Validate inputs
    $fullname = trim($_POST['fullname'] ?? '');
    $position_id = intval($_POST['position_id'] ?? 0);

    if (empty($fullname) || $position_id <= 0) {
        $message = "Please provide all required fields.";
    } else {
        // Handle photo upload
        $photo = 'candidate_photos/default.jpg';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($_FILES['photo']['tmp_name']);
            $max_size = 2 * 1024 * 1024; // 2MB

            if (!in_array($file_type, $allowed_types)) {
                $message = "Only JPG, PNG, and GIF images are allowed.";
            } elseif ($_FILES['photo']['size'] > $max_size) {
                $message = "File size must be under 2MB.";
            } else {
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('cand_') . '.' . $ext;
                $target = __DIR__ . "/candidate_photos/" . $filename;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                    $photo = "candidate_photos/" . $filename;
                } else {
                    $message = "Failed to upload photo.";
                }
            }
        }

        if (empty($message)) {
            // Insert candidate securely
            $stmt = $conn->prepare("INSERT INTO candidates (name, position_id, photo) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $fullname, $position_id, $photo);
            if ($stmt->execute()) {
                $message = "Candidate added successfully!";
            } else {
                $message = "Error adding candidate: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Candidate</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: #1e1e2f; 
    color: #f0f0f0;
    font-family: 'Fira Code', monospace;
}
.form-container {
    max-width: 600px;
    margin: 50px auto;
    background: #2d2d3d;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 0 15px rgba(0,255,204,0.3);
}
h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #00ffcc;
    text-shadow: 0 0 5px #00ffcc;
}
.form-control, .form-select {
    background: #1e1e3a;
    color: #f0f0f0;
    border: 1px solid #00ffcc;
    border-radius: 8px;
}
.form-control:focus, .form-select:focus {
    box-shadow: 0 0 8px #00ffcc;
    border-color: #00ffcc;
    outline: none;
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

<div class="container form-container">
    <h2>Add New Candidate</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="fullname" class="form-control" required value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
        </div>
        <div class="mb-3">
            <label>Position</label>
            <select name="position_id" class="form-select" required>
                <option value="">Select Position</option>
                <?php foreach ($positions as $pos): ?>
                    <option value="<?= $pos['id']; ?>" <?= (isset($_POST['position_id']) && $_POST['position_id'] == $pos['id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($pos['position_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Photo (optional)</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
        </div>
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-custom">Add Candidate</button>
            <a href="view_candidates.php" class="btn btn-secondary btn-custom">Back to Candidates</a>
        </div>
    </form>
</div>

</body>
</html>
