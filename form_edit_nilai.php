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

if (!isset($_GET['id'])) {
    header("Location: daftar_kelas.php");
    exit;
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
$user_id = (int) $_SESSION['id'];

if (!$id || $id < 1) {
    exit("ID nilai tidak valid.");
}

// Ambil nilai beserta identitas siswa dan mapelnya.
// Pastikan siswa dan mapel masih termasuk penempatan guru yang login.
$sql = "SELECT
            nilai_rapor.*,
            siswa.nama AS nama_siswa,
            siswa.kelas,
            mapel.nama_mapel
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

$result = mysqli_query($koneksi, $sql);

if (!$result) {
    exit("Data nilai gagal dibaca.");
}

$data = mysqli_fetch_assoc($result);

if (!$data) {
    http_response_code(403);
    exit("Data tidak ditemukan atau bukan penempatan mengajar Anda.");
}

// Jika KKM di database masih NULL, tampilkan nilai awal 75.
$kkm = 75;

if ($data['nilai_kkm'] !== null) {
    $kkm = $data['nilai_kkm'];
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
    <title>Form Edit Nilai</title>

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
                    <h4 class="mb-0">Edit Nilai Siswa</h4>
                </div>

                <div class="card-body">
                    <form action="proses_edit_nilai.php" method="POST">

                        <input type="hidden"
                               name="id"
                               value="<?= $data['id'] ?>">

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
                                   value="<?= htmlspecialchars((string) $kkm) ?>"
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
                                   value="<?= htmlspecialchars($data['nilai_pengetahuan'] ?? '') ?>"
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
                                   value="<?= htmlspecialchars($data['nilai_keterampilan'] ?? '') ?>"
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
                                      maxlength="50"><?= htmlspecialchars($data['keterangan'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="daftar_nilai.php?kelas=<?= urlencode($data['kelas']) ?>"
                               class="btn btn-secondary">
                                Kembali
                            </a>

                            <button type="submit" class="btn btn-primary">
                                Update Nilai
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