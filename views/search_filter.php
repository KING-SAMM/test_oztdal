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
    <link rel="stylesheet" href="http://testoztdal.local/assets/css/nav.css" />
    <script src="http://testoztdal.local/assets/js/nav.js"></script>
    <style>
        
        @media print {
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                padding: 8px;
                border: 1px solid black;
            }
            caption {
                font-size: 14pt;
                font-weight: bold;
            }
        }
        
    </style>
    
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
                            callback(data.data, data.filterType);
                        } else if (data.status === 'success' && data.message) {
                            alert('Message: ' + data.message);
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
                    console.log(params[key]);
                });

                fetchData('http://testoztdal.local/api/filters/filters.php', params, displayFilteredResults);
            });

            // Display Results for Fetch All
            function displayResults(data) {
                resultsTable.innerHTML = `
                <table style="
                    width: 100%; 
                    border-collapse: collapse; 
                    font-size: 12pt; 
                    font-family: 'Times New Roman', serif; 
                    color: black;
                    margin: 20px 0;
                ">
                    <caption style="
                        font-size: 14pt; 
                        font-weight: bold; 
                        margin-bottom: 10px; 
                        text-align: center;
                        letter-spacing: 8px;
                        font-variant: small-caps;
                        color: rgba(120, 120, 120, 1.0);
                    ">OZTDAL: All Members</caption>
                    <thead style="
                        border-bottom: 2px solid black;
                        position: sticky; top: 0;
                        max-width: 100vw;
                    ">
                        <tr class="tableHeader" 
                            style="
                                background: white;
                                BOX-SHADOW: 0 4px 8px rgba(0, 0, 0, 0.3);">
                            <th style="
                                padding: 8px; 
                                text-align: left; 
                                font-weight: bold; 
                                border-right: 1px solid black;
                                border-bottom: 2px solid black;
                            ">Name</th>
                            <th style="
                                padding: 8px; 
                                text-align: left; 
                                font-weight: bold; 
                                border-right: 1px solid black;
                                border-bottom: 2px solid black;
                            ">Community</th>
                            <th style="
                                padding: 8px; 
                                text-align: left; 
                                font-weight: bold; 
                                border-right: 1px solid black;
                                border-bottom: 2px solid black;
                            ">Local Govt</th>
                            <th style="
                                padding: 8px; 
                                text-align: left; 
                                font-weight: bold;
                                border-bottom: 2px solid black;
                            ">Constituency</th>
                        </tr>
                    </thead>
                    <tbody>
                `;
                data.forEach((item) => {
                    resultsTable.innerHTML += `
                        <tr style="border-bottom: 1px solid black;">
                            <td style="padding: 8px; border-right: 1px solid black;">${item.firstname || ''} ${item.lastname || ''}</td>
                            <td style="padding: 8px; border-right: 1px solid black;">${item.community_name || ''}</td>
                            <td style="padding: 8px; border-right: 1px solid black;">${item.local_govt || ''}</td>
                            <td style="padding: 8px;">${item.constituency || ''}</td>
                        </tr>
                    `;
                });
                resultsTable.innerHTML += `</tbody></table>`;
            }

            // Display Results for Filter
            function displayFilteredResults(data, filterType) {
                console.log(data, filterType)
                if (filterType === 'communities') {
                    resultsTable.innerHTML = communitiesFilter(data);
                } 
                if (filterType === 'members') {
                    resultsTable.innerHTML = membersFilter(data);
                } 
                if (filterType === 'local_govts') {
                    resultsTable.innerHTML = membersFilter(data);
                } 
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
    <nav class="navbar">
        <div class="logo">
            <img src="http://testoztdal.local/assets/img/oztdal_logo-trans.png" alt="Logo">
        </div>
         <!-- Hamburger Menu -->
         <div class="hamburger" onclick="toggleMenu()">
            <div></div>
            <div></div>
            <div></div>
        </div>
        <ul class="nav-links">
            <li><a href="https://oztdal.com.ng">Home</a></li>
            <li><a href="#">About</a></li>
            <!--<li><a href="#">Meetings</a></li>-->
            <li><a href="http://testoztdal.local/views/register.php">Registration & Membership</a></li>
            <!--<li><a href="#">Events</a></li>-->
            <li><a href="https://oztdal.com.ng/payment-dues">Payments & Dues</a></li>
            <!--<li><a href="#">Contact</a></li>-->
            <div class="login_out">
                <p class="welcome">Welcome, <?= htmlspecialchars($_SESSION['user']); ?></p>
                <a class="logout" href="?logout=true">Logout</a>
            </div>
        </ul>
    </nav>
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
                    <option value="communities">Communities</option>
                    <option value="local_governments">Local Governments</option>
                    <option value="constituencies">Constituencies</option>
                    <option value="ezes">Ezes</option>
                    <option value="members">Members</option>
                </select>

                <label for="order">Order:</label>
                <select id="order" name="order">
                    <option value="ASC">Ascending</option>
                    <option value="DESC">Descending</option>
                </select>

                <label for="orderBy">Order By:</label>
                <select id="orderBy" name="orderBy">
                    <option value="">Select order column</option>
                    <option value="community_name">Community Name</option>
                    <option value="community_eze">Eze</option>
                    <option value="firstname">First Name</option>
                    <option value="created_at">Date Created</option>
                </select>

                <button type="submit" title="Filter results">Filter</button>
            </form>
            <div id="printButton" title="print">
                <img src="http://testoztdal.local/assets/img/printer.png" />
            </div>
        </div>

        <h2 id="loader" style="display: none;"><span class="loader"></span> <span class="loader-text">Loading...</span></h2>

        <table id="resultsTable" style="margin-bottom: 50px;"></table>
    </main>
    <script src="http://testoztdal.local/assets/js/filters.js" defer></script>
    <script>
        const table = document.querySelector("#resultsTable");

        // Create a MutationObserver
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    // console.log('Child nodes added or removed');
                    const rows = document.querySelectorAll("#resultsTable tr");
    
                    rows.forEach((row, index) => {
                        if (index % 2 === 0) {
                            row.style.backgroundColor = '#f5f5f5'; // Even rows
                        } else {
                            row.style.backgroundColor = '#ffffff'; // Odd rows
                        }
                    });
                    const addedRows = Array.from(mutation.addedNodes).filter(node => node.tagName === 'tbody');
                    if (addedRows.length > 0) {
                        console.log('New rows added:', addedRows);
                    }
                }
            });
        });

        // Start observing the table for child additions
        observer.observe(table, { childList: true });
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
