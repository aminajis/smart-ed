<?php
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/config/database.php';

$create_setting_profile = "CREATE TABLE IF NOT EXISTS setting_profile (
    id INT PRIMARY KEY,
    nama_lengkap VARCHAR(100) DEFAULT '',
    jabatan VARCHAR(100) DEFAULT '',
    email VARCHAR(150) DEFAULT '',
    sipa VARCHAR(100) DEFAULT '',
    nama_apotek VARCHAR(150) DEFAULT 'Apotek Bintang Surya',
    telp_apotek VARCHAR(50) DEFAULT '',
    alamat_apotek TEXT,
    password_akun VARCHAR(255) DEFAULT '',
    stok_min INT DEFAULT 20,
    ed_alert INT DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if (!mysqli_query($conn, $create_setting_profile)) {
    $bootstrap_error = 'Gagal menyiapkan tabel setting_profile: ' . mysqli_error($conn);
} else {
    mysqli_query($conn, 'INSERT IGNORE INTO setting_profile (id) VALUES (1)');
}

include __DIR__ . '/includes/header.php';

$tab = $_GET['tab'] ?? 'profil';
$pesan_sukses = trim($_GET['pesan'] ?? '');
$pesan_gagal = isset($bootstrap_error) ? $bootstrap_error : '';
if ($pesan_sukses === 'email_sukses') {
    $pesan_sukses = 'Email laporan peringatan ED berhasil dikirim.';
} elseif ($pesan_sukses === 'email_gagal') {
    $pesan_sukses = '';
    $detail_error = trim($_SESSION['email_error'] ?? 'Tidak ada detail error tersedia.');
    $pesan_gagal = 'Email laporan peringatan ED gagal dikirim. Detail: ' . $detail_error;
    unset($_SESSION['email_error']);
}
$user_id = (int)($_SESSION['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($tab === 'profil') {
        $nama = trim($_POST['nama'] ?? '');
        $jabatan = trim($_POST['jabatan'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $sipa = trim($_POST['sipa'] ?? '');

        $stmt = mysqli_prepare($conn, 'UPDATE setting_profile SET nama_lengkap=?, jabatan=?, email=?, sipa=? WHERE id=1');
        mysqli_stmt_bind_param($stmt, 'ssss', $nama, $jabatan, $email, $sipa);
        $ok_setting = mysqli_stmt_execute($stmt);

        $ok_user = true;
        if ($user_id > 0) {
            $stmt_user = mysqli_prepare($conn, 'UPDATE users SET nama_lengkap=?, email=? WHERE id=?');
            mysqli_stmt_bind_param($stmt_user, 'ssi', $nama, $email, $user_id);
            $ok_user = mysqli_stmt_execute($stmt_user);
            if ($ok_user) {
                $_SESSION['nama_lengkap'] = $nama;
            }
        }

        if ($ok_setting && $ok_user) {
            $pesan_sukses = 'Profil pengguna berhasil diperbarui dan sinkron ke akun login.';
        } else {
            $pesan_gagal = 'Gagal menyimpan profil: ' . mysqli_error($conn);
        }

    } elseif ($tab === 'sistem') {
        $nama_apotek = trim($_POST['nama_apotek'] ?? '');
        $telp_apotek = trim($_POST['telp_apotek'] ?? '');
        $alamat_apotek = trim($_POST['alamat_apotek'] ?? '');

        $stmt = mysqli_prepare($conn, 'UPDATE setting_profile SET nama_apotek=?, telp_apotek=?, alamat_apotek=? WHERE id=1');
        mysqli_stmt_bind_param($stmt, 'sss', $nama_apotek, $telp_apotek, $alamat_apotek);
        if (mysqli_stmt_execute($stmt)) {
            $pesan_sukses = 'Konfigurasi identitas apotek berhasil disimpan.';
        } else {
            $pesan_gagal = 'Gagal menyimpan data apotek: ' . mysqli_error($conn);
        }

    } elseif ($tab === 'keamanan') {
        $pass_baru = trim($_POST['pass_baru'] ?? '');
        if (strlen($pass_baru) < 6) {
            $pesan_gagal = 'Password baru minimal 6 karakter.';
        } elseif ($user_id > 0) {
            $password_hash = password_hash($pass_baru, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare($conn, 'UPDATE setting_profile SET password_akun=? WHERE id=1');
            mysqli_stmt_bind_param($stmt, 's', $password_hash);
            $ok_setting = mysqli_stmt_execute($stmt);

            $stmt_user = mysqli_prepare($conn, 'UPDATE users SET password=? WHERE id=?');
            mysqli_stmt_bind_param($stmt_user, 'si', $password_hash, $user_id);
            $ok_user = mysqli_stmt_execute($stmt_user);

            if ($ok_setting && $ok_user) {
                $pesan_sukses = 'Password akun berhasil diperbarui dan sinkron.';
            } else {
                $pesan_gagal = 'Gagal memperbarui password: ' . mysqli_error($conn);
            }
        } else {
            $pesan_gagal = 'Sesi pengguna tidak valid.';
        }

    } elseif ($tab === 'notifikasi') {
        $stok_min = max(0, (int)($_POST['stok_min'] ?? 20));
        $ed_alert = max(0, (int)($_POST['ed_alert'] ?? 3));

        $stmt = mysqli_prepare($conn, 'UPDATE setting_profile SET stok_min=?, ed_alert=? WHERE id=1');
        mysqli_stmt_bind_param($stmt, 'ii', $stok_min, $ed_alert);
        if (mysqli_stmt_execute($stmt)) {
            $pesan_sukses = 'Aturan ambang batas notifikasi berhasil disimpan.';
        } else {
            $pesan_gagal = 'Gagal menyimpan aturan alert: ' . mysqli_error($conn);
        }
    }
}

$stmt_profile = mysqli_prepare($conn, 'SELECT * FROM setting_profile WHERE id=1');
mysqli_stmt_execute($stmt_profile);
$data_db = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_profile)) ?: [];

$user_nama    = $data_db['nama_lengkap'] ?: ($_SESSION['nama_lengkap'] ?? 'Administrator');
$user_jabatan = $data_db['jabatan'] ?: 'Apoteker Utama & Manajer Operasional';
$user_email   = $data_db['email'] ?: '';
$user_sipa    = $data_db['sipa'] ?: '';
$nama_apotek   = $data_db['nama_apotek'] ?: 'Apotek Bintang Surya';
$telp_apotek   = $data_db['telp_apotek'] ?: '';
$alamat_apotek = $data_db['alamat_apotek'] ?: '';
$stok_min      = (int)($data_db['stok_min'] ?? 20);
$ed_alert      = (int)($data_db['ed_alert'] ?? 3);
?>

<style>
    .sett-container { display: flex; gap: 30px; padding: 30px; background: #f8fafc; min-height: 100vh; font-family: 'Inter', sans-serif; text-align: left; }
    .sett-sidebar { width: 260px; background: white; border-radius: 16px; padding: 20px; border: 1px solid #eef2f6; height: fit-content; }
    .sett-menu-title { font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 20px; }
    .sett-nav { display: flex; flex-direction: column; gap: 8px; }
    .sett-link { display: flex; align-items: center; padding: 12px 16px; border-radius: 10px; text-decoration: none; color: #64748b; font-size: 14px; font-weight: 500; transition: all 0.2s; }
    .sett-link:hover { background: #f1f5f9; color: #1e293b; }
    .sett-link.active { background: #E6EFF5; color: #2B5B7C; font-weight: 600; }
    .sett-content { flex: 1; background: white; border-radius: 16px; padding: 35px; border: 1px solid #eef2f6; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
    .content-section-title { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-group label { font-size: 11px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-group input, .form-group select, .form-group textarea { padding: 12px 14px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 14px; outline: none; color: #334155; background: #fff; }
    .form-group input:focus { border-color: #2B5B7C; box-shadow: 0 0 0 3px rgba(43,91,124,0.1); }
    .badge-admin { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; display: inline-block; margin-top: 5px; width: fit-content; }
    .btn-save { background: #2B5B7C; color: white; padding: 12px 25px; border-radius: 8px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 15px; transition: background 0.2s; display: block; }
    .btn-save:hover { background: #1f435c; }
    .alert-sukses { background: #dcfce7; color: #15803d; padding: 12px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; margin-bottom: 25px; border: 1px solid #bbf7d0; }
    .alert-gagal { background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; margin-bottom: 25px; border: 1px solid #fca5a5; }
    .toggle-container { background: #f8fafc; border: 1px solid #eef2f6; border-radius: 12px; padding: 20px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
</style>

<div class="sett-container">
    
    <div class="sett-sidebar">
        <div class="sett-menu-title">Pengaturan</div>
        <nav class="sett-nav">
            <a href="/smart-ed/pengaturan.php?tab=profil" class="sett-link <?= $tab == 'profil' ? 'active' : '' ?>">Profil Pengguna</a>
            <a href="/smart-ed/pengaturan.php?tab=sistem" class="sett-link <?= $tab == 'sistem' ? 'active' : '' ?>">Konfigurasi Sistem</a>
            <a href="/smart-ed/pengaturan.php?tab=keamanan" class="sett-link <?= $tab == 'keamanan' ? 'active' : '' ?>">Keamanan &amp; Akses</a>
            <a href="/smart-ed/pengaturan.php?tab=integrasi" class="sett-link <?= $tab == 'integrasi' ? 'active' : '' ?>">Integrasi Pihak Ketiga</a>
            <a href="/smart-ed/pengaturan.php?tab=notifikasi" class="sett-link <?= $tab == 'notifikasi' ? 'active' : '' ?>">Notifikasi &amp; Alert</a>
        </nav>
    </div>

    <div class="sett-content">
        
        <?php if(!empty($pesan_sukses)): ?>
            <div class="alert-sukses">✅ <?= $pesan_sukses ?></div>
        <?php endif; ?>
        
        <?php if(!empty($pesan_gagal)): ?>
            <div class="alert-gagal">❌ <?= $pesan_gagal ?></div>
        <?php endif; ?>

        <form method="POST" action="/smart-ed/pengaturan.php?tab=<?= urlencode($tab) ?>">
            
            <?php if($tab == 'profil'): ?>
                <div class="content-section-title">Informasi Personal</div>
                <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 30px;">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #94a3b8;">👤</div>
                    <div>
                        <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b;">Informasi Personal</h4>
                        <p style="margin: 0; font-size: 12px; color: #64748b;">Informasi ini akan ditampilkan pada resep dan laporan resmi.</p>
                        <span class="badge-admin">ADMINISTRATOR</span> <span style="font-size: 11px; color:#94a3b8; font-weight:600; margin-left:5px;">ID: 88291-ABS</span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($user_nama) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Jabatan Resmi</label>
                        <input type="text" name="jabatan" value="<?= htmlspecialchars($user_jabatan) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email Profesional</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user_email) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor Izin Praktik (SIPA)</label>
                        <input type="text" name="sipa" value="<?= htmlspecialchars($user_sipa) ?>" placeholder="Masukkan nomor SIPA resmi...">
                    </div>
                </div>

                <div class="content-section-title" style="margin-top: 40px;">Preferensi Sistem</div>
                <div class="toggle-container">
                    <div>
                        <strong style="font-size: 14px; color: #1e293b; display: block; margin-bottom: 4px;">Email Alert Stok Menipis</strong>
                        <span style="font-size: 12px; color: #64748b;">Kirim laporan otomatis harian untuk item di bawah ambang batas.</span>
                    </div>
                    <input type="checkbox" checked style="width: 22px; height: 22px; cursor: pointer;">
                </div>

                <button type="submit" class="btn-save">Simpan Perubahan</button>

            <?php elseif($tab == 'sistem'): ?>
                <div class="content-section-title">Konfigurasi Identitas Apotek</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Unit Bisnis / Apotek</label>
                        <input type="text" name="nama_apotek" value="<?= htmlspecialchars($nama_apotek) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor Telepon Operasional</label>
                        <input type="text" name="telp_apotek" value="<?= htmlspecialchars($telp_apotek) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat Fisik Legal</label>
                    <textarea name="alamat_apotek" rows="3" required><?= htmlspecialchars($alamat_apotek) ?></textarea>
                </div>
                <button type="submit" class="btn-save">Simpan Data Apotek</button>

            <?php elseif($tab == 'keamanan'): ?>
                <div class="content-section-title">Kredensial Akun</div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Kata Sandi Baru</label>
                    <input type="password" name="pass_baru" placeholder="Masukkan password baru..." required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-save">Update Password</button>

            <?php elseif($tab == 'integrasi'): ?>
                <div class="content-section-title">Bridging API Eksternal</div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Sandi Organisasi SATUSEHAT (Kemenkes RI)</label>
                    <input type="text" placeholder="Masukkan token Production Client ID" value="MOCK-TOKEN-SATUSEHAT-9921X" disabled style="background:#f1f5f9;">
                </div>
                <button type="button" class="btn-save" style="background:#64748b;">Koneksi API Aktif</button>

            <?php elseif($tab == 'notifikasi'): ?>
                <div class="content-section-title">Pengingat Logistik Obat</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Kategori Stok Tipis (Ambang Batas)</label>
                        <select name="stok_min">
                            <option value="10" <?= $stok_min == 10 ? 'selected' : '' ?>>Sisa 10 unit langsung beri tanda bahaya</option>
                            <option value="20" <?= $stok_min == 20 ? 'selected' : '' ?>>Sisa 20 unit langsung beri tanda bahaya</option>
                            <option value="50" <?= $stok_min == 50 ? 'selected' : '' ?>>Sisa 50 unit langsung beri tanda bahaya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notifikasi Kadaluwarsa (Expired Alert)</label>
                        <select name="ed_alert">
                            <option value="1" <?= $ed_alert == 1 ? 'selected' : '' ?>>Ingatkan 1 bulan sebelum masa kadaluwarsa</option>
                            <option value="3" <?= $ed_alert == 3 ? 'selected' : '' ?>>Ingatkan 3 bulan sebelum masa kadaluwarsa</option>
                            <option value="6" <?= $ed_alert == 6 ? 'selected' : '' ?>>Ingatkan 6 bulan sebelum masa kadaluwarsa</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-save">Simpan Aturan Alert</button>
                <a href="/smart-ed/proses_kirim_email.php" onclick="return confirm('Kirim laporan peringatan ED ke email sekarang?')" style="display:inline-block;margin-top:12px;padding:12px 25px;border-radius:8px;background:#2B6CB0;color:#fff;text-decoration:none;font-size:14px;font-weight:600;">Kirim Laporan Peringatan ED ke Email Sekarang</a>
            <?php endif; ?>

        </form>
    </div>

</div>

<?php include 'includes/footer.php'; ?>