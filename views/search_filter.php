<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: http://testoztdal.local/views/access_denied.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: http://testoztdal.local/auth/access.php');
    exit;
}




// SEARCH
// Include required files
require_once __DIR__ . DIRECTORY_SEPARATOR . '../api/classes/Database.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '../api/classes/models/CommunityRep.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . '../api/classes/models/Community.php';

// Handle the search query
$searchQuery = $_GET['q'] ?? '';

$db = (new Database())->connect();

$communityModel = new Community($db);
$repModel = new CommunityRep($db);

// Fetch matching results
$communities = $communityModel->searchCommunities($searchQuery);
$members = $repModel->searchMembers($searchQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search and Filter Members</title>
    <link rel="stylesheet" href="http://testoztdal.local/assets/css/filter.css" />
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const resultsTable = document.getElementById('resultsTable');
            const filterForm = document.getElementById('filterForm');
            const fetchAllButton = document.getElementById('fetchAll');
            const printButton = document.getElementById('printButton');
            const loader = document.getElementById('loader');
            let isFetching = false; // Flag to prevent concurrent requests

            // Function to show the loader
            function showLoader() {
                loader.style.display = 'block';
            }

            // Function to hide the loader
            function hideLoader() {
                loader.style.display = 'none';
            }

            // Function to handle API calls
            function fetchData(endpoint, params, callback) {
                if (isFetching) return; // Prevent concurrent requests
                isFetching = true; // Set flag to true
                showLoader();

                fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(params),
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.status === 'success') {
                            callback(data.data);
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch((error) => console.error('Error:', error))
                    .finally(() => {
                        isFetching = false; // Reset flag
                        hideLoader();
                    });
            }

            // Handle Fetch All Members
            fetchAllButton.addEventListener('click', () => {
                const params = { action: 'fetch_all' };
                fetchData('http://testoztdal.local/api/filters/filters.php', params, displayResults);
            });

            // Handle Filter Form Submission
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(filterForm);
                const params = { action: 'filter' };

                formData.forEach((value, key) => {
                    params[key] = value;
                });

                fetchData('http://testoztdal.local/api/filters/filters.php', params, displayCommunityResults);
            });

            // Display Results for Fetch All
            function displayResults(data) {
                resultsTable.innerHTML = `
                    <tr class="tableHeader" style="border-bottom: 2px solid #CCC;">
                        <th>Name</th>
                        <th>Community</th>
                        <th>Local Govt</th>
                        <th>Constituency</th>
                    </tr>
                `;
                data.forEach((item) => {
                    resultsTable.innerHTML += `
                        <tr style="border-bottom: 2px solid blue;">
                            <td style="border-bottom: 1px solid blue; padding: 2px 20px">${item.firstname || ''} ${item.lastname || ''}</td>
                            <td style="border-bottom: 1px solid blue; padding: 2px 20px">${item.community_name || ''}</td>
                            <td style="border-bottom: 1px solid blue; padding: 2px 20px">${item.local_govt || ''}</td>
                            <td style="border-bottom: 1px solid blue; padding: 2px 20px">${item.constituency || ''}</td>
                        </tr>
                    `;
                });
            }

            // Display Results for Filter
            function displayCommunityResults(data) {
                resultsTable.innerHTML = '';
                data.forEach((community) => {
                    resultsTable.innerHTML += `
                        <tr class="tableHeader" style="border-top: 2px solid blue; padding-top:10px; font-style: italic;">
                            <th colspan="4" style="text-align: left;">Community</th>
                        </tr>
                        <tr style="border-bottom: 2px solid blue;">
                            <td colspan="4" style="border-bottom: 1px solid blue; padding: 2px 20px">${community.community_name || 'N/A'}</td>
                        </tr>
                        <tr class="tableHeader" style="font-style: italic;">
                            <th colspan="4" style="text-align: left;">Eze</th>
                        </tr>
                        <tr style="border-bottom: 2px solid blue;">
                            <td colspan="4" style="border-bottom: 1px solid blue; padding: 2px 20px">${community.eze_name || 'N/A'}</td>
                        </tr>
                        <tr class="tableHeader" style="font-style: italic;">
                            <th colspan="2" style="text-align: left;">Chairman / President</th>
                            <th colspan="2" style="text-align: left;">Secretary</th>
                        </tr>
                        <tr>
                            <td colspan="2" style="border-bottom: 1px solid blue; padding: 2px 20px">${community.chair_phone || 'N/A'}</td>
                            <td colspan="2" style="border-bottom: 1px solid blue; padding: 2px 20px">${community.secretary_phone || 'N/A'}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid blue; padding: 2px 20px">
                            <td style="border-bottom: 1px solid blue;">${community.chair_email || 'N/A'}</td>
                            <td colspan="3" style="border-bottom: 1px solid blue;"></td>
                        </tr>
                        <tr class="tableHeader" style="font-style: italic;">
                            <th colspan="4" style="text-align: left;">Members</th>
                        </tr>
                    `;

                    community.members.forEach((member) => {
                        resultsTable.innerHTML += `
                            <tr style="border-bottom: 2px solid blue;">
                                <td style="border-bottom: 1px solid blue; padding: 2px 20px">${member.firstname || 'N/A'}</td>
                                <td style="border-bottom: 1px solid blue; padding: 2px 20px">${member.lastname || 'N/A'}</td>
                                <td style="border-bottom: 1px solid blue; padding: 2px 20px">${member.phone || 'N/A'}</td>
                                <td style="border-bottom: 1px solid blue; padding: 2px 20px">${member.gender || 'N/A'}</td>
                            </tr>
                        `;
                    });
                });
            }

            // Print Results
            printButton.addEventListener('click', () => {
                const newWindow = window.open('', '', 'width=800, height=600');
                newWindow.document.write(`<html><body>${resultsTable.outerHTML}</body></html>`);
                newWindow.document.close();
                newWindow.print();
            });
        });

    </script>

    
</head>
<body>
    <button id="backToTop">↑ Back to Top</button>
    <div class="login_out">
        <p class="welcome">Welcome, <?= htmlspecialchars($_SESSION['user']); ?></p>
        <a class="logout" href="?logout=true">Logout</a>
    </div>
    <header>
        <h1>Retrieve and Filter Members</h1>
    </header>
    <main class="main">
        <div class="formContainer">
            <form id="filterForm">
                <button id="fetchAll" title="Fetch all members">Fetch All Members</button>
                <label for="filterType">Filter By:</label>
                <select id="filterType" name="filterType">
                    <option value="">Select filter type</option>
                    <option value="community">Community</option>
                    <option value="local_government">Local Government</option>
                    <option value="constituency">Constituency</option>
                    <option value="eze">Eze</option>
                    <option value="members">Members</option>
                    <option value="all">All</option>
                </select>

                <label for="order">Order:</label>
                <select id="order" name="order">
                    <option value="ASC">Ascending</option>
                    <option value="DESC">Descending</option>
                </select>

                <label for="orderBy">Order By:</label>
                <select id="orderBy" name="orderBy">
                    <option value="firstname">First Name</option>
                    <option value="created_at">Date Created</option>
                </select>

                <button type="submit" title="Filter results">Filter</button>
            </form>
            <div id="printButton" title="print">
                <img src="../assets/img/printer.png" />
                <!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 9V2h12v7" />
                <rect x="6" y="13" width="12" height="8" />
                <path d="M6 13H4a2 2 0 0 1-2-2v-1a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v1a2 2 0 0 1-2 2h-2" />
                <circle cx="18" cy="15" r="1" />
                </svg> -->

            </div>
        </div>

        <!-- BEGIN SEARCH -->
        <form method="get" action="search_filter.php">
            <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search by name or community">
            <button type="submit">Search</button>
        </form>

        <div>
            <h2>Search Results</h2>
            <?php if (empty($communities) && empty($members)): ?>
                <p>No matching results found.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($communities as $community): ?>
                        <li style="list-style-type: none;">
                            <a href="http://testoztdal.local/views/community.php?id=<?= $community['id'] ?>">
                                <?= htmlspecialchars($community['community_name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <?php foreach ($members as $member): ?>
                        <li style="list-style-type: none;">
                            <a href="http://testoztdal.local/views/community.php?id=<?= $member['community_id'] ?>">
                                <?= htmlspecialchars($member['firstname'] . ' ' . $member['lastname']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <!-- END SEARCH -->

        <h2 id="loader" style="display: none;"><span class="loader"></span> <span class="loader-text">Loading...</span></h2>

        <table id="resultsTable" style="margin-bottom: 50px;"></table>
    </main>
    <script>
        const rows = document.querySelectorAll("#resultsTable tbody");
        rows.forEach((row, index) => {
            if (index % 2 === 0) {
                row.style.backgroundColor = '#7242f2'; // Even rows
            } else {
                row.style.backgroundColor = '#ffffff'; // Odd rows
            }
        });
    </script>
    <script>
        // Back to Top Button Script
        const backToTopButton = document.getElementById('backToTop');

        // Show/Hide Button on Scroll
        window.addEventListener('scroll', () => {
            if (window.scrollY > 200) {
                backToTopButton.style.display = 'block'; // Show button
            } else {
                backToTopButton.style.display = 'none'; // Hide button
            }
        });

        // Scroll to Top Functionality
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth' // Smooth scrolling
            });
        });
    </script>
</body>
</html>
