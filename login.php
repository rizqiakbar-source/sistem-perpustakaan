<!DOCTYPE html>
<html>
<head>
    <title>Login Perpustakaan</title>
</head>
<body>
    <h2>Form Login</h2>
    
    <?php if (isset($_GET['pesan'])): ?>
        <p style="color: red;">Username atau password salah!</p>
    <?php endif; ?>
    
    <form method="POST" action="login_proses.php">
        <table>
            <tr>
                <td>Username</td>
                <td><input type="text" name="username" required></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input type="password" name="password" required></td>
            </tr>
            <tr>
                <td></td>
                <td><button type="submit">Login</button></td>
            </tr>
        </table>
    </form>
</body>
</html>