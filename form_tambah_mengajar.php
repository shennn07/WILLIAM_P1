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
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Tambah Mengajar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="daftar_mengajar.php">
            SIACAD SMK
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="daftar_mengajar.php">
                        Daftar Mengajar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="form_mengajar.php">
                        Form Mengajar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="daftar_mapel.php">
                        Daftar Mapel
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-7 col-xl-6">

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white p-4">
                    <h1 class="h4 mb-1">Form Tambah Mengajar</h1>
                </div>

                <div class="card-body p-4">
                    <form action="proses_tambah_mengajar.php" method="POST">

                        <div class="border rounded-3 bg-light p-3 p-md-4 mb-4">
                            <h2 class="h5 mb-1">Data Mengajar</h2>
                            <p class="text-secondary small mb-4">
                                Pilih mata pelajaran dan kelas.
                            </p>

                            <div class="mb-3">
                                <label for="mapel_id" class="form-label">
                                    Mata Pelajaran dan Guru
                                </label>

                                <select name="mapel_id" id="mapel_id"
                                        class="form-select" required>
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
                                    ?>
                                        <option value="<?php echo $mapel['id']; ?>">
                                            <?php
                                            echo htmlspecialchars(
                                                $mapel['nama_mapel'] . ' - ' . $mapel['nama_guru']
                                            );
                                            ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="kelas" class="form-label">
                                    Kelas
                                </label>

                                <select name="kelas" id="kelas"
                                        class="form-select" required>
                                    <option value="">Pilih Kelas</option>

                                    <?php
                                    $kelas_query = "
                                        SELECT DISTINCT kelas
                                        FROM siswa
                                        WHERE kelas IS NOT NULL AND kelas <> ''
                                        ORDER BY kelas
                                    ";

                                    $kelas_result = mysqli_query($koneksi, $kelas_query);

                                    while ($siswa = mysqli_fetch_assoc($kelas_result)) {
                                    ?>
                                        <option value="<?php echo htmlspecialchars($siswa['kelas']); ?>">
                                            <?php echo htmlspecialchars($siswa['kelas']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <button type="submit" name="kirim" class="btn btn-primary">
                            Simpan Data
                        </button>

                        <a href="daftar_mengajar.php" class="btn btn-secondary">
                            Kembali
                        </a>

                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>