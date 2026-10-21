<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['id'])) {
    header("Location: form_login.php");
    exit;
}

if (($_SESSION['role'] ?? '') !== 'guru') {
    http_response_code(403);
    exit("Akses ditolak. Halaman ini hanya untuk guru.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: daftar_kelas.php");
    exit;
}

// Periksa token yang dikirim dari tombol hapus.
$token = $_POST['csrf_nilai'] ?? '';

if (
    !is_string($token) ||
    empty($_SESSION['csrf_nilai']) ||
    !hash_equals($_SESSION['csrf_nilai'], $token)
) {
    http_response_code(403);
    exit("Permintaan hapus tidak valid. Buka kembali daftar nilai.");
}

$user_id = (int) $_SESSION['id'];

$id = filter_var(
    $_POST['id'] ?? '',
    FILTER_VALIDATE_INT
);

if (!$id || $id < 1) {
    exit("ID nilai tidak valid.");
}

// Ambil kelas sebelum nilai dihapus.
// Pastikan nilai termasuk penempatan guru yang login.
$sql_data = "SELECT nilai_rapor.id, siswa.kelas
             FROM nilai_rapor
             JOIN siswa ON nilai_rapor.siswa_id = siswa.id
             JOIN mapel ON nilai_rapor.mapel_id = mapel.id
             JOIN guru ON mapel.guru_id = guru.id
             JOIN mengajar
                 ON mengajar.siswa_id = siswa.id
                 AND mengajar.mapel_id = mapel.id
             WHERE nilai_rapor.id = '$id'
               AND guru.user_id = '$user_id'
             LIMIT 1";

$result_data = mysqli_query($koneksi, $sql_data);

if (!$result_data) {
    exit("Data nilai gagal dibaca.");
}

$data = mysqli_fetch_assoc($result_data);

if (!$data) {
    http_response_code(403);
    exit("Nilai tidak ditemukan atau bukan penempatan mengajar Anda.");
}

// Hapus satu data nilai yang dipilih.
$sql = "DELETE FROM nilai_rapor WHERE id = '$id'";

if (!mysqli_query($koneksi, $sql)) {
    exit("Nilai siswa gagal dihapus.");
}

header("Location: daftar_nilai.php?kelas=" . urlencode($data['kelas']));
exit;