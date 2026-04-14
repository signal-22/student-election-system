<?php
include 'db_connect.php';

// Fetch candidates with correct position names and vote counts
$query = "
    SELECT 
        c.id,
        c.name AS name,
        c.photo AS photo,
        COALESCE(p.position_name, 'Unknown') AS position,
        (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.id) AS votes
    FROM candidates c
    LEFT JOIN positions p ON c.position_id = p.id
    ORDER BY p.position_name ASC, votes DESC, c.name ASC
";

$result = mysqli_query($conn, $query);
$candidates = [];

while ($row = mysqli_fetch_assoc($result)) {
    $candidates[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'photo' => $row['photo'] ?: 'candidate_photos/default.jpg', // keep your existing photo paths
        'position' => $row['position'],
        'votes' => (int)$row['votes']
    ];
}

header('Content-Type: application/json');
echo json_encode($candidates);
