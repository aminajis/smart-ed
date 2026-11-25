<?php
// 1. Wajib jalankan session di baris paling pertama
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Hubungkan ke database lo
require_once 'config/database.php';

// Jika user ternyata udah login, langsung lempar ke home (biar gak masuk login lagi)
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: /smart-ed/index.php");
    exit;
}

// 3. Proses pengecekan saat tombol login diklik
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id, nama_lengkap, role, username, password FROM users WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'] ?: $row['username'];
            $_SESSION['role'] = $row['role'] ?: 'Admin';
            header('Location: /smart-ed/index.php');
            exit;
        }
    }

    $login_error = 'Username atau Password salah.';
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
                <h2>Selamat Datang</h2>
                <p class="subtitle">Silakan masuk menggunakan akun SMART ED Anda</p>

                <form action="" method="POST">
                    <div class="input-group" style="margin-bottom: 20px;">
                        <label>USERNAME</label>
                        <input type="text" name="username" placeholder="Masukkan username Anda" required>
                    </div>

                    <div class="input-group" style="margin-bottom: 20px;">
                        <label>PASSWORD</label>
                        <input type="password" name="password" placeholder="Masukkan password Anda" required>
                    </div>

                    <button type="submit" name="btn_login" class="btn-login" style="margin-top: 10px;">Masuk &rarr;</button>
                </form>

                <p class="footer-text">Belum punya akun? <a href="/smart-ed/register.php">Daftar Baru</a></p>
            </div>
        </div>
    </div>

</body>
</html>