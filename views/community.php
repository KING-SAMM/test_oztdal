<?php
session_start();

// Check session
if (!isset($_SESSION['user'])) {
    header('Location: http://testoztdal.local/views/access_denied.php');
    exit();
}

// Get community ID
$communityId = $_GET['id'] ?? null;

if (!$communityId) {
    header('Location: http://testoztdal.local/views/search_filter.php');
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
    <link rel="stylesheet" href="http://testoztdal.local/assets/css/filter.css" />
    <link rel="stylesheet" href="http://testoztdal.local/assets/css/nav.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .main {
            width: 100%;
        }

        .main #community-details {
            width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        .main #community-details h1 {
            font-size: 2em;
            margin-bottom: 10px;
            color: #333;
        }
        .main #community-details h2 {
            margin-top: 12px;
            margin-bottom:8px;
        }
        
        .main #community-details p {
            font-size: 1em;
            margin: 5px 0;
            color: #555;
        }
        .main #community-details ol {
            padding-left: 30px;
            margin-bottom:12px;
        }

        button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background: rgba(0, 123, 255, 1);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1em;
        }

        button:hover {
            background: rgba(0, 90, 245, 1);;
        }

    </style>
    <script defer>
        document.addEventListener('DOMContentLoaded', () => {
            const communityDetails = document.getElementById("community-details");
            printButton.addEventListener('click', () => {
                const newWindow = window.open('', '', 'width=800, height=600');
                newWindow.document.write(`<html><body>${communityDetails.outerHTML}</body></html>`);
                newWindow.document.close();
                newWindow.print();
            });
        });
    </script>
</head>
<body>
    <div class="login_out">
        <p class="welcome">Welcome, <?= htmlspecialchars($_SESSION['user']); ?></p>
        <a class="logout" href="?logout=true">Logout</a>
    </div>
    <nav class="navbar" style="display:flex;justify-content:flex-start;">
        <div class="logo">
            <img src="http://testoztdal.local/assets/img/oztdal_logo-trans.png" alt="Logo">
        </div>
        <ul class="nav-links" style="display:flex;justify-content:flex-start; margin-left: 100px;">
            <li><a href="https://oztdal.com.ng">Home</a></li>
            <li><a href="#">About</a></li>
            <!--<li><a href="#">Meetings</a></li>-->
            <li><a href="http://testoztdal.local/views/register.php">Registration & Membership</a></li>
            <!--<li><a href="#">Events</a></li>-->
            <li><a href="https://oztdal.com.ng/payment-dues">Payments & Dues</a></li>
            <!--<li><a href="#">Contact</a></li>-->
        </ul>
    </nav>
    <main class="main">
        <div class="formContainer">
            <div id="printButton" title="print">
                <img src="http://testoztdal.local/assets/img/printer.png" />
            </div>
        </div>
        <div id="community-details"></div>
        <button onclick="window.history.back()">Back</button>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            fetch(`http://testoztdal.local/api/communities/read.php?id=${<?= json_encode($communityId) ?>}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const details = data.data;
                        document.getElementById("community-details").innerHTML = `
                            <h1>${details.community_name} Community</h1>
                            <p><strong>Eze:</strong> ${details.eze_name}</p>
                            <p><strong>Chairman Email:</strong> ${details.chair_email}</p>
                            <p><strong>Chairman Phone:</strong> ${details.chair_phone}</p>
                            <p><strong>Secretary Phone:</strong> ${details.secretary_phone}</p>
                            <h2>Members</h2>
                            <ol>
                                ${details.members.map(member => `
                                    <li>${member.firstname} ${member.lastname} - ${member.phone} (${member.gender})</li>
                                `).join('')}
                            </ol>
                            <p><strong>Local Government:</strong> ${details.local_govt}</p>
                            <p><strong>Constituency:</strong> ${details.constituency}</p>
                            <p><strong>Joined:</strong> ${details.created_at}</p>
                        `;
                    } else {
                        document.getElementById("community-details").innerHTML = `<p>Error loading community details.</p>`;
                    }
                });
        });
    </script>
</body>
</html>
