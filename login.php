<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="staff-portal" style="background-color: var(--text-brown);">

    <div class="card login-container">
        <h2 style="text-align: center; color: var(--accent-orange);">Staff Portal Login</h2>
        
        <form id="loginForm" action="staff_dashboard.php" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter username">

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password">

            <button type="submit" style="width: 100%; margin-top: 10px;">Login</button>
        </form>
    </div>

    <script src="js/admin_validation.js"></script>
</body>
</html>