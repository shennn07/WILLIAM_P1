<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: form_login.php");
    exit;

} elseif (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak. Halaman ini hanya untuk admin.");
}
$mapel_id = (int) $_POST['mapel_id'];
$kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);

// Ambil semua siswa dari kelas yang dipilih.
$sql_siswa = "SELECT id FROM siswa WHERE kelas = '$kelas'";
$result_siswa = mysqli_query($koneksi, $sql_siswa);

if (mysqli_num_rows($result_siswa) > 0) {

    // Masukkan penempatan mengajar satu per satu untuk setiap siswa.
    while ($siswa = mysqli_fetch_assoc($result_siswa)) {

        $siswa_id = $siswa['id'];

        $sql = "INSERT INTO mengajar (mapel_id, siswa_id)
                VALUES ('$mapel_id', '$siswa_id')";

        if (!mysqli_query($koneksi, $sql)) {
            die("Data mengajar gagal ditambahkan: " . mysqli_error($koneksi));
        }
    }

    header("Location: daftar_mengajar.php");
    exit();

} else {
    echo "Tidak ada siswa pada kelas yang dipilih.";
}
?>