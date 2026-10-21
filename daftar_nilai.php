<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: form_login.php");
    exit;

} elseif (($_SESSION['role'] ?? '') !== 'guru') {
    http_response_code(403);
    exit("Akses ditolak. Halaman ini hanya untuk guru.");
}

if (empty($_SESSION['csrf_nilai'])) {
    $_SESSION['csrf_nilai'] = bin2hex(random_bytes(32));
}

// Kelas dikirim dari tombol Lihat pada daftar_kelas.php.
if (!isset($_GET['kelas']) || !is_string($_GET['kelas'])) {
    header("Location: daftar_kelas.php");
    exit;
}

$kelas = $_GET['kelas'];

if (trim($kelas) === '') {
    header("Location: daftar_kelas.php");
    exit;
}

$user_id = (int) $_SESSION['id'];
$kelas_sql = mysqli_real_escape_string($koneksi, $kelas);

// LEFT JOIN membuat siswa yang belum memiliki nilai tetap tampil.
// DISTINCT mencegah baris penempatan mengajar yang sama tampil berulang.
$sql = "SELECT DISTINCT
            siswa.id AS id_siswa,
            siswa.nama AS nama_siswa,
            mapel.id AS id_mapel,
            mapel.nama_mapel,
            nilai_rapor.id AS id_nilai,
            nilai_rapor.nilai_kkm,
            nilai_rapor.nilai_pengetahuan,
            nilai_rapor.nilai_keterampilan,
            nilai_rapor.keterangan
        FROM mengajar
        JOIN siswa ON mengajar.siswa_id = siswa.id
        JOIN mapel ON mengajar.mapel_id = mapel.id
        JOIN guru ON mapel.guru_id = guru.id
        LEFT JOIN nilai_rapor
            ON nilai_rapor.siswa_id = siswa.id
            AND nilai_rapor.mapel_id = mapel.id
        WHERE guru.user_id = '$user_id'
          AND siswa.kelas = '$kelas_sql'
        ORDER BY mapel.nama_mapel, siswa.nama";

$result = mysqli_query($koneksi, $sql);

if (!$result) {
    http_response_code(500);
    exit("Data nilai siswa gagal dibaca.");
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
    <title>Daftar Nilai Siswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="daftar_guru.php">
            SIACAD SMK
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="daftar_guru.php">
                        Daftar Guru
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active"
                       aria-current="page"
                       href="daftar_kelas.php">
                        Nilai Siswa
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

                <div class="dropdown-menu dropdown-menu-end"
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
            <div class="d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="h4 mb-1">Daftar Nilai Siswa</h2>

                    <p class="text-secondary mb-0">
                        Kelas:
                        <strong>
                            <?php echo htmlspecialchars($kelas); ?>
                        </strong>
                    </p>
                </div>

                <a href="daftar_kelas.php" class="btn btn-secondary">
                    Kembali
                </a>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Mapel</th>
                            <th>KKM</th>
                            <th>Pengetahuan</th>
                            <th>Keterampilan</th>
                            <th>Nilai Akhir</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($data)) { ?>

                            <?php
                            $no = 1;

                            foreach ($data as $item) {
                                // Nilai awal untuk tampilan KKM.
                                $kkm = 75;

                                if ($item['nilai_kkm'] !== null) {
                                    $kkm = $item['nilai_kkm'];
                                }

                                // Nilai akhir dihitung jika kedua nilai tersedia.
                                $nilai_akhir = '-';

                                if (
                                    $item['nilai_pengetahuan'] !== null &&
                                    $item['nilai_keterampilan'] !== null
                                ) {
                                    $nilai_akhir = round(
                                        (
                                            $item['nilai_pengetahuan'] +
                                            $item['nilai_keterampilan']
                                        ) / 2
                                    );
                                }
                            ?>

                                <tr>
                                    <td>
                                        <?php echo $no++; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($item['nama_siswa']); ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($item['nama_mapel']); ?>
                                    </td>

                                    <td>
                                        <?php echo $kkm; ?>
                                    </td>

                                    <td>
                                        <?php echo $item['nilai_pengetahuan'] ?? '-'; ?>
                                    </td>

                                    <td>
                                        <?php echo $item['nilai_keterampilan'] ?? '-'; ?>
                                    </td>

                                    <td>
                                        <?php echo $nilai_akhir; ?>
                                    </td>

                                    <td>
                                        <?php
                                        if (
                                            $item['keterangan'] !== null &&
                                            $item['keterangan'] !== ''
                                        ) {
                                            echo htmlspecialchars($item['keterangan']);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>

                                    <td class="text-nowrap">
                                        <?php if ($item['id_nilai'] === null) { ?>

                                            <a href="form_tambah_nilai.php?siswa_id=<?php echo $item['id_siswa']; ?>&amp;mapel_id=<?php echo $item['id_mapel']; ?>"
                                               class="btn btn-sm btn-primary">
                                                Tambah
                                            </a>

                                        <?php } else { ?>

                                            <a href="form_edit_nilai.php?id=<?php echo $item['id_nilai']; ?>"
                                               class="btn btn-sm btn-warning">
                                                Edit
                                            </a>

                                            <form action="proses_hapus_nilai.php"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Hapus nilai siswa untuk mapel ini?')">

                                                <input type="hidden"
                                                   name="id"
                                                   value="<?php echo $item['id_nilai']; ?>">

                                                <input type="hidden"
                                                   name="csrf_nilai"
                                                   value="<?php echo htmlspecialchars($_SESSION['csrf_nilai']); ?>">

                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    Hapus
                                                </button>
                                            </form>

                                        <?php } ?>
                                    </td>
                                </tr>

                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="9"
                                    class="text-center text-secondary py-4">
                                    Tidak ada siswa yang ditugaskan kepada Anda
                                    pada kelas ini.
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