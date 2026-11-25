<?php
require_once __DIR__ . '/../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: /smart-ed/inventaris/index.php');
    exit;
}

$stmt = mysqli_prepare($conn, 'DELETE FROM inventaris WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
if (mysqli_stmt_execute($stmt)) {
    header('Location: /smart-ed/inventaris/index.php?pesan=hapus_berhasil');
    exit;
}
header('Location: /smart-ed/inventaris/index.php?pesan=hapus_gagal');
exit;
?>