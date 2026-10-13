<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['id'])) {
    header("Location: form_login.php");
    exit;

} elseif (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak. Halaman ini hanya untuk admin.");
}

if (!isset($_POST['id'], $_POST['mapel_id'], $_POST['siswa_id'])) {
    header("Location: daftar_mengajar.php");
    exit;
}

$id       = (int) $_POST['id'];
$mapel_id = (int) $_POST['mapel_id'];
$siswa_id = (int) $_POST['siswa_id'];

$sql = "UPDATE mengajar
        SET mapel_id = '$mapel_id',
            siswa_id = '$siswa_id'
        WHERE id = '$id'";

if (mysqli_query($koneksi, $sql)) {
    header("Location: daftar_mengajar.php");
    exit();
} else {
    echo "Data mengajar gagal diperbarui: " . mysqli_error($koneksi);
}
?>