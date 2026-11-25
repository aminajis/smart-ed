<?php
$host    = "localhost:3307";
$user    = "root";
$pass    = "";
$db_name = "apotek_smart_ed";

$conn = mysqli_connect($host, $user, $pass, $db_name);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>