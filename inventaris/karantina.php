<?php
require_once __DIR__ . '/../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /smart-ed/monitoring/index.php?pesan=invalid_id');
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE inventaris SET status = 'Karantina', lokasi_penyimpanan = 'Gudang Karantina' WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    header('Location: /smart-ed/monitoring/index.php?pesan=karantina_berhasil');
    exit;
}

header('Location: /smart-ed/monitoring/index.php?pesan=karantina_gagal');
exit;
