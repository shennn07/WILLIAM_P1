<?php
require 'database.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: form_login.php");
    exit;

} elseif (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak. Halaman ini hanya untuk admin.");
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $mapel = "DELETE FROM mapel WHERE id = $id";
    if (mysqli_query($koneksi, $mapel)) {
        echo "<script>alert('Data mapel berhasil dihapus.'); window.location.href = 'daftar_mapel.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat menghapus data mapel: " . mysqli_error($koneksi) . "'); window.location.href = 'daftar_mapel.php';</script>";
    }
} else {
    echo "<script>alert('ID mapel tidak ditemukan.'); window.location.href = 'daftar_mapel.php';</script>";
}
?>