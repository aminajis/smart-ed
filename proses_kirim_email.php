<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Panggil library PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mail.php';
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

// Cek dan buat tabel setting_profile jika belum ada
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS setting_profile (
    id INT PRIMARY KEY,
    nama_lengkap VARCHAR(100) DEFAULT '', jabatan VARCHAR(100) DEFAULT '',
    email VARCHAR(150) DEFAULT '', sipa VARCHAR(100) DEFAULT '',
    nama_apotek VARCHAR(150) DEFAULT 'Apotek Bintang Surya',
    telp_apotek VARCHAR(50) DEFAULT '', alamat_apotek TEXT,
    password_akun VARCHAR(255) DEFAULT '', stok_min INT DEFAULT 20, ed_alert INT DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
mysqli_query($conn, 'INSERT IGNORE INTO setting_profile (id) VALUES (1)');
mysqli_query($conn, 'ALTER TABLE setting_profile ADD COLUMN IF NOT EXISTS last_email_sent DATETIME NULL');

// Ambil pengaturan profil
$profile = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT email, nama_apotek, ed_alert FROM setting_profile WHERE id=1')) ?: [];
$ed_alert = max(1, (int)($profile['ed_alert'] ?? 3));
$nama_apotek = $profile['nama_apotek'] ?: 'Apotek Bintang Surya';
$recipient_profile = trim($profile['email'] ?? '');

$mail = new PHPMailer(true);
$email_delivery_silent = $email_delivery_silent ?? false;
$email_send_success = false;

try {
    // ==========================================
    // ⚙️ PENGATURAN SERVER EMAIL
    // ==========================================
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_user;
    $mail->Password = $smtp_pass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->SMTPOptions = [
        'ssl' => [
            'allow_self_signed' => true,
            'verify_peer' => false,
            'verify_peer_name' => false,
        ],
    ];

    $mail->setFrom($smtp_user, 'SMART-ED Apotek');

    if ($recipient_profile === '') {
        throw new Exception('Alamat email penerima belum tersedia pada setting_profile email (id=1).');
    }

    $mail->addAddress($recipient_profile, $nama_apotek);

    // Ambil data obat yang mendekati masa kadaluarsa
    $stmt = mysqli_prepare($conn, "SELECT nama_obat, no_batch, sisa_stok, expired, lokasi
        FROM inventaris
        WHERE (status IS NULL OR status <> 'Karantina')
          AND expired IS NOT NULL
          AND expired <= DATE_ADD(CURDATE(), INTERVAL ? MONTH)
        ORDER BY expired ASC, nama_obat ASC");
    mysqli_stmt_bind_param($stmt, 'i', $ed_alert);
    mysqli_stmt_execute($stmt);
    $result_obat = mysqli_stmt_get_result($stmt);

    // Rakit tabel obat
    $rows = '';
    $count_obat = 0;
    while ($obat = mysqli_fetch_assoc($result_obat)) {
        $count_obat++;
        $rows .= '<tr>'
            . '<td style="padding:8px;border:1px solid #ddd">' . htmlspecialchars($obat['nama_obat']) . '</td>'
            . '<td style="padding:8px;border:1px solid #ddd;text-align:center">' . htmlspecialchars($obat['no_batch']) . '</td>'
            . '<td style="padding:8px;border:1px solid #ddd;text-align:center">' . (int)$obat['sisa_stok'] . '</td>'
            . '<td style="padding:8px;border:1px solid #ddd;text-align:center">' . htmlspecialchars($obat['lokasi'] ?: '-') . '</td>'
            . '<td style="padding:8px;border:1px solid #ddd;text-align:center;color:#d32f2f">' . htmlspecialchars($obat['expired']) . '</td>'
            . '</tr>';
    }

    // Jika tidak ada obat, tampilkan pesan
    if ($rows === '') {
        $rows = '<tr><td colspan="5" style="padding:12px;border:1px solid #ddd;text-align:center">Tidak ada obat aktif yang mendekati kadaluarsa dalam ' . $ed_alert . ' bulan.</td></tr>';
    }

    // ==========================================
    // 💌 KONTEN EMAIL NOTIFIKASI KESELURUHAN
    // ==========================================
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Peringatan ED Obat - ' . $nama_apotek;
    $mail->Body = '<div style="font-family:Arial,sans-serif;max-width:760px"><h2 style="color:#2B5B7C">' . htmlspecialchars($nama_apotek) . '</h2>
        <p>Laporan obat aktif yang mendekati atau telah melewati masa kadaluarsa dalam ambang batas <b>' . $ed_alert . ' bulan</b>.</p>
        <table style="width:100%;border-collapse:collapse;font-size:13px"><thead><tr style="background:#2B5B7C;color:#fff"><th style="padding:10px;border:1px solid #ddd">Nama Obat</th><th style="padding:10px;border:1px solid #ddd">No Batch</th><th style="padding:10px;border:1px solid #ddd">Stok</th><th style="padding:10px;border:1px solid #ddd">Lokasi</th><th style="padding:10px;border:1px solid #ddd">Expired</th></tr></thead><tbody>' . $rows . '</tbody></table>
        <p style="color:#64748b;font-size:12px">Jumlah item terdeteksi: ' . $count_obat . '</p></div>';

    // Kirim email
    $mail->send();
    $sent_at = date('Y-m-d H:i:s');
    $stmt_sent = mysqli_prepare($conn, 'UPDATE setting_profile SET last_email_sent=? WHERE id=1');
    mysqli_stmt_bind_param($stmt_sent, 's', $sent_at);
    mysqli_stmt_execute($stmt_sent);
    $email_send_success = true;

    if ($email_delivery_silent) {
        // Tidak ada redirect atau output untuk pemanggil otomatis/header.
    } elseif (PHP_SAPI === 'cli') {
        echo "Email laporan ED berhasil dikirim.\n";
    } else {
        header('Location: /smart-ed/pengaturan.php?tab=notifikasi&pesan=email_sukses');
        exit;
    }

} catch (\Throwable $e) {
    $error_message = trim($mail->ErrorInfo ?: $e->getMessage());

    if (!$email_delivery_silent) {
        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, 'Email laporan ED gagal dikirim: ' . $error_message . "\n");
            exit(1);
        }

        $_SESSION['email_error'] = $error_message;
        header('Location: /smart-ed/pengaturan.php?tab=notifikasi&pesan=email_gagal');
        exit;
    }
}
?>