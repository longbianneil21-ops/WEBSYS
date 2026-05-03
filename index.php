<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QCU Student Portal - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            font-family: 'Poppins', sans-serif;
            height: 100%;
            background: linear-gradient(135deg, #1a2a4a 0%, #0f1a2e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            padding: 50px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .login-header h1 {
            color: #1a2a4a;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .login-header p {
            color: #f0a500;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #1a2a4a;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 13px;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9e9e9;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #f0a500;
            box-shadow: 0 0 0 3px rgba(240, 165, 0, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #f0a500, #e09500);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(240, 165, 0, 0.3);
        }

        .demo-credentials {
            background: #f5f5f5;
            border-left: 3px solid #f0a500;
            padding: 15px;
            border-radius: 8px;
            margin-top: 25px;
        }

        .demo-credentials h3 {
            color: #1a2a4a;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .demo-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .demo-row:last-child {
            margin-bottom: 0;
        }

        .demo-label {
            color: #f0a500;
            font-weight: 600;
        }

        .demo-value {
            color: #1a2a4a;
            font-family: monospace;
        }

        .admin-link {
            text-align: center;
            margin-top: 20px;
        }

        .admin-link a {
            color: #f0a500;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
        }

        .admin-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                max-width: 100%;
                margin: 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }

            .demo-credentials {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">📚</div>
            <h1>QCU Student Portal</h1>
            <p>Login to Continue</p>
        </div>

        <form action="auth/login.php" method="POST">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" required placeholder="Enter your username or email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>

            <button type="submit" class="login-btn">Login</button>

            <div class="demo-credentials">
                <h3>Demo Credentials</h3>
                <div class="demo-row">
                    <div><span class="demo-label">Admin:</span></div>
                    <div></div>
                </div>
                <div class="demo-row">
                    <div>Username:</div>
                    <div class="demo-value">admin</div>
                </div>
                <div class="demo-row">
                    <div>Password:</div>
                    <div class="demo-value">admin123</div>
                </div>

                <div class="demo-row" style="margin-top: 15px;">
                    <div><span class="demo-label">Student:</span></div>
                    <div></div>
                </div>
                <div class="demo-row">
                    <div>Username:</div>
                    <div class="demo-value">neil.longbian</div>
                </div>
                <div class="demo-row">
                    <div>Password:</div>
                    <div class="demo-value">student123</div>
                </div>
            </div>
        </form>

        <div class="admin-link">
            <p>💡 For questions, contact <a href="mailto:support@qcu.edu.ph">Student Services</a></p>
        </div>
    </div>
</body>
</html>
