<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: http://testoztdal.local/views/access_denied.php');
    exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
    die('Invalid member ID');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Details</title>
    <!-- <link rel="stylesheet" href="styles/member.css"> -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const id = <?= json_encode($id) ?>;
            fetch(`http://testoztdal.local/api/members/read.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const member = data.data;
                        document.getElementById('member-details').innerHTML = `
                            <h1>${member.firstname} ${member.lastname}</h1>
                            <p><strong>Phone:</strong> ${member.phone}</p>
                            <p><strong>Gender:</strong> ${member.gender}</p>
                            <p><strong>Community:</strong> ${member.community_name}</p>
                            <p><strong>Community Eze:</strong> ${member.community_eze}</p>
                            <p><strong>Constituency:</strong> ${member.constituency}</p>
                            <p><strong>Local Government:</strong> ${member.local_government}</p>
                            <p><strong>Date Joined:</strong> ${member.created_at}</p>
                        `;
                    } else {
                        document.getElementById('member-details').textContent = 'Error loading member details.';
                    }
                })
                .catch(() => {
                    document.getElementById('member-details').textContent = 'Something went wrong....';
                });
        });
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        #member-details {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        #member-details h1 {
            font-size: 2em;
            margin-bottom: 10px;
            color: #333;
        }

        #member-details p {
            font-size: 1em;
            margin: 5px 0;
            color: #555;
        }

        button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }

        button:hover {
            background: #45a049;
        }

    </style>
</head>
<body>
    <div id="member-details">Loading...</div>
    <button onclick="window.history.back()">Back</button>
</body>
</html>
