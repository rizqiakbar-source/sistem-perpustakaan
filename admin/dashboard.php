<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
</head>
<body>
    <h1>Selamat Datang, Admin!</h1>
    <p>Username: <?php echo $_SESSION['username']; ?></p>
    <a href="../logout.php">Logout</a>
</body>
</html>