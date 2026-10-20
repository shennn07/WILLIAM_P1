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

if (!isset($_GET['id'])) {
    header("Location: daftar_mengajar.php");
    exit;
}

$id = (int) $_GET['id'];

// DIUBAH: Mengambil mapel dan kelas dari ID perwakilan.
$sql_data = "SELECT mengajar.mapel_id, siswa.kelas
             FROM mengajar
             JOIN siswa ON mengajar.siswa_id = siswa.id
             WHERE mengajar.id = '$id'
             LIMIT 1";

$query_data = mysqli_query($koneksi, $sql_data);

if (!$query_data) {
    exit("Data mengajar gagal dibaca.");
}

$data = mysqli_fetch_assoc($query_data);

if (!$data) {
    exit("Data mengajar tidak ditemukan.");
}

// DIUBAH: Memastikan data memiliki kelas.
if ($data['kelas'] === null || $data['kelas'] === '') {
    exit("Kelas kosong. Lengkapi kelas siswa terlebih dahulu.");
}

$mapel_id = (int) $data['mapel_id'];
$kelas = mysqli_real_escape_string($koneksi, $data['kelas']);

// DIUBAH: Menghapus seluruh kelompok mapel dan kelas.
// Tidak lagi hanya menghapus satu ID mengajar.
$sql = "DELETE mengajar FROM mengajar
        JOIN siswa ON mengajar.siswa_id = siswa.id
        WHERE mengajar.mapel_id = '$mapel_id'
          AND siswa.kelas = '$kelas'";

if (mysqli_query($koneksi, $sql)) {
    echo "<script>
        alert('Proses hapus kelompok mengajar selesai.');
        window.location.href = 'daftar_mengajar.php';
    </script>";
} else {
    echo "Data mengajar gagal dihapus: " . mysqli_error($koneksi);
}
?>