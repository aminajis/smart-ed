<?php
session_start();
require_once __DIR__ . '/config/database.php';

function checkLogin() {
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        header('Location: /smart-ed/login.php');
        exit;
    }
}

function tambahObat($conn, $data) {
    $status = ((int)$data['jumlah_stok'] > 0) ? 'Tersedia' : 'Habis';
    $stmt = mysqli_prepare($conn, 'INSERT INTO inventaris (nama_obat,no_batch,sisa_stok,status,expired,kategori,lokasi,supplier) VALUES (?,?,?,?,?,?,?,?)');
    mysqli_stmt_bind_param($stmt, 'ssisssss', $data['nama_obat'], $data['no_batch'], $data['jumlah_stok'], $status, $data['expiry_date'], $data['kategori'], $data['lokasi'], $data['supplier']);
    return mysqli_stmt_execute($stmt);
}

function ubahObat($conn, $data) {
    $status = ((int)$data['jumlah_stok'] > 0) ? 'Tersedia' : 'Habis';
    $stmt = mysqli_prepare($conn, 'UPDATE inventaris SET nama_obat=?,no_batch=?,sisa_stok=?,status=?,expired=?,kategori=?,lokasi=?,supplier=? WHERE id=?');
    mysqli_stmt_bind_param($stmt, 'ssisssssi', $data['nama_obat'], $data['no_batch'], $data['jumlah_stok'], $status, $data['expiry_date'], $data['kategori'], $data['lokasi'], $data['supplier'], $data['id']);
    return mysqli_stmt_execute($stmt);
}

function hapusObat($conn, $id) {
    $stmt = mysqli_prepare($conn, 'DELETE FROM inventaris WHERE id=?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    return mysqli_stmt_execute($stmt);
}
?>