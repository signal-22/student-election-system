<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Election System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #1e1e2f;
        background-image: radial-gradient(circle at top left, #2a2a3f, #1e1e2f);
        color: #f0f0f0;
        font-family: 'Fira Code', monospace;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        min-height: 100vh;
        overflow: hidden;
    }

    @keyframes codeLines {
        0% { background-position: 0 0; }
        100% { background-position: -200px 0; }
    }

    body::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 200%;
        height: 100%;
        background-image: linear-gradient(to right, rgba(255,255,255,0.05) 1px, transparent 1px);
        background-size: 20px 100%;
        animation: codeLines 15s linear infinite;
        pointer-events: none;
        z-index: 0;
    }

    h1 {
        z-index: 1;
        font-size: 3rem;
        margin-bottom: 50px;
        text-shadow: 0 0 10px #28a745;
        color: #28a745;
        border-right: 3px solid #28a745;
        white-space: nowrap;
        overflow: hidden;
        width: 0;
        animation: typing 3s steps(35, end) forwards, blink-caret 0.75s step-end infinite;
    }

    @keyframes typing {
        from { width: 0; }
        to { width: 22ch; }
    }

    @keyframes blink-caret {
        from, to { border-color: transparent; }
        50% { border-color: #28a745; }
    }

    .btn-custom {
        width: 260px;
        margin: 10px;
        font-size: 1.2rem;
        font-weight: bold;
        padding: 14px;
        border-radius: 10px;
        transition: all 0.3s ease;
        z-index: 1;
        position: relative;
        opacity: 0; /* Initially hidden */
    }

    .btn-custom.show {
        opacity: 1;
        transform: translateY(0);
    }

    .btn-custom:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(40, 167, 69, 0.6);
    }

    .btn-voter { background-color: #28a745; border: none; color: #fff; }
    .btn-admin { background-color: #007bff; border: none; color: #fff; }
    .btn-results { background-color: #ffc107; border: none; color: #1e1e2f; }
    .btn-charts { background-color: #17a2b8; border: none; color: #fff; }

    .container-main {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
</style>
</head>
<body>

<div class="container-main text-center">
    <h1>Student Election System</h1>

    <a href="voter_login.php" class="btn btn-voter btn-custom">Voter Login</a>
    <a href="login.php" class="btn btn-admin btn-custom">Admin Login</a>
    <a href="results_table.php" class="btn btn-results btn-custom">View Results (Table)</a>
    <a href="results_graph.php" class="btn btn-charts btn-custom">View Results (Charts)</a>
</div>

<script>
    // Sequentially reveal buttons like code execution
    const buttons = document.querySelectorAll('.btn-custom');
    buttons.forEach((btn, index) => {
        setTimeout(() => {
            btn.classList.add('show');
        }, 3500 + index * 500); // start after heading typing + stagger
    });
</script>

</body>
</html>
