<?php
// 1. Masukkan koneksi database lo di baris paling awal
require_once 'config/database.php';

// 2. Cek apakah user menekan tombol daftar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_daftar'])) {
    $nama = trim($_POST['nama']);
    $role = trim($_POST['role']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $register_error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $register_error = 'Password minimal 6 karakter.';
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE username = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $register_error = 'Username sudah digunakan.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, 'INSERT INTO users (nama_lengkap, role, username, email, password) VALUES (?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sssss', $nama, $role, $username, $email, $password_hash);
            if (mysqli_stmt_execute($stmt)) {
                header('Location: /smart-ed/login.php?registered=1');
                exit;
            }
            $register_error = 'Gagal membuat akun.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - SMART ED Apotek Bintang Surya</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/smart-ed/style.css"> 
</head>
<body>

    <div class="login-container">
        <div class="left-panel">
            <div class="brand-top">
                <img src="logo-icon.png" alt="Icon" class="logo-img">
                <div>
                    <strong>SMART ED APOTEK</strong><br>
                    <span>BINTANG SURYA</span>
                </div>
            </div>

            <div class="branding-text">
                <h1>Akurasi Data Apotek</h1>
                <p>Kelola stok dan pantau masa kadaluarsa dengan sistem yang presisi</p>
            </div>
        </div>

        <div class="right-panel">
            <div class="login-box signin-box">
                <h2>Buat Akun Baru</h2>
                <p class="subtitle">Selamat Datang Di Sistem Manajemen SMART ED</p>

                <form action="" method="POST">
                    <div class="form-row">
                        <div class="input-group">
                            <label>NAMA LENGKAP</label>
                            <input type="text" name="nama" placeholder="Masukkan Nama Lengkap Anda" required>
                        </div>
                        <div class="input-group">
                            <label>ROLE / JABATAN</label>
                            <select name="role" class="custom-select" required>
                                <option value="">Pilih Role</option>
                                <option value="Apoteker">Apoteker</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label>USERNAME</label>
                            <input type="text" name="username" placeholder="Masukkan username Anda" required>
                        </div>
                        <div class="input-group">
                            <label>EMAIL</label>
                            <input type="email" name="email" placeholder="Masukkan email Anda" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="input-group">
                            <label>PASSWORD</label>
                            <input type="password" name="password" placeholder="Masukkan password Anda" required>
                        </div>
                        <div class="input-group">
                            <label>KONFIRMASI PASSWORD</label>
                            <input type="password" name="confirm_password" placeholder="Konfirmasi password Anda" required>
                        </div>
                    </div>

                    <div class="checkbox-row">
                        <input type="checkbox" id="terms" required>
                        <label for="terms" style="font-size: 11px;">Saya setuju dengan <a href="#">Syarat & Ketentuan</a> serta <a href="#">Kebijakan Privasi</a> SMART ED Systems.</label>
                    </div>

                    <button type="submit" name="btn_daftar" class="btn-login">Daftar &rarr;</button>
                </form>

                <p class="footer-text">Sudah punya akun? <a href="/smart-ed/login.php">Masuk</a></p>
            </div>
        </div>
    </div>

</body>
</html>