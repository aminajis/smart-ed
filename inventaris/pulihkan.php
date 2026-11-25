<?php
require_once __DIR__ . '/../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /smart-ed/inventaris/index.php?tab=karantina&pesan=pulihkan_gagal');
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE inventaris SET status='Aktif', lokasi_penyimpanan='Rak Penyimpanan' WHERE id=?");
mysqli_stmt_bind_param($stmt, 'i', $id);
if (mysqli_stmt_execute($stmt)) {
    header('Location: /smart-ed/inventaris/index.php?tab=aktif&pesan=pulihkan_berhasil');
    exit;
}
header('Location: /smart-ed/inventaris/index.php?tab=karantina&pesan=pulihkan_gagal');
exit;
