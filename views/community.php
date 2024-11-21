<?php
session_start();

// Check session
if (!isset($_SESSION['user'])) {
    header('Location: access_denied.php');
    exit();
}

// Get community ID
$communityId = $_GET['id'] ?? null;

if (!$communityId) {
    header('Location: search_filter.php');
    exit();
}

// Fetch data via AJAX
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Details</title>
    <style>
        /* Add styles for community layout */
    </style>
</head>
<body>
    <div id="communityDetails"></div>
    <button onclick="window.history.back()">Back</button>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            fetch(`http://testoztdal.local/api/communities/read.php?id=${<?= json_encode($communityId) ?>}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const details = data.data;
                        document.getElementById("communityDetails").innerHTML = `
                            <h1>${details.community_name}</h1>
                            <p>Eze: ${details.eze_name}</p>
                            <p>Chairman Email: ${details.chair_email}</p>
                            <p>Chairman Phone: ${details.chair_phone}</p>
                            <p>Secretary Phone: ${details.secretary_phone}</p>
                            <h2>Members</h2>
                            <ul>
                                ${details.members.map(member => `
                                    <li>${member.firstname} ${member.lastname} - ${member.phone} (${member.gender})</li>
                                `).join('')}
                            </ul>
                            <p>Local Government: ${details.local_govt}</p>
                            <p>Constituency: ${details.constituency}</p>
                            <p>Joined: ${details.created_at}</p>
                        `;
                    } else {
                        document.getElementById("communityDetails").innerHTML = `<p>Error loading community details.</p>`;
                    }
                });
        });
    </script>
</body>
</html>
