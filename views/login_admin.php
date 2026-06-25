<?php
// views/login_admin.php
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Perpustakaan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #e8edf5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .login-card {
            background: #fff;
            padding: 40px 40px 30px 40px;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.1);
            width: 420px;
            text-align: center;
        }
        .login-card .logo { font-size: 48px; margin-bottom: 5px; }
        .login-card h3 { 
            color: #1a237e; 
            font-weight: 700; 
            margin-top: 0;
            margin-bottom: 2px;
        }
        .login-card .sub { 
            color: #777; 
            font-size: 14px; 
            margin-bottom: 25px;
            margin-top: 0;
        }
        .login-card .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            width: 100%;
            height: 46px;
        }
        .login-card .btn-login {
            width: 100%;
            padding: 12px;
            background: #1a237e;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            transition: 0.3s;
            cursor: pointer;
            height: 46px;
        }
        .login-card .btn-login:hover { background: #0d1555; }
        .login-card hr {
            border-color: #eef2f7;
            margin: 20px 0 10px;
            border: none;
            border-top: 1px solid #eef2f7;
        }
        .login-card .footer { 
            font-size: 12px; 
            color: #aaa;
            margin-top: 18px;
            margin-bottom: 0;
        }
        .login-card .switch-link { 
            font-size: 13px;
            margin-top: 0;
            margin-bottom: 0;
        }
        .login-card .switch-link a { 
            color: #1a237e; 
            text-decoration: none; 
            font-weight: 600; 
        }
        .login-card .switch-link a:hover { text-decoration: underline; }
        .login-card .divider {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        .login-card .divider hr {
            flex: 1;
            border: none;
            border-top: 1px solid #eef2f7;
            margin: 0;
        }
        .login-card .divider span {
            padding: 0 15px;
            color: #aaa;
            font-size: 13px;
        }
        .login-card .mb-3 {
            margin-bottom: 16px;
        }
        .login-card .text-start {
            text-align: left;
        }
        .login-card .fw-bold {
            font-weight: 700;
        }
        .login-card .form-label {
            margin-bottom: 5px;
            display: block;
        }
        .alert {
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-danger {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">📚</div>
        <h3>SISTEM PERPUSTAKAAN</h3>
        <p class="sub">Politeknik Negeri Lampung</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">❌ Username atau password salah!</div>
        <?php endif; ?>

        <form action="../login_proses.php" method="POST">
            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn-login">LOGIN SEBAGAI ADMIN</button>
        </form>

        <div class="divider">
            <hr>
            <span>atau</span>
            <hr>
        </div>

        <p class="switch-link">Login sebagai <a href="login.php">Anggota</a></p>

        <hr>
        <div class="footer">&copy; 2026 Politeknik Negeri Lampung</div>
    </div>
</body>
</html>