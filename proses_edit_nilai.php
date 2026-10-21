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

// Periksa token yang dikirim dari form.
$token = $_POST['csrf_nilai'] ?? '';

if (
    !is_string($token) ||
    empty($_SESSION['csrf_nilai']) ||
    !hash_equals($_SESSION['csrf_nilai'], $token)
) {
    http_response_code(403);
    exit("Pengiriman form tidak valid. Buka kembali form edit nilai.");
}

// Ambil dan periksa data dari form.
$user_id = (int) $_SESSION['id'];

$id = filter_var(
    $_POST['id'] ?? '',
    FILTER_VALIDATE_INT
);

if (!$id || $id < 1) {
    exit("ID nilai tidak valid.");
}

$aturan_nilai = [
    'options' => [
        'min_range' => 0,
        'max_range' => 100
    ]
];

$nilai_kkm = filter_var(
    $_POST['nilai_kkm'] ?? '',
    FILTER_VALIDATE_INT,
    $aturan_nilai
);

$nilai_pengetahuan = filter_var(
    $_POST['nilai_pengetahuan'] ?? '',
    FILTER_VALIDATE_INT,
    $aturan_nilai
);

$nilai_keterampilan = filter_var(
    $_POST['nilai_keterampilan'] ?? '',
    FILTER_VALIDATE_INT,
    $aturan_nilai
);

if (
    $nilai_kkm === false ||
    $nilai_pengetahuan === false ||
    $nilai_keterampilan === false
) {
    exit("KKM dan nilai harus berupa bilangan bulat antara 0 sampai 100.");
}

$keterangan = $_POST['keterangan'] ?? '';

if (!is_string($keterangan)) {
    exit("Keterangan tidak valid.");
}

$keterangan = trim($keterangan);

// Cari nilai dan pastikan termasuk penempatan guru yang login.
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

// Amankan teks sebelum dimasukkan ke query.
$keterangan_sql = mysqli_real_escape_string($koneksi, $keterangan);

// Sesuaikan panjang keterangan dengan kolom VARCHAR(50).
$sql_panjang = "SELECT CHAR_LENGTH('$keterangan_sql') AS panjang";
$result_panjang = mysqli_query($koneksi, $sql_panjang);

if (!$result_panjang) {
    exit("Keterangan gagal diperiksa.");
}

$panjang = mysqli_fetch_assoc($result_panjang);

if ($panjang['panjang'] > 50) {
    exit("Keterangan maksimal 50 karakter.");
}

// Perbarui hanya bagian nilai dan keterangan.
$sql = "UPDATE nilai_rapor
        SET nilai_kkm = '$nilai_kkm',
            nilai_pengetahuan = '$nilai_pengetahuan',
            nilai_keterampilan = '$nilai_keterampilan',
            keterangan = '$keterangan_sql'
        WHERE id = '$id'";

if (!mysqli_query($koneksi, $sql)) {
    exit("Nilai siswa gagal diperbarui.");
}

header("Location: daftar_nilai.php?kelas=" . urlencode($data['kelas']));
exit;