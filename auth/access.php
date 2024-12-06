<?php
session_start();
// Check for logout request
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: http://testoztdal.local/auth/access.php');
    exit;
}

// Determine whether the user is logged in
$isLoggedIn = isset($_SESSION['user']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: #fff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background-color: #ffffff;
            color: #333;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-container h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input[type="text"],
        input[type="password"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            outline: none;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 5px rgba(106, 17, 203, 0.5);
        }

        button {
            background-color: #6a11cb;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2575fc;
        }

        .form-footer {
            text-align: center;
            font-size: 14px;
            margin-top: 10px;
            color: #666;
        }

        .form-footer a {
            color: #6a11cb;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }
    </style>
    <link rel="stylesheet" href="http://testoztdal.local/assets/css/nav.css" />
    
</head>
<body>
    <nav class="navbar" style="z-index:1000; position: absolute; top: 0; width:100vw;">
        <div class="logo">
            <img src="http://testoztdal.local/assets/img/oztdal_logo-trans.png" alt="Logo">
        </div>
        <ul class="nav-links">
            <li><a href="http://testoztdal.local/">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="meetings.html">Meetings</a></li>
            <li><a href="http://testoztdal.local/views/register.php">Registration & Membership</a></li>
            <li><a href="events.html">Events</a></li>
            <li><a href="payments.html">Payments & Dues</a></li>
            <?php if (!$isLoggedIn): ?>
                <li><a href="#"Contact</a></li>
            <?php else: ?>
                <div class="login_out">
                    <p class="welcome">Welcome, <?= htmlspecialchars($_SESSION['user']); ?></p>
                    <a class="logout" href="?logout=true" style="text-decoration:none;">Logout</a>
                </div>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="login-container">
        <h1>Login</h1>
        <form id="loginForm">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <div class="form-footer">
            <p>This area is restricted. Ensure you are duly authorized</p>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const username = formData.get('username');
            const password = formData.get('password');

            const response = await fetch('/api/auth/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password }),
            });

            const result = await response.json();
            if (result.status === 'success') {
                window.location.href = 'http://testoztdal.local/views/search_filter.php';
            } else {
                alert(result.message);
            }
        });
    </script>
</body>
</html>
