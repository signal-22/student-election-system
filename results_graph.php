<?php
session_start();
include 'db_connect.php';

// Only admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Results Graph</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Fira Code', monospace; background: #1e1e2f; color: #fff; text-align: center; padding: 20px; }
        h2 { color: #00ffcc; margin-bottom: 20px; }
        .chart-wrapper { margin: 40px auto; max-width: 800px; background: #2d2d3d; padding: 20px; border-radius: 15px; }
        .candidate-card { display: inline-block; margin: 10px; padding: 10px; background: #38385c; border-radius: 10px; width: 140px; text-align: center; }
        .candidate-card img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #00ffcc; }
        .candidate-card p { margin: 5px 0; }
        .candidate-card p.position { font-style: italic; font-size: 0.9em; color: #ddd; }
    </style>
</head>
<body>
    <h2>Live Election Results</h2>
    <div id="chartsContainer"></div>

<script>
async function fetchData() {
    try {
        const res = await fetch('fetch_votes.php');
        const data = await res.json();

        // Group candidates by position
        const positions = {};
        data.forEach(c => {
            if (!positions[c.position]) positions[c.position] = [];
            positions[c.position].push(c);
        });

        const container = document.getElementById('chartsContainer');
        container.innerHTML = ''; // Clear existing charts

        for (const [position, candidates] of Object.entries(positions)) {
            // Create wrapper
            const wrapper = document.createElement('div');
            wrapper.className = 'chart-wrapper';

            const title = document.createElement('h3');
            title.textContent = position;
            wrapper.appendChild(title);

            // Canvas for chart
            const canvas = document.createElement('canvas');
            wrapper.appendChild(canvas);

            // Candidate cards
            const photosDiv = document.createElement('div');
            photosDiv.style.marginTop = '20px';
            candidates.forEach(c => {
                const card = document.createElement('div');
                card.className = 'candidate-card';
                card.innerHTML = `
                    <img src="${c.photo}" alt="${c.name}">
                    <p><strong>${c.name}</strong></p>
                    <p class="position">${c.position}</p>
                    <p>${c.votes} votes</p>
                `;
                photosDiv.appendChild(card);
            });
            wrapper.appendChild(photosDiv);

            container.appendChild(wrapper);

            // Pie chart
            const ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: candidates.map(c => c.name),
                    datasets: [{
                        data: candidates.map(c => c.votes),
                        backgroundColor: candidates.map((_, i) => `hsl(${i*360/candidates.length}, 70%, 50%)`),
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'right' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const idx = context.dataIndex;
                                    const votes = candidates[idx].votes;
                                    return context.label + ': ' + votes + ' votes';
                                }
                            }
                        }
                    }
                }
            });
        }

    } catch (err) {
        console.error("Error fetching data:", err);
    }
}

fetchData();
setInterval(fetchData, 5000); // refresh every 5 sec
</script>
</body>
</html>
