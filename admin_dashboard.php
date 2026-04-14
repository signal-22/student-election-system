<?php
session_start();
include 'db_connect.php';

// Only admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Statistics
$total_voters_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM voters");
$total_voters = mysqli_fetch_assoc($total_voters_query)['total'];

$total_votes_query = mysqli_query($conn, "SELECT COUNT(DISTINCT voter_id) AS total_cast FROM votes");
$total_votes_cast = mysqli_fetch_assoc($total_votes_query)['total_cast'];

$remaining_voters = $total_voters - $total_votes_cast;
$admin_name = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Student Election</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;700&display=swap" rel="stylesheet">

    <style>
        /* General body */

        body {
            background: linear-gradient(135deg, #fdf6e3 25%, #cfe2f3 25%, #cfe2f3 50%, #fdf6e3 50%, #fdf6e3 75%, #cfe2f3 75%, #cfe2f3 100%);
            background-size: 100px 100px;
            /* larger squares for a softer pattern */
            font-family: 'Fira Code', monospace;
            color: #1c1c1c;
            /* dark text for contrast */
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100%;
            background: #0d1117;
            box-shadow: 2px 0 20px rgba(0, 255, 200, 0.2);
            padding-top: 30px;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            text-align: center;
            color: #0ff;
            margin-bottom: 30px;
            font-size: 1.5rem;
        }

        .sidebar a {
            text-decoration: none;
            color: #f0f0f0;
            padding: 15px 20px;
            margin: 5px 0;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .sidebar a:hover {
            color: #0ff;
            border-left: 3px solid #0ff;
            background: rgba(0, 255, 200, 0.1);
        }

        /* Main content */
        .main {
            margin-left: 240px;
            padding: 30px;
        }

        /* Stats & Menu cards with subtle overlay */
        .stats-card,
        .menu-card {
            background-color: rgba(31, 31, 31, 0.9);
            /* more opaque for readability */
            border-left: 5px solid rgba(14, 14, 14, 1);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 0 15px rgba(0, 255, 200, 0.15);
            /* softer shadow */
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stats-card:hover,
        .menu-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 255, 200, 0.25);
        }

        .stats-card h5 {
            color: #0ff;
            font-size: 1.1rem;
        }

        .stats-card p {
            font-size: 2rem;
            font-weight: bold;
            color: #0ff;
        }

        .menu-card {
            text-align: center;
            cursor: pointer;
        }

        .menu-card a {
            text-decoration: none;
            color: #f0f0f0;
            font-weight: bold;
            font-size: 1.2rem;


        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="add_candidate.php">Add Candidate</a>
        <a href="view_candidates.php">View Candidates</a>
        <a href="add_position.php">Add Position</a>
        <a href="view_positions.php">View Positions</a>
        <a href="voters_list.php">View Voters</a>
        <a href="results_table.php">Results (Table)</a>
        <a href="results_graph.php">Results (Charts)</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main container">
        <h1 class="mb-4 welcome-text">Welcome,
            <?= htmlspecialchars($admin_name); ?>!
        </h1>

        <!-- Stats -->
        <div class="row">
            <div class="col-md-4">
                <div class="stats-card">
                    <h5>Total Registered Voters</h5>
                    <p class="counter" data-target="<?= $total_voters; ?>">0</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h5>Total Votes Cast</h5>
                    <p class="counter" data-target="<?= $total_votes_cast; ?>">0</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h5>Voters Remaining</h5>
                    <p class="counter" data-target="<?= $remaining_voters; ?>">0</p>
                </div>
            </div>
        </div>

        <!-- Menu Grid -->
        <div class="row g-4 mt-3">
            <div class="col-md-3">
                <div class="menu-card bg-dark"><a href="add_candidate.php">Add Candidate</a></div>
            </div>
            <div class="col-md-3">
                <div class="menu-card bg-dark"><a href="view_candidates.php">View Candidates</a></div>
            </div>
            <div class="col-md-3">
                <div class="menu-card bg-dark"><a href="add_position.php">Add Position</a></div>
            </div>
            <div class="col-md-3">
                <div class="menu-card bg-dark"><a href="view_positions.php">View Positions</a></div>
            </div>
            <div class="col-md-3">
                <div class="menu-card bg-dark"><a href="voters_list.php">View Voters</a></div>
            </div>
            <div class="col-md-3">
                <div class="menu-card bg-dark"><a href="results_table.php">Results (Table)</a></div>
            </div>
            <div class="col-md-3">
                <div class="menu-card bg-dark"><a href="results_graph.php">Results (Charts)</a></div>
            </div>
            <div class="col-md-3">
                <div class="menu-card bg-dark"><a href="logout.php">Logout</a></div>
            </div>
        </div>
    </div>

    <!-- Cursor Trail -->
    <div class="cursor-trail" id="cursor"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animated counters
        document.addEventListener("DOMContentLoaded", () => {
            const counters = document.querySelectorAll('.counter');
            counters.forEach(counter => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-target');
                    const count = +counter.innerText;
                    const inc = Math.ceil(target / 50);
                    if (count < target) {
                        counter.innerText = count + inc;
                        setTimeout(updateCount, 20);
                    } else {
                        counter.innerText = target;
                    }
                };
                updateCount();
            });
        });

        // Cursor trailing effect
        const cursor = document.getElementById('cursor');
        document.addEventListener('mousemove', e => {
            cursor.style.transform = `translate(${e.clientX}px, ${e.clientY}px)`;
        });
    </script>

</body>

</html>