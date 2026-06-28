<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Staff login portal for Restaurant ZZ Order Management System">
    <title>Staff Login — Restaurant ZZ</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* ── Login page layout ── */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(145deg, var(--text-brown) 0%, #5a2e18 100%);
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        /* ── Logo / Branding ── */
        .login-logo {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-logo .logo-icon {
            font-size: 3.2rem;
            display: block;
            margin-bottom: 10px;
            animation: floatIcon 3s ease-in-out infinite;
        }
        @keyframes floatIcon {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-6px); }
        }
        .login-logo h1 {
            margin: 0;
            color: var(--bg-cream);
            font-size: 1.55rem;
            font-weight: 700;
            letter-spacing: 0.04em;
        }
        .login-logo p {
            margin: 6px 0 0;
            color: rgba(225, 211, 169, 0.62);
            font-size: 0.8rem;
            font-weight: 400;
        }

        /* ── Login Card ── */
        .login-card {
            background: var(--white);
            border-radius: 16px;
            padding: 36px 36px 24px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.42);
        }
        .login-card h2 {
            margin: 0 0 22px;
            text-align: center;
            color: var(--text-brown);
            font-size: 1.05rem;
            font-weight: 700;
        }

        /* ── Error Banner ── */
        .error-banner {
            display: none;
            align-items: center;
            gap: 9px;
            background: #fde8e8;
            color: var(--danger-red);
            border-left: 4px solid var(--danger-red);
            border-radius: 7px;
            padding: 10px 14px;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .error-banner.show { display: flex; }

        /* ── Form Group ── */
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-brown);
            margin-bottom: 5px;
        }
        .form-group input {
            margin: 0;
            border: 2px solid #e4dcd0;
            border-radius: 6px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 3px rgba(168, 85, 48, 0.12);
        }
        .form-group input.input-error {
            border-color: var(--danger-red);
            box-shadow: 0 0 0 3px rgba(94, 42, 37, 0.10);
        }

        /* ── Login Button ── */
        .btn-login {
            width: 100%;
            padding: 12px;
            font-size: 0.95rem;
            border-radius: 8px;
            margin-top: 8px;
            background: linear-gradient(135deg, #bf5c28, var(--accent-orange));
            box-shadow: 0 4px 14px rgba(168, 85, 48, 0.38);
            transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
            letter-spacing: 0.03em;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 22px rgba(168, 85, 48, 0.52);
            opacity: 1;
        }
        .btn-login:active { transform: translateY(0); }

        /* ── Hint Card ── */
        .hint-card {
            margin-top: 20px;
            border: 1px dashed var(--accent-orange);
            border-radius: 8px;
            padding: 12px 16px;
            background: rgba(168, 85, 48, 0.05);
        }
        .hint-title {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--accent-orange);
            margin-bottom: 8px;
        }
        .hint-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.78rem;
            color: var(--text-brown);
            margin-bottom: 5px;
        }
        .hint-row:last-child { margin-bottom: 0; }
        .hint-row code {
            background: rgba(55, 30, 19, 0.09);
            padding: 2px 9px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-brown);
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .hint-row code:hover {
            background: var(--accent-orange);
            color: #fff;
        }
        .hint-row code::after {
            content: ' ⤵';
            font-size: 0.7rem;
            opacity: 0.6;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">

        <!-- ── Branding ── -->
        <div class="login-logo">
            <span class="logo-icon">🍽️</span>
            <h1>Restaurant ZZ</h1>
            <p>Staff Portal — Order Management System</p>
        </div>

        <!-- ── Login Card ── -->
        <div class="login-card">
            <h2>🔐 Staff Login</h2>

            <!-- Error Banner -->
            <div class="error-banner" id="loginError" role="alert">
                <span>⚠</span>
                <span id="loginErrorText">Invalid username or password.</span>
            </div>

            <!-- Login Form -->
            <form id="loginForm" method="POST" action="process/login_process.php">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           placeholder="Enter your username"
                           autocomplete="username"
                           oninput="clearLoginError()">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Enter your password"
                           autocomplete="current-password"
                           oninput="clearLoginError()">
                </div>

                <button type="button" class="btn-login" id="loginBtn" onclick="doLogin()">
                    Login →
                </button>

            </form>

            <!-- Demo Credentials Hint -->
            <div class="hint-card">
                <div class="hint-title">🔑 Demo Credentials (click to fill)</div>
                <div class="hint-row">
                    <span>Username</span>
                    <code id="hintUser" onclick="fillCred('username','admin')" title="Click to fill">admin</code>
                </div>
                <div class="hint-row">
                    <span>Password</span>
                    <code id="hintPass" onclick="fillCred('password','123456')" title="Click to fill">123456</code>
                </div>
            </div>
        </div>

    </div><!-- /.login-wrapper -->

    <script src="js/admin_validation.js?v=<?= time() ?>"></script>
    <script>
        // Clear auth state on login page load
        sessionStorage.removeItem('ros_auth');

        // ── Login Handler ──
        function doLogin() {
            var user   = document.getElementById('username').value.trim();
            var pass   = document.getElementById('password').value.trim();
            var userIn = document.getElementById('username');
            var passIn = document.getElementById('password');

            clearLoginError();

            if (!user || !pass) {
                showLoginError('Please enter both username and password.');
                if (!user) userIn.classList.add('input-error');
                if (!pass) passIn.classList.add('input-error');
                return;
            }

            // Set session storage auth so client-side guards allow page access
            sessionStorage.setItem('ros_auth', '1');

            // Submit the form to PHP backend for session & database authentication!
            var form = document.getElementById('loginForm');
            form.submit();
        }

        function showLoginError(msg) {
            document.getElementById('loginErrorText').innerText = msg;
            document.getElementById('loginError').classList.add('show');
        }

        function clearLoginError() {
            document.getElementById('loginError').classList.remove('show');
            document.getElementById('username').classList.remove('input-error');
            document.getElementById('password').classList.remove('input-error');
        }

        // Click credential hint → auto-fill field
        function fillCred(fieldId, value) {
            var el = document.getElementById(fieldId);
            if (el) { el.value = value; clearLoginError(); }
        }

        // Allow Enter key to submit
        document.getElementById('loginForm').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') doLogin();
        });
    </script>
</body>
</html>