<?php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /smart-ed/inventaris/index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nama_obat = trim($_POST['nama_obat'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$no_batch = trim($_POST['no_batch'] ?? '');
$expired = $_POST['tgl_kadaluarsa'] ?? '';
$sisa_stok = filter_input(INPUT_POST, 'sisa_stok', FILTER_VALIDATE_INT);
$lokasi = trim($_POST['lokasi'] ?? '');
$supplier = trim($_POST['supplier'] ?? '');
$satuan = trim($_POST['satuan'] ?? 'Tablet');

$allowed_units = ['Tablet','Kapsul','Botol','Tube','Sachet','Ampul','Pcs'];
if (!in_array($satuan, $allowed_units, true)) $satuan = 'Tablet';

if (!$id || $nama_obat === '' || $no_batch === '' || $expired === '' || $sisa_stok === false || $sisa_stok < 0) {
    header('Location: /smart-ed/inventaris/index.php?pesan=validasi_gagal');
    exit;
}

$status = $sisa_stok > 0 ? 'Tersedia' : 'Habis';
$stmt = mysqli_prepare($conn, 'UPDATE inventaris SET nama_obat=?, no_batch=?, sisa_stok=?, status=?, expired=?, kategori=?, lokasi=?, supplier=?, satuan=? WHERE id=?');
mysqli_stmt_bind_param($stmt, 'ssissssssi', $nama_obat, $no_batch, $sisa_stok, $status, $expired, $kategori, $lokasi, $supplier, $satuan, $id);

if (mysqli_stmt_execute($stmt)) {
    header('Location: /smart-ed/inventaris/index.php?pesan=update_berhasil');
    exit;
}
header('Location: /smart-ed/inventaris/index.php?pesan=update_gagal');
exit;
?>