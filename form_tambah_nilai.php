<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['id'])) {
    header("Location: form_login.php");
    exit;

} elseif (($_SESSION['role'] ?? '') !== 'guru') {
    http_response_code(403);
    exit("Akses ditolak. Halaman ini hanya untuk guru.");
}

if (!isset($_GET['siswa_id'], $_GET['mapel_id'])) {
    header("Location: daftar_kelas.php");
    exit;
}

$siswa_id = filter_var($_GET['siswa_id'], FILTER_VALIDATE_INT);
$mapel_id = filter_var($_GET['mapel_id'], FILTER_VALIDATE_INT);
$user_id = (int) $_SESSION['id'];

if (!$siswa_id || !$mapel_id || $siswa_id < 1 || $mapel_id < 1) {
    exit("ID siswa atau mapel tidak valid.");
}

// Ambil siswa dan mapel sesuai penempatan guru yang login.
// Sekaligus periksa apakah nilainya sudah tersedia.
$sql = "SELECT
            siswa.id AS id_siswa,
            siswa.nama AS nama_siswa,
            siswa.kelas,
            mapel.id AS id_mapel,
            mapel.nama_mapel,
            nilai_rapor.id AS id_nilai
        FROM mengajar
        JOIN siswa ON mengajar.siswa_id = siswa.id
        JOIN mapel ON mengajar.mapel_id = mapel.id
        JOIN guru ON mapel.guru_id = guru.id
        LEFT JOIN nilai_rapor
            ON nilai_rapor.siswa_id = siswa.id
            AND nilai_rapor.mapel_id = mapel.id
        WHERE siswa.id = '$siswa_id'
          AND mapel.id = '$mapel_id'
          AND guru.user_id = '$user_id'
        LIMIT 1";

$result = mysqli_query($koneksi, $sql);

if (!$result) {
    exit("Data siswa dan mapel gagal dibaca.");
}

$data = mysqli_fetch_assoc($result);

if (!$data) {
    http_response_code(403);
    exit("Data tidak ditemukan atau bukan penempatan mengajar Anda.");
}

// Kalau nilainya sudah ada, arahkan ke form edit.
if ($data['id_nilai'] !== null) {
    header("Location: form_edit_nilai.php?id=" . $data['id_nilai']);
    exit;
}

// Token untuk diperiksa pada file proses nanti.
if (empty($_SESSION['csrf_nilai'])) {
    $_SESSION['csrf_nilai'] = bin2hex(random_bytes(32));
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Tambah Nilai</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">

                <div class="card-header">
                    <h4 class="mb-0">Tambah Nilai Siswa</h4>
                </div>

                <div class="card-body">
                    <form action="proses_tambah_nilai.php" method="POST">

                        <input type="hidden"
                               name="siswa_id"
                               value="<?= $data['id_siswa'] ?>">

                        <input type="hidden"
                               name="mapel_id"
                               value="<?= $data['id_mapel'] ?>">

                        <input type="hidden"
                               name="csrf_nilai"
                               value="<?= htmlspecialchars($_SESSION['csrf_nilai']) ?>">

                        <div class="mb-3">
                            <label for="nama_siswa" class="form-label">
                                Nama Siswa
                            </label>

                            <input type="text"
                                   id="nama_siswa"
                                   class="form-control"
                                   value="<?= htmlspecialchars($data['nama_siswa']) ?>"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label for="kelas" class="form-label">
                                Kelas
                            </label>

                            <input type="text"
                                   id="kelas"
                                   class="form-control"
                                   value="<?= htmlspecialchars($data['kelas']) ?>"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label for="nama_mapel" class="form-label">
                                Mata Pelajaran
                            </label>

                            <input type="text"
                                   id="nama_mapel"
                                   class="form-control"
                                   value="<?= htmlspecialchars($data['nama_mapel']) ?>"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label for="nilai_kkm" class="form-label">
                                Nilai KKM
                            </label>

                            <input type="number"
                                   id="nilai_kkm"
                                   name="nilai_kkm"
                                   class="form-control"
                                   value="75"
                                   min="0"
                                   max="100"
                                   step="1"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="nilai_pengetahuan" class="form-label">
                                Nilai Pengetahuan
                            </label>

                            <input type="number"
                                   id="nilai_pengetahuan"
                                   name="nilai_pengetahuan"
                                   class="form-control"
                                   min="0"
                                   max="100"
                                   step="1"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="nilai_keterampilan" class="form-label">
                                Nilai Keterampilan
                            </label>

                            <input type="number"
                                   id="nilai_keterampilan"
                                   name="nilai_keterampilan"
                                   class="form-control"
                                   min="0"
                                   max="100"
                                   step="1"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label">
                                Keterangan
                            </label>

                            <textarea id="keterangan"
                                      name="keterangan"
                                      class="form-control"
                                      rows="3"
                                      maxlength="50"
                                      placeholder="Contoh: Baik, Sangat Baik, Perlu Bimbingan"></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="daftar_nilai.php?kelas=<?= urlencode($data['kelas']) ?>"
                               class="btn btn-secondary">
                                Kembali
                            </a>

                            <button type="submit" class="btn btn-primary">
                                Simpan Nilai
                            </button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

</body>
</html>