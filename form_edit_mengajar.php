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

$sql = "SELECT * FROM mengajar WHERE id = '$id' LIMIT 1";
$result = mysqli_fetch_assoc(mysqli_query($koneksi, $sql));

if (!$result) {
    exit("Data mengajar tidak ditemukan.");
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Edit Mengajar</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">

                <div class="card-header">
                    <h4>Form Edit Mengajar</h4>
                </div>

                <div class="card-body">
                    <form action="proses_edit_mengajar.php" method="POST">

                        <h3>Data Mengajar</h3>

                        <label>Mata Pelajaran dan Guru:</label>
                        <select name="mapel_id" class="form-control" required>
                            <option value="">Pilih Mapel</option>

                            <?php
                            $mapel_query = "
                                SELECT mapel.id, mapel.nama_mapel,
                                       guru.nama AS nama_guru
                                FROM mapel
                                JOIN guru ON mapel.guru_id = guru.id
                                ORDER BY mapel.nama_mapel
                            ";

                            $mapel_result = mysqli_query($koneksi, $mapel_query);

                            while ($mapel = mysqli_fetch_assoc($mapel_result)) {
                                $selected = ($mapel['id'] == $result['mapel_id'])
                                    ? 'selected' : '';
                            ?>
                                <option value="<?= $mapel['id'] ?>" <?= $selected ?>>
                                    <?= htmlspecialchars(
                                        $mapel['nama_mapel'] . ' - ' . $mapel['nama_guru']
                                    ) ?>
                                </option>
                            <?php } ?>
                        </select>

                        <br>

                        <label>Siswa dan Kelas:</label>
                        <select name="siswa_id" class="form-control" required>
                            <option value="">Pilih Siswa</option>

                            <?php
                            $siswa_query = "
                                SELECT id, nis, nama, kelas
                                FROM siswa
                                ORDER BY kelas, nama
                            ";

                            $siswa_result = mysqli_query($koneksi, $siswa_query);

                            while ($siswa = mysqli_fetch_assoc($siswa_result)) {
                                $selected = ($siswa['id'] == $result['siswa_id'])
                                    ? 'selected' : '';
                            ?>
                                <option value="<?= $siswa['id'] ?>" <?= $selected ?>>
                                    <?= htmlspecialchars(
                                        $siswa['nis'] . ' - ' .
                                        $siswa['nama'] . ' - ' .
                                        $siswa['kelas']
                                    ) ?>
                                </option>
                            <?php } ?>
                        </select>

                        <br>

                        <input type="hidden" name="id" value="<?= $result['id'] ?>">

                        <button type="submit" class="btn btn-primary">
                            Simpan
                        </button>

                        <a href="daftar_mengajar.php" class="btn btn-secondary">
                            Kembali
                        </a>

                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

</body>
</html>