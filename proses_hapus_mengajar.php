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

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $sql = "DELETE FROM mengajar WHERE id = '$id'";

    if (mysqli_query($koneksi, $sql)) {
        echo "<script>
            alert('Proses hapus data mengajar selesai.');
            window.location.href = 'daftar_mengajar.php';
        </script>";
    } else {
        echo "Data mengajar gagal dihapus: " . mysqli_error($koneksi);
    }

} else {
    echo "<script>
        alert('ID mengajar tidak ditemukan.');
        window.location.href = 'daftar_mengajar.php';
    </script>";
}
?>