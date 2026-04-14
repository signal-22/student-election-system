<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();
include 'db_connect.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_number = trim($_POST['reg_number'] ?? '');

    if ($reg_number) {
        $stmt = $conn->prepare("SELECT id, fullname, email FROM voters WHERE reg_number = ?");
        $stmt->bind_param("s", $reg_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $voter = $result->fetch_assoc();

            // Generate 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Invalidate any previous unused OTPs for this voter
            $del = $conn->prepare("UPDATE otp_tokens SET is_used = 1 WHERE voter_id = ? AND is_used = 0");
            $del->bind_param("i", $voter['id']);
            $del->execute();

            // Store new OTP
            $ins = $conn->prepare("INSERT INTO otp_tokens (voter_id, otp_code) VALUES (?, ?)");
            $ins->bind_param("is", $voter['id'], $otp);
            $ins->execute();

            // Send OTP via email using PHPMailer
            require 'PHPMailer/PHPMailer.php';
            require 'PHPMailer/SMTP.php';
            require 'PHPMailer/Exception.php';


            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'kcaevoting@gmail.com'; // your school email
                $mail->Password = 'YOUR_APP_PASSWORD_HERE';    // paste your password here
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('kcaevoting@gmail.com', 'KCA E-Voting System');
                $mail->addAddress($voter['email'], $voter['fullname']);

                $mail->Subject = 'Your Voting Login OTP';
                $mail->Body = "Hello {$voter['fullname']},\n\nYour one-time login code is: $otp\n\nDo not share this code with anyone.\n\nKCA University E-Voting System";

                $mail->send();

                // Save voter id in session and redirect to OTP page
                $_SESSION['otp_voter_id'] = $voter['id'];
                $_SESSION['otp_voter_name'] = $voter['fullname'];
                header("Location: verify_otp.php");
                exit();

            } catch (Exception $e) {
                $message = "Could not send OTP. Error: " . $mail->ErrorInfo;
            }

        } else {
            $message = "Registration number not found.";
        }
        $stmt->close();
    } else {
        $message = "Please enter your registration number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Login</title>
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
    </style>
</head>

<body>
    <div class="card">
        <h3>Voter Login</h3>

        <?php if ($message): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Registration Number</label>
                <input type="text" name="reg_number" class="form-control" required
                    value="<?= isset($_POST['reg_number']) ? htmlspecialchars($_POST['reg_number']) : ''; ?>">
            </div>
            <button class="btn btn-primary w-100">Send OTP</button>
        </form>
    </div>
</body>

</html>