<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['id'])) {
    header("Location: form_login.php");
    exit;
} elseif ($_SESSION['role'] !== 'admin') {
    exit("Akses ditolak.");
}

if (!isset($_GET['id'])) {
    header("Location: daftar_mengajar.php");
    exit;
}

$id = (int) $_GET['id'];

// DIUBAH: Mengambil data mengajar beserta kelasnya.
$sql = "SELECT mengajar.*, siswa.kelas
        FROM mengajar
        JOIN siswa ON mengajar.siswa_id = siswa.id
        WHERE mengajar.id = '$id'";

$query = mysqli_query($koneksi, $sql);

if (!$query) {
    exit("Data mengajar gagal dibaca.");
}

$data = mysqli_fetch_assoc($query);

if (!$data) {
    exit("Data mengajar tidak ditemukan.");
}

$sql_mapel = "SELECT mapel.id, mapel.nama_mapel,
                    guru.nama AS nama_guru
             FROM mapel
             JOIN guru ON mapel.guru_id = guru.id
             ORDER BY mapel.nama_mapel";

$result_mapel = mysqli_query($koneksi, $sql_mapel);

// DIUBAH: Kelas ditampilkan sekali seperti form tambah.
$sql_kelas = "SELECT DISTINCT kelas
             FROM siswa
             WHERE kelas IS NOT NULL AND kelas <> ''
             ORDER BY kelas";

$result_kelas = mysqli_query($koneksi, $sql_kelas);

if (!$result_mapel || !$result_kelas) {
    exit("Pilihan mapel atau kelas gagal dibaca.");
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

                        <input type="hidden"
                               name="id"
                               value="<?= $data['id'] ?>">

                        <label>Mata Pelajaran dan Guru:</label>

                        <select name="mapel_id" class="form-control" required>
                            <?php
                            while ($mapel = mysqli_fetch_assoc($result_mapel)) {
                            ?>
                                <option value="<?= $mapel['id'] ?>"
                                    <?php
                                    // DIUBAH: Memakai if biasa agar mudah dibaca.
                                    if ($mapel['id'] == $data['mapel_id']) {
                                        echo 'selected';
                                    }
                                    ?>
                                >
                                    <?= htmlspecialchars(
                                        $mapel['nama_mapel'] . ' - ' .
                                        $mapel['nama_guru']
                                    ) ?>
                                </option>
                            <?php } ?>
                        </select>

                        <br>

                        <!-- DIUBAH: Memilih kelas, bukan siswa. -->
                        <label>Kelas:</label>

                        <select name="kelas" class="form-control" required>
                            <?php
                            while ($kelas = mysqli_fetch_assoc($result_kelas)) {
                            ?>
                                <option value="<?= htmlspecialchars($kelas['kelas']) ?>"
                                    <?php
                                    if ($kelas['kelas'] == $data['kelas']) {
                                        echo 'selected';
                                    }
                                    ?>
                                >
                                    <?= htmlspecialchars($kelas['kelas']) ?>
                                </option>
                            <?php } ?>
                        </select>

                        <br>

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