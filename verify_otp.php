<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['otp_voter_id'])) {
    header("Location: voter_login.php");
    exit();
}

$message = "";
$voter_id = $_SESSION['otp_voter_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp'] ?? '');

    $stmt = $conn->prepare("SELECT id FROM otp_tokens WHERE voter_id = ? AND otp_code = ? AND is_used = 0 ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("is", $voter_id, $entered_otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Invalidate OTP immediately
        $upd = $conn->prepare("UPDATE otp_tokens SET is_used = 1 WHERE id = ?");
        $upd->bind_param("i", $row['id']);
        $upd->execute();

        // Log in the voter
        session_regenerate_id(true);
        $_SESSION['voter_id'] = $voter_id;
        $_SESSION['voter_name'] = $_SESSION['otp_voter_name'];
        unset($_SESSION['otp_voter_id'], $_SESSION['otp_voter_name']);

        header("Location: voter_dashboard.php");
        exit();
    } else {
        $message = "Invalid or already used OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            height: 100vh;
            font-family: 'Fira Code', monospace;
            background: url('candidate_photos/election.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .card {
            position: relative;
            z-index: 1;
            background: rgba(250, 250, 250, 0.85);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(87, 122, 115, 0.4);
            width: 100%;
            max-width: 450px;
        }

        h3 {
            text-align: center;
            color: #c4c449ff;
            margin-bottom: 25px;
            font-style: italic;
            text-shadow: 0 0 5px #00ffcc;
        }

        .form-control {
            background: #1e1e3a;
            color: #f0f0f0;
            border: 1px solid #00ffcc;
            border-radius: 8px;
            font-size: 1.3rem;
            text-align: center;
            letter-spacing: 6px;
        }

        .form-control:focus {
            outline: none;
            border-color: #00ffcc;
            box-shadow: 0 0 8px #00ffcc;
        }

        .btn-primary {
            background-color: #00ffcc;
            color: #1e1e2f;
            font-weight: bold;
            border-radius: 8px;
            transition: 0.2s;
        }

        .btn-primary:hover {
            background-color: #00e6b8;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 255, 204, 0.6);
        }

        .alert-danger {
            background-color: #ff4d4d;
            color: #1e1e2f;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #00ffcc;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .hint {
            text-align: center;
            color: #555;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="card">
        <h3>Enter OTP</h3>
        <p class="hint">A 6-digit code has been sent to your registered email.</p>

        <?php if ($message): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>OTP Code</label>
                <input type="text" name="otp" class="form-control" maxlength="6" required autofocus>
            </div>
            <button class="btn btn-primary w-100">Verify</button>
        </form>
        <a href="voter_login.php" class="back-link">← Request a new OTP</a>
    </div>
</body>

</html>