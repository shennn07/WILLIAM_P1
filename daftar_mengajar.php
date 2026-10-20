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

// MIN mengambil ID mengajar terkecil sebagai perwakilan kelompok.
// Kolom lainnya dimasukkan ke GROUP BY agar query valid.
$sql = "SELECT
            MIN(mengajar.id) AS id_mengajar,
            guru.id AS id_guru,
            guru.nama AS nama_guru,
            mapel.id AS id_mapel,
            mapel.kode_mapel,
            mapel.nama_mapel,
            siswa.kelas
        FROM mengajar
        JOIN mapel ON mengajar.mapel_id = mapel.id
        JOIN guru ON mapel.guru_id = guru.id
        JOIN siswa ON mengajar.siswa_id = siswa.id
        GROUP BY
            guru.id,
            guru.nama,
            mapel.id,
            mapel.kode_mapel,
            mapel.nama_mapel,
            siswa.kelas
        ORDER BY siswa.kelas, mapel.nama_mapel";

$result = mysqli_query($koneksi, $sql);

if (!$result) {
    http_response_code(500);
    exit("Data mengajar gagal dibaca.");
}

$data = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Mengajar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="#">SIACAD SMK</a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active"
                       aria-current="page"
                       href="daftar_mengajar.php">
                        Daftar mengajar
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="form_tambah_mengajar.php">
                        Form
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="daftar_mapel.php">
                        Daftar mapel
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="daftar_user.php">
                        Daftar user
                    </a>
                </li>
            </ul>

            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle"
                        type="button"
                        id="dropdownMenuButton"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                    AKUN
                </button>

                <div class="dropdown-menu"
                     aria-labelledby="dropdownMenuButton">

                    <span class="dropdown-item-text">
                        user:
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span>

                    <span class="dropdown-item-text">
                        Role:
                        <?php echo htmlspecialchars($_SESSION['role']); ?>
                    </span>

                    <span class="dropdown-item-text">
                        <a href="proses_logout.php"
                           class="btn btn-sm btn-outline-danger mt-2">
                            Logout
                        </a>
                    </span>
                </div>
            </div>
        </div>
    </div>
</nav>

<main class="container py-5">
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h2 class="h4 mb-1">Daftar Mengajar</h2>
                    <p class="text-secondary mb-0">
                        Data guru dan mata pelajaran pada setiap kelas.
                    </p>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <!-- DIUBAH: No kembali menjadi ID Mengajar. -->
                            <th>ID Mengajar</th>
                            <th>nama guru</th>
                            <th>nama mapel</th>
                            <th>kode mapel</th>
                            <th>kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($data)) { ?>

                            <!-- DIUBAH: Tidak menggunakan $no lagi. -->
                            <?php foreach ($data as $item) { ?>
                                <tr>
                                    <!-- DIUBAH: Menampilkan ID hasil MIN(). -->
                                    <td>
                                        <?php echo $item["id_mengajar"]; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($item["nama_guru"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($item["nama_mapel"]); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($item["kode_mapel"]); ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo isset($item["kelas"])
                                            ? htmlspecialchars($item["kelas"])
                                            : '<span class="text-secondary fst-italic">-</span>';
                                        ?>
                                    </td>

                                    <td>
                                        <!-- DIUBAH: Mengirim id seperti versi awal. -->
                                        <a href="form_edit_mengajar.php?id=<?php echo $item['id_mengajar']; ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>

                                        <!-- DIUBAH: Mengirim id dan memberi konfirmasi hapus. -->
                                        <a href="proses_hapus_mengajar.php?id=<?php echo $item['id_mengajar']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Hapus satu data mengajar dengan ID ini?')">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="6"
                                    class="text-center text-secondary py-4">
                                    Belum ada data mengajar.
                                </td>
                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>