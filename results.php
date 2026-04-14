<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch voter by email
    $query = mysqli_query($conn, "SELECT * FROM voters WHERE email='$email'");

    if (mysqli_num_rows($query) == 1) {
        $voter = mysqli_fetch_assoc($query);

        // Prevent login if voter has already voted
        if ($voter['has_voted'] == 1) {
            $error = "You have already voted. Login not allowed!";
        } 
        // Check password if they haven't voted
        else if (password_verify($password, $voter['password'])) {
            $_SESSION['voter_id']   = $voter['id'];
            $_SESSION['voter_name'] = $voter['fullname'];
            $_SESSION['voter_reg']  = $voter['reg_number'];

            header("Location: voter_dashboard.php");
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "Voter not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Voter Login</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background: white;
            padding: 30px;
            width: 350px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>

</head>
<body>

<div class="login-box">
    <h2>Voter Login</h2>

    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Enter Email" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit">Login</button>
    </form>
</div>

</body>
</html>
