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
    exit("Pengiriman form tidak valid. Buka kembali form tambah nilai.");
}

// Ambil dan periksa data dari form.
$user_id = (int) $_SESSION['id'];

$siswa_id = filter_var(
    $_POST['siswa_id'] ?? '',
    FILTER_VALIDATE_INT
);

$mapel_id = filter_var(
    $_POST['mapel_id'] ?? '',
    FILTER_VALIDATE_INT
);

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
    !$siswa_id || $siswa_id < 1 ||
    !$mapel_id || $mapel_id < 1
) {
    exit("ID siswa atau mapel tidak valid.");
}

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

// Cocokkan siswa dan mapel dengan penempatan guru yang login.
// Ambil kelas dari database untuk alamat kembali.
$sql_data = "SELECT siswa.kelas
             FROM mengajar
             JOIN siswa ON mengajar.siswa_id = siswa.id
             JOIN mapel ON mengajar.mapel_id = mapel.id
             JOIN guru ON mapel.guru_id = guru.id
             WHERE siswa.id = '$siswa_id'
               AND mapel.id = '$mapel_id'
               AND guru.user_id = '$user_id'
             LIMIT 1";

$result_data = mysqli_query($koneksi, $sql_data);

if (!$result_data) {
    exit("Data penempatan mengajar gagal dibaca.");
}

$data = mysqli_fetch_assoc($result_data);

if (!$data) {
    http_response_code(403);
    exit("Siswa dan mapel bukan penempatan mengajar Anda.");
}

// Cegah penambahan ulang nilai siswa untuk mapel yang sama.
$sql_cek = "SELECT id
            FROM nilai_rapor
            WHERE siswa_id = '$siswa_id'
              AND mapel_id = '$mapel_id'
            LIMIT 1";

$result_cek = mysqli_query($koneksi, $sql_cek);

if (!$result_cek) {
    exit("Pemeriksaan nilai gagal.");
}

$nilai_lama = mysqli_fetch_assoc($result_cek);

if ($nilai_lama) {
    header("Location: form_edit_nilai.php?id=" . $nilai_lama['id']);
    exit;
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

// Simpan nilai baru.
$sql = "INSERT INTO nilai_rapor (
            siswa_id,
            mapel_id,
            nilai_kkm,
            nilai_pengetahuan,
            nilai_keterampilan,
            keterangan
        ) VALUES (
            '$siswa_id',
            '$mapel_id',
            '$nilai_kkm',
            '$nilai_pengetahuan',
            '$nilai_keterampilan',
            '$keterangan_sql'
        )";

if (!mysqli_query($koneksi, $sql)) {
    exit("Nilai siswa gagal ditambahkan.");
}

header("Location: daftar_nilai.php?kelas=" . urlencode($data['kelas']));
exit;