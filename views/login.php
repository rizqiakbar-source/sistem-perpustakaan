<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan</title>
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
        .login-card h3 { color: #1a237e; font-weight: 700; }
        .login-card .sub { color: #777; font-size: 14px; margin-bottom: 25px; }
        .login-card .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            width: 100%;
        }
        .login-card .btn-login {
            width: 100%;
            padding: 12px;
            background: #2e7d32;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            transition: 0.3s;
            cursor: pointer;
        }
        .login-card .btn-login:hover { background: #1b5e20; }
        .login-card hr {
            border-color: #eef2f7;
            margin: 20px 0 10px;
            border: none;
            border-top: 1px solid #eef2f7;
        }
        .login-card .footer { font-size: 12px; color: #aaa; }
        .login-card .admin-link { font-size: 13px; }
        .login-card .admin-link a { color: #1a237e; text-decoration: none; }
        .login-card .admin-link a:hover { text-decoration: underline; }
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
        .login-card .link-daftar {
            text-align: center;
            margin-top: 10px;
            font-size: 13px;
        }
        .login-card .link-daftar a {
            color: #2e7d32;
            font-weight: 600;
            text-decoration: none;
        }
        .login-card .link-daftar a:hover {
            text-decoration: underline;
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
            <div class="alert alert-danger">❌ NIS/NIM atau password salah!</div>
        <?php endif; ?>

        <form action="login_anggota_proses.php" method="POST">
            <div class="mb-3 text-start">
                <label class="form-label fw-bold">NIS / NIM</label>
                <input type="text" name="nis_nim" class="form-control" placeholder="Masukkan NIS/NIM" required>
            </div>
            <div class="mb-3 text-start">
                <label class="form-label fw-bold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn-login">LOGIN SEBAGAI ANGGOTA</button>
        </form>

        <div class="link-daftar">
            Belum punya akun? <a href="daftar_anggota.php">Daftar di sini</a>
        </div>

        <div class="divider">
            <hr>
            <span>atau</span>
            <hr>
        </div>

        <p class="admin-link">Login sebagai <a href="login_admin.php">Admin</a></p>

        <hr>
        <div class="footer">&copy; 2026 Politeknik Negeri Lampung</div>
    </div>
</body>
</html>