<?php
// views/ubah_foto_admin.php - Ubah Foto Profil Admin
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['foto_base64'])) {
    $foto_base64 = $_POST['foto_base64'];
    $foto_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $foto_base64));
    
    $upload_dir = '../upload/foto_admin/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_path = $upload_dir . $user_id . '.jpg';
    
    if (file_put_contents($file_path, $foto_data)) {
        $success = "Foto profil berhasil diubah!";
    } else {
        $error = "Gagal mengupload foto!";
    }
}
?>
<!DOCTYPE html>
<html lang="id" id="html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Foto Admin - Perpustakaan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --bg-body: #f1f4f9;
            --bg-card: #ffffff;
            --text-color: #333;
            --border-color: #eef2f7;
            --shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        body.dark-mode {
            --bg-body: #1a1a2e;
            --bg-card: #16213e;
            --text-color: #e8e8e8;
            --border-color: #2a2a4a;
            --shadow: 0 2px 12px rgba(0,0,0,0.3);
        }
        body {
            background: var(--bg-body);
            color: var(--text-color);
            transition: 0.3s;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 240px;
            height: 100%;
            background: #1a237e;
            color: #fff;
            padding: 20px 0;
            overflow-y: auto;
            z-index: 1000;
        }
        body.dark-mode .sidebar { background: #0d1b2a; }
        .sidebar .brand { text-align: center; padding: 10px 0 20px 0; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar .brand h3 { font-size: 18px; font-weight: 700; }
        .sidebar .brand small { font-size: 11px; opacity: 0.7; }
        .sidebar .menu { list-style: none; padding: 0 15px; }
        .sidebar .menu li { padding: 12px 16px; margin: 4px 0; border-radius: 10px; font-size: 14px; cursor: pointer; transition: 0.3s; color: rgba(255,255,255,0.7); }
        .sidebar .menu li:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar .menu li.active { background: rgba(255,255,255,0.15); color: #fff; font-weight: 600; }
        .sidebar .menu li .icon { margin-right: 12px; }
        .main-content { margin-left: 240px; padding: 20px 30px; min-height: 100vh; }
        @media (max-width: 768px) {
            .sidebar { width: 60px; padding: 10px 0; }
            .sidebar .brand h3 { display: none; }
            .sidebar .brand small { display: none; }
            .sidebar .menu li span { display: none; }
            .sidebar .menu li { text-align: center; padding: 12px 0; }
            .main-content { margin-left: 60px; padding: 15px; }
            .top-header { flex-direction: column; align-items: flex-start; gap: 10px; }
        }
        .navbar-custom {
            background: #1a237e !important;
            padding: 12px 20px;
            border-radius: 10px;
        }
        body.dark-mode .navbar-custom { background: #0d1b2a !important; }
        .navbar-custom .navbar-brand { font-weight: 700; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #fff;
        }
        .user-info .name { font-weight: 600; font-size: 14px; }
        .user-info .role { font-size: 12px; opacity: 0.8; }
        .user-info .logout { color: #ef9a9a; text-decoration: none; font-weight: 600; }
        .user-info .logout:hover { text-decoration: underline; }
        .preview-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            color: #fff;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.3);
            background: #1a237e;
        }
        .preview-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .theme-toggle {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 50%;
            transition: 0.3s;
            color: #fff;
        }
        .theme-toggle:hover { background: rgba(255,255,255,0.1); }
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0 25px 0;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 25px;
        }
        .top-header .page-title h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1a237e;
        }
        body.dark-mode .top-header .page-title h2 { color: #90caf9; }
        .top-header .page-title small { font-size: 13px; color: #888; }
        .card-custom {
            background: var(--bg-card);
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
            transition: 0.3s;
        }
        .profile-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid var(--border-color);
            object-fit: cover;
            display: block;
            margin: 0 auto 15px;
        }
        .crop-container {
            max-width: 100%;
            margin: 20px auto;
            display: none;
            background: var(--bg-body);
            padding: 10px;
            border-radius: 8px;
        }
        .crop-container img { max-width: 100%; display: block; }
        .btn-container {
            display: none;
            text-align: center;
            margin-top: 15px;
            gap: 10px;
        }
        .btn-container .btn { margin: 0 5px; }
    </style>
</head>
<body id="body">

<div class="sidebar">
    <div class="brand">
        <h3>📚 PERPUSTAKAAN</h3>
        <small>Politeknik Negeri Lampung</small>
    </div>
    <ul class="menu">
        <li onclick="location.href='../index.php'"><span class="icon">📊</span> <span>Dashboard</span></li>
        <li onclick="location.href='buku/index.php'"><span class="icon">📚</span> <span>Buku</span></li>
        <li onclick="location.href='anggota/index.php'"><span class="icon">👤</span> <span>Anggota</span></li>
        <li onclick="location.href='peminjaman/index.php'"><span class="icon">📝</span> <span>Peminjaman</span></li>
        <li onclick="location.href='pengembalian/index.php'"><span class="icon">↩️</span> <span>Pengembalian</span></li>
        <li onclick="location.href='laporan/index.php'"><span class="icon">📊</span> <span>Laporan</span></li>
    </ul>
</div>

<div class="main-content">

    <nav class="navbar navbar-dark navbar-custom">
        <div class="container-fluid">
            <span class="navbar-brand">📚 SISTEM PERPUSTAKAAN</span>
            <div class="user-info">
                <button class="theme-toggle" id="themeToggle" title="Toggle Dark/Light Mode">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="preview-avatar"><?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?></div>
                <div>
                    <div class="name"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></div>
                    <div class="role"><?= $_SESSION['role'] ?></div>
                </div>
                <a href="../logout.php" class="logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="top-header">
        <div class="page-title">
            <h2>📷 Ubah Foto Profil</h2>
            <small>Pilih foto, crop sesuai keinginan, lalu upload</small>
        </div>
        <div>
            <a href="profil_admin.php" class="btn btn-secondary btn-sm">⬅ Kembali</a>
        </div>
    </div>

    <div style="max-width: 600px; margin: 0 auto;">
        <div class="card-custom">
            <div class="text-center">
                <?php 
                $foto_path = '../upload/foto_admin/' . $user_id . '.jpg';
                if (file_exists($foto_path)): ?>
                    <img src="<?= $foto_path ?>" class="profile-preview" id="previewFoto">
                <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama_lengkap']) ?>&size=150&background=1a237e&color=fff" class="profile-preview" id="previewFoto">
                <?php endif; ?>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Foto (JPG, PNG, JPEG, max 2MB)</label>
                <input type="file" id="fotoInput" class="form-control" accept="image/*">
            </div>

            <div class="crop-container" id="cropContainer">
                <img id="imageCrop" src="">
            </div>

            <div class="btn-container" id="btnContainer">
                <button type="button" class="btn btn-success" id="cropBtn">✂️ Crop & Upload</button>
                <button type="button" class="btn btn-secondary" id="cancelBtn">Batal</button>
            </div>

            <form id="uploadForm" method="POST" style="display:none;">
                <input type="hidden" name="foto_base64" id="fotoBase64">
            </form>
        </div>
    </div>

    <div style="text-align:center; color:#aaa; font-size:12px; padding:20px 0 10px 0; border-top:1px solid var(--border-color); margin-top:20px;">
        &copy; 2026 Politeknik Negeri Lampung
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script src="../assets/js/darkmode.js"></script>
<script>
    let cropper = null;
    const fotoInput = document.getElementById('fotoInput');
    const cropContainer = document.getElementById('cropContainer');
    const btnContainer = document.getElementById('btnContainer');
    const imageCrop = document.getElementById('imageCrop');
    const previewFoto = document.getElementById('previewFoto');

    fotoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal 2MB!');
                fotoInput.value = '';
                return;
            }
            const allowed = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!allowed.includes(file.type)) {
                alert('Hanya file JPG, JPEG, PNG yang diperbolehkan!');
                fotoInput.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                imageCrop.src = e.target.result;
                cropContainer.style.display = 'block';
                btnContainer.style.display = 'block';
                if (cropper) { cropper.destroy(); cropper = null; }
                setTimeout(function() {
                    cropper = new Cropper(imageCrop, {
                        viewMode: 1,
                        aspectRatio: 1,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        background: false,
                        guides: true,
                        center: true,
                        highlight: true,
                        responsive: true,
                    });
                }, 100);
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('cropBtn').addEventListener('click', function() {
        if (cropper) {
            const canvas = cropper.getCroppedCanvas({ width: 300, height: 300 });
            const base64 = canvas.toDataURL('image/jpeg', 0.9);
            document.getElementById('fotoBase64').value = base64;
            document.getElementById('uploadForm').submit();
        }
    });

    document.getElementById('cancelBtn').addEventListener('click', function() {
        cropContainer.style.display = 'none';
        btnContainer.style.display = 'none';
        fotoInput.value = '';
        if (cropper) { cropper.destroy(); cropper = null; }
    });
</script>

</body>
</html>