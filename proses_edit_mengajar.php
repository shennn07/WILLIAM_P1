<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['id'])) {
    header("Location: form_login.php");
    exit;
} elseif ($_SESSION['role'] !== 'admin') {
    exit("Akses ditolak.");
}

if (!isset($_POST['id'], $_POST['mapel_id'], $_POST['kelas'])) {
    header("Location: daftar_mengajar.php");
    exit;
}

$id = (int) $_POST['id'];
$mapel_id = (int) $_POST['mapel_id'];
$kelas_baru = $_POST['kelas'];

$kelas = mysqli_real_escape_string($koneksi, $kelas_baru);

// DIUBAH: Cari kelompok lama melalui ID perwakilan.
$sql = "SELECT mengajar.mapel_id, siswa.kelas
        FROM mengajar
        JOIN siswa ON mengajar.siswa_id = siswa.id
        WHERE mengajar.id = '$id'";

$result = mysqli_query($koneksi, $sql);

if (!$result) {
    exit("Data mengajar gagal dibaca.");
}

$data = mysqli_fetch_assoc($result);

if (!$data) {
    exit("Data mengajar tidak ditemukan.");
}

if ($data['kelas'] === null || $data['kelas'] === '') {
    exit("Lengkapi kelas siswa terlebih dahulu.");
}

$mapel_lama = (int) $data['mapel_id'];
$kelas_lama = mysqli_real_escape_string($koneksi, $data['kelas']);

// Memastikan mapel yang dipilih tersedia.
$sql_mapel = "SELECT id FROM mapel WHERE id = '$mapel_id'";
$result_mapel = mysqli_query($koneksi, $sql_mapel);

if (!$result_mapel || mysqli_num_rows($result_mapel) == 0) {
    exit("Mapel tidak ditemukan.");
}

// DIUBAH: Mengambil semua siswa kelas tujuan.
$sql_siswa = "SELECT id FROM siswa WHERE kelas = '$kelas'";
$result_siswa = mysqli_query($koneksi, $sql_siswa);

if (
    $kelas_baru === '' ||
    !$result_siswa ||
    mysqli_num_rows($result_siswa) == 0
) {
    exit("Kelas tujuan tidak memiliki siswa.");
}

// DIUBAH: Cek duplikat jika mapel atau kelas berubah.
if ($mapel_id != $mapel_lama || $kelas_baru != $data['kelas']) {

    $sql_cek = "SELECT mengajar.id
                FROM mengajar
                JOIN siswa ON mengajar.siswa_id = siswa.id
                WHERE mengajar.mapel_id = '$mapel_id'
                  AND siswa.kelas = '$kelas'";

    $result_cek = mysqli_query($koneksi, $sql_cek);

    if (!$result_cek) {
        exit("Pemeriksaan penempatan gagal.");
    }

    if (mysqli_num_rows($result_cek) > 0) {
        exit("Mapel tersebut sudah ada di kelas tujuan.");
    }
}

// DIUBAH: Menggunakan if–else biasa.
if ($kelas_baru == $data['kelas']) {

    // Kelas tetap: UPDATE semua penempatan dalam kelompok.
    $sql_update = "UPDATE mengajar
                   JOIN siswa ON mengajar.siswa_id = siswa.id
                   SET mengajar.mapel_id = '$mapel_id'
                   WHERE mengajar.mapel_id = '$mapel_lama'
                     AND siswa.kelas = '$kelas_lama'";

    if (!mysqli_query($koneksi, $sql_update)) {
        exit("Data mengajar gagal diperbarui.");
    }

} else {

    // Kelas berubah: hapus penempatan kelompok lama.
    $sql_hapus = "DELETE mengajar FROM mengajar
                  JOIN siswa ON mengajar.siswa_id = siswa.id
                  WHERE mengajar.mapel_id = '$mapel_lama'
                    AND siswa.kelas = '$kelas_lama'";

    if (!mysqli_query($koneksi, $sql_hapus)) {
        exit("Penempatan lama gagal dihapus.");
    }

    // Tambah penempatan untuk setiap siswa kelas tujuan.
    // Sama seperti proses tambah mengajar.
    while ($siswa = mysqli_fetch_assoc($result_siswa)) {
        $siswa_id = (int) $siswa['id'];

        $sql_tambah = "INSERT INTO mengajar (mapel_id, siswa_id)
                       VALUES ('$mapel_id', '$siswa_id')";

        if (!mysqli_query($koneksi, $sql_tambah)) {
            exit("Penambahan gagal. Penempatan lama sudah dihapus; periksa kembali data mengajar.");
        }
    }
}

header("Location: daftar_mengajar.php");
exit;
?>