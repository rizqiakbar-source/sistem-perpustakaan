<?php
// views/daftar_anggota.php - Halaman Daftar Akun Anggota
session_start();
require_once '../config/database.php';

$error = '';
$success = '';

// Data Provinsi dan Kota/Kabupaten (LENGKAP)
$data_provinsi = [
    'Aceh' => ['Banda Aceh', 'Sabang', 'Lhokseumawe', 'Langsa', 'Subulussalam', 'Jantho', 'Kutacane', 'Blangpidie', 'Tapaktuan', 'Bireuen', 'Lhoksukon', 'Sigli', 'Meulaboh', 'Calang', 'Takengon', 'Kota Jantho'],
    'Sumatera Utara' => ['Medan', 'Binjai', 'Pematangsiantar', 'Tebing Tinggi', 'Tanjungbalai', 'Sibolga', 'Padang Sidempuan', 'Gunungsitoli', 'Kisaran', 'Rantauprapat', 'Sidikalang', 'Tarutung', 'Balige', 'Parapat', 'Stabat', 'Lubuk Pakam', 'Deli Serdang', 'Serdang Bedagai'],
    'Sumatera Barat' => ['Padang', 'Bukittinggi', 'Payakumbuh', 'Solok', 'Sawahlunto', 'Padang Panjang', 'Pariaman', 'Batusangkar', 'Lubuk Basung', 'Sijunjung', 'Muara Sijunjung', 'Painan', 'Kota Padang'],
    'Riau' => ['Pekanbaru', 'Dumai', 'Bengkalis', 'Siak', 'Selat Panjang', 'Bagansiapiapi', 'Rengat', 'Tembilahan', 'Pangkalan Kerinci', 'Pasir Pengaraian', 'Teluk Kuantan', 'Bangkinang', 'Kota Pekanbaru'],
    'Kepulauan Riau' => ['Tanjungpinang', 'Batam', 'Karimun', 'Tarempa', 'Ranai', 'Daik', 'Senayang', 'Kota Tanjungpinang'],
    'Jambi' => ['Jambi', 'Sungai Penuh', 'Muara Bulian', 'Muara Tembesi', 'Bangko', 'Sarolangun', 'Sungai Gelam', 'Kota Jambi', 'Merlung'],
    'Sumatera Selatan' => ['Palembang', 'Prabumulih', 'Lubuklinggau', 'Pagar Alam', 'Baturaja', 'Kayuagung', 'Lahat', 'Sekayu', 'Muara Enim', 'Tanjung Api-api', 'Kota Palembang'],
    'Bangka Belitung' => ['Pangkalpinang', 'Sungailiat', 'Muntok', 'Tanjung Pandan', 'Manggar', 'Koba', 'Mentok', 'Kota Pangkalpinang'],
    'Bengkulu' => ['Bengkulu', 'Manna', 'Argamakmur', 'Mukomuko', 'Kepahiang', 'Kota Bengkulu', 'Curup'],
    'Lampung' => ['Bandar Lampung', 'Metro', 'Kotabumi', 'Menggala', 'Gunung Sugih', 'Pringsewu', 'Kalianda', 'Sukadana', 'Liwa', 'Blambangan Umpu', 'Tulang Bawang', 'Kota Bandar Lampung', 'Kota Metro'],
    'DKI Jakarta' => ['Jakarta Pusat', 'Jakarta Barat', 'Jakarta Selatan', 'Jakarta Timur', 'Jakarta Utara', 'Kepulauan Seribu'],
    'Banten' => ['Serang', 'Tangerang', 'Cilegon', 'Tangerang Selatan', 'Pandeglang', 'Rangkasbitung', 'Balaraja', 'Kota Serang'],
    'Jawa Barat' => ['Bandung', 'Bekasi', 'Bogor', 'Cimahi', 'Cirebon', 'Depok', 'Sukabumi', 'Tasikmalaya', 'Banjar', 'Purwakarta', 'Karawang', 'Cianjur', 'Garut', 'Ciamis', 'Kuningan', 'Majalengka', 'Sumedang', 'Indramayu', 'Subang', 'Cikarang', 'Lembang', 'Cipanas', 'Puncak', 'Kota Bandung', 'Kota Bekasi', 'Kota Bogor'],
    'Jawa Tengah' => ['Semarang', 'Surakarta', 'Magelang', 'Pekalongan', 'Salatiga', 'Tegal', 'Kudus', 'Purwokerto', 'Cilacap', 'Demak', 'Jepara', 'Kebumen', 'Klaten', 'Kendal', 'Pati', 'Rembang', 'Sragen', 'Sukoharjo', 'Temanggung', 'Wonosobo', 'Kebonagung', 'Kota Semarang', 'Kota Surakarta'],
    'DI Yogyakarta' => ['Yogyakarta', 'Sleman', 'Bantul', 'Kulon Progo', 'Gunung Kidul', 'Kota Yogyakarta'],
    'Jawa Timur' => ['Surabaya', 'Malang', 'Madiun', 'Kediri', 'Blitar', 'Pasuruan', 'Mojokerto', 'Probolinggo', 'Jember', 'Banyuwangi', 'Bojonegoro', 'Bondowoso', 'Gresik', 'Lamongan', 'Lumajang', 'Ngawi', 'Pacitan', 'Pamekasan', 'Pasuruan', 'Ponorogo', 'Sampang', 'Sidoarjo', 'Situbondo', 'Sumenep', 'Trenggalek', 'Tuban', 'Tulungagung', 'Kediri', 'Kota Surabaya', 'Kota Malang', 'Kota Kediri', 'Kota Madiun'],
    'Bali' => ['Denpasar', 'Singaraja', 'Tabanan', 'Gianyar', 'Bangli', 'Klungkung', 'Karangasem', 'Negara', 'Kota Denpasar'],
    'Nusa Tenggara Barat' => ['Mataram', 'Bima', 'Sumbawa Besar', 'Dompu', 'Praya', 'Raba', 'Kota Mataram', 'Kota Bima'],
    'Nusa Tenggara Timur' => ['Kupang', 'Ende', 'Maumere', 'Ruteng', 'Waingapu', 'Atambua', 'Kalabahi', 'Larantuka', 'Lewoleba', 'Soe', 'Kota Kupang'],
    'Kalimantan Barat' => ['Pontianak', 'Singkawang', 'Mempawah', 'Sambas', 'Bengkayang', 'Landak', 'Sanggau', 'Sintang', 'Kapuas', 'Sekadau', 'Melawi', 'Kayong Utara', 'Kota Pontianak', 'Kota Singkawang'],
    'Kalimantan Tengah' => ['Palangka Raya', 'Sampit', 'Pangkalan Bun', 'Kuala Kapuas', 'Muara Teweh', 'Buntok', 'Kasongan', 'Kota Palangka Raya'],
    'Kalimantan Selatan' => ['Banjarmasin', 'Banjarbaru', 'Martapura', 'Kandangan', 'Barabai', 'Amuntai', 'Kotabaru', 'Rantau', 'Pelaihari', 'Kota Banjarmasin', 'Kota Banjarbaru'],
    'Kalimantan Timur' => ['Samarinda', 'Balikpapan', 'Bontang', 'Tenggarong', 'Sendawar', 'Malinau', 'Tanjung Redeb', 'Kota Samarinda', 'Kota Balikpapan', 'Kota Bontang'],
    'Kalimantan Utara' => ['Tarakan', 'Tanjung Selor', 'Malinau', 'Nunukan', 'Kota Tarakan'],
    'Sulawesi Utara' => ['Manado', 'Bitung', 'Kotamobagu', 'Tomohon', 'Tondano', 'Airmadidi', 'Amurang', 'Kota Manado', 'Kota Bitung'],
    'Sulawesi Tengah' => ['Palu', 'Poso', 'Luwuk', 'Ampana', 'Tentena', 'Banggai', 'Kota Palu'],
    'Sulawesi Selatan' => ['Makassar', 'Parepare', 'Palopo', 'Watampone', 'Sengkang', 'Bulukumba', 'Sinjai', 'Maros', 'Takalar', 'Gowa', 'Barru', 'Pangkajene', 'Sidrap', 'Pinrang', 'Enrekang', 'Luwuk', 'Kota Makassar', 'Kota Parepare', 'Kota Palopo'],
    'Sulawesi Tenggara' => ['Kendari', 'Baubau', 'Kolaka', 'Raha', 'Andoolo', 'Unaaha', 'Kota Kendari', 'Kota Baubau'],
    'Gorontalo' => ['Gorontalo', 'Limboto', 'Marisa', 'Kota Gorontalo'],
    'Sulawesi Barat' => ['Mamuju', 'Polewali', 'Majene', 'Barru', 'Kota Mamuju'],
    'Maluku' => ['Ambon', 'Tual', 'Masohi', 'Tulehu', 'Kota Ambon'],
    'Maluku Utara' => ['Ternate', 'Tidore Kepulauan', 'Sofifi', 'Kota Ternate', 'Kota Tidore Kepulauan'],
    'Papua' => ['Jayapura', 'Merauke', 'Biak', 'Timika', 'Wamena', 'Nabire', 'Manokwari', 'Sorong', 'Fakfak', 'Kota Jayapura', 'Kota Sorong'],
    'Papua Barat' => ['Manokwari', 'Sorong', 'Raja Ampat', 'Bintuni', 'Fakfak', 'Kota Sorong']
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nis_nim = $_POST['nis_nim'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $no_telepon = $_POST['no_telepon'];
    $provinsi = $_POST['provinsi'];
    $kota = $_POST['kota'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi
    if (empty($nis_nim) || empty($nama_lengkap) || empty($email) || empty($password) || empty($provinsi) || empty($kota)) {
        $error = "Semua field wajib diisi!";
    } elseif ($password !== $konfirmasi_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Cek apakah NIS/NIM sudah terdaftar
        $sql = "SELECT COUNT(*) FROM anggota WHERE nis_nim = :nis_nim";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nis_nim' => $nis_nim]);
        if ($stmt->fetchColumn() > 0) {
            $error = "NIS/NIM sudah terdaftar!";
        } else {
            // Cek apakah email sudah terdaftar
            $sql = "SELECT COUNT(*) FROM anggota WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Email sudah terdaftar!";
            } else {
                // Gabungkan alamat
                $alamat = $kota . ', ' . $provinsi;
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Simpan data anggota
                $sql = "INSERT INTO anggota (nis_nim, nama_lengkap, email, no_telepon, alamat, jenis_kelamin, tanggal_daftar, status_aktif, password) 
                        VALUES (:nis_nim, :nama_lengkap, :email, :no_telepon, :alamat, :jenis_kelamin, CURDATE(), 'aktif', :password)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'nis_nim' => $nis_nim,
                    'nama_lengkap' => $nama_lengkap,
                    'email' => $email,
                    'no_telepon' => $no_telepon,
                    'alamat' => $alamat,
                    'jenis_kelamin' => $jenis_kelamin,
                    'password' => $hashed_password
                ]);

                $success = "Pendaftaran berhasil! Silakan login.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Perpustakaan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #e8edf5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .register-card {
            background: #fff;
            padding: 40px 35px;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.1);
            width: 550px;
            max-width: 100%;
        }
        .register-card .logo { text-align: center; font-size: 48px; margin-bottom: 5px; }
        .register-card h3 { color: #1a237e; text-align: center; font-weight: 700; }
        .register-card .sub { text-align: center; color: #777; font-size: 14px; margin-bottom: 25px; }
        .register-card .form-control { border-radius: 8px; padding: 10px 14px; }
        .register-card .btn-register {
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
        .register-card .btn-register:hover { background: #1b5e20; }
        .register-card hr { border-color: #eef2f7; margin: 20px 0 10px; }
        .register-card .footer { text-align: center; font-size: 12px; color: #aaa; }
        .register-card .login-link { text-align: center; font-size: 14px; margin-top: 15px; }
        .register-card .login-link a { color: #1a237e; text-decoration: none; font-weight: 600; }
        .register-card .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="logo">📝</div>
        <h3>DAFTAR AKUN ANGGOTA</h3>
        <p class="sub">Silakan isi data diri Anda</p>

        <?php if ($error): ?>
            <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" id="formDaftar">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">NIS / NIM *</label>
                    <input type="text" name="nis_nim" class="form-control" placeholder="Masukkan NIS/NIM" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama lengkap" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Email *</label>
                <input type="email" name="email" class="form-control" placeholder="email@domain.com" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">No. Telepon</label>
                    <input type="text" name="no_telepon" class="form-control" placeholder="08123456789">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
            </div>

            <!-- ALAMAT: PROVINSI + KOTA/KABUPATEN -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Provinsi *</label>
                    <select name="provinsi" class="form-control" id="provinsi" required>
                        <option value="">-- Pilih Provinsi --</option>
                        <?php foreach (array_keys($data_provinsi) as $prov): ?>
                            <option value="<?= htmlspecialchars($prov) ?>"><?= htmlspecialchars($prov) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Kota / Kabupaten *</label>
                    <select name="kota" class="form-control" id="kota" required>
                        <option value="">-- Pilih Kota/Kabupaten --</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Konfirmasi Password *</label>
                    <input type="password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password" required>
                </div>
            </div>

            <button type="submit" class="btn-register">DAFTAR</button>
        </form>

        <hr>
        <div class="login-link">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
        <div class="footer">&copy; 2026 Politeknik Negeri Lampung</div>
    </div>

    <!-- JavaScript untuk Provinsi -> Kota/Kabupaten -->
    <script>
        const dataProvinsi = <?= json_encode($data_provinsi) ?>;

        const provinsiSelect = document.getElementById('provinsi');
        const kotaSelect = document.getElementById('kota');

        provinsiSelect.addEventListener('change', function() {
            const provinsi = this.value;
            kotaSelect.innerHTML = '<option value="">-- Pilih Kota/Kabupaten --</option>';

            if (provinsi && dataProvinsi[provinsi]) {
                dataProvinsi[provinsi].forEach(function(kota) {
                    const option = document.createElement('option');
                    option.value = kota;
                    option.textContent = kota;
                    kotaSelect.appendChild(option);
                });
            }
        });
    </script>
</body>
</html>