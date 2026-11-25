<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /smart-ed/inventaris/index.php');
    exit;
}

$nama_obat = trim($_POST['nama_obat'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$no_batch = trim($_POST['no_batch'] ?? '');
$expiry_date = $_POST['expiry_date'] ?? '';
$stok = filter_input(INPUT_POST, 'sisa_stok', FILTER_VALIDATE_INT);
$lokasi = trim($_POST['lokasi'] ?? '');
$supplier = trim($_POST['supplier'] ?? '');
$satuan = trim($_POST['satuan'] ?? 'Tablet');

$allowed_units = ['Tablet','Kapsul','Botol','Tube','Sachet','Ampul','Pcs'];
if (!in_array($satuan, $allowed_units, true)) $satuan = 'Tablet';

if ($nama_obat === '' || $no_batch === '' || $expiry_date === '' || $stok === false || $stok < 0) {
    header('Location: /smart-ed/inventaris/tambah.php?pesan=validasi_gagal');
    exit;
}

$status = $stok > 0 ? 'Tersedia' : 'Habis';
$stmt = mysqli_prepare($conn, 'INSERT INTO inventaris (nama_obat, no_batch, sisa_stok, status, expired, kategori, lokasi, supplier, satuan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
mysqli_stmt_bind_param($stmt, 'ssissssss', $nama_obat, $no_batch, $stok, $status, $expiry_date, $kategori, $lokasi, $supplier, $satuan);

if (mysqli_stmt_execute($stmt)) {
    header('Location: /smart-ed/inventaris/index.php?pesan=tambah_berhasil');
    exit;
}
header('Location: /smart-ed/inventaris/tambah.php?pesan=tambah_gagal');
exit;
?>