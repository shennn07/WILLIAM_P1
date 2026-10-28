<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: form_login.php");
    exit;

} elseif (($_SESSION['role'] ?? '') !== 'siswa') {
    http_response_code(403);
    exit("Akses ditolak. Halaman ini hanya untuk siswa.");
}

// ID akun user yang sedang login (mengacu ke users.id).
$user_id = (int) $_SESSION['id'];

// 1. Ambil data profil siswa yang sedang login berdasarkan user_id.
$sql_siswa = "SELECT id, nama, nis, kelas
              FROM siswa
              WHERE user_id = '$user_id'
              LIMIT 1";

$result_siswa = mysqli_query($koneksi, $sql_siswa);

if (!$result_siswa) {
    http_response_code(500);
    exit("Data profil siswa gagal dibaca.");
}

$siswa = mysqli_fetch_assoc($result_siswa);

if (!$siswa) {
    exit("Profil siswa tidak ditemukan untuk akun ini.");
}

$siswa_id = (int) $siswa['id'];

// 2. Ambil daftar nilai per mapel untuk siswa yang sedang login.
// Mengikuti pola JOIN pada daftar_nilai.php.
$sql_nilai = "SELECT DISTINCT
                  mapel.id AS id_mapel,
                  mapel.nama_mapel,
                  nilai_rapor.nilai_kkm,
                  nilai_rapor.nilai_pengetahuan,
                  nilai_rapor.nilai_keterampilan
              FROM mengajar
              JOIN mapel ON mengajar.mapel_id = mapel.id
              LEFT JOIN nilai_rapor
                  ON nilai_rapor.siswa_id = mengajar.siswa_id
                  AND nilai_rapor.mapel_id = mapel.id
              WHERE mengajar.siswa_id = '$siswa_id'
              ORDER BY mapel.nama_mapel";

$result_nilai = mysqli_query($koneksi, $sql_nilai);

if (!$result_nilai) {
    http_response_code(500);
    exit("Data nilai siswa gagal dibaca.");
}

$data = [];
$total_rata = 0;
$jumlah_mapel_dinilai = 0;

if (mysqli_num_rows($result_nilai) > 0) {
    while ($row = mysqli_fetch_assoc($result_nilai)) {
        // Nilai default KKM sama seperti di daftar_nilai.php.
        $kkm = 75;
        if ($row['nilai_kkm'] !== null) {
            $kkm = $row['nilai_kkm'];
        }

        $rata_mapel = '-';
        $keterangan = '-';

        // Hitung rata-rata dan kelulusan jika nilai pengetahuan & keterampilan sudah diinput.
        if (
            $row['nilai_pengetahuan'] !== null &&
            $row['nilai_keterampilan'] !== null
        ) {
            $hitung_rata = ($row['nilai_pengetahuan'] + $row['nilai_keterampilan']) / 2;
            $rata_mapel = number_format($hitung_rata, 2);

            if ($hitung_rata >= $kkm) {
                $keterangan = 'Lulus';
            } else {
                $keterangan = 'Tidak Lulus';
            }

            $total_rata += $hitung_rata;
            $jumlah_mapel_dinilai++;
        }

        $row['kkm_tampil'] = $kkm;
        $row['rata_tampil'] = $rata_mapel;
        $row['keterangan_tampil'] = $keterangan;

        $data[] = $row;
    }
}

// 3. Hitung rata-rata keseluruhan dari semua mapel yang sudah dinilai.
$rata_keseluruhan = '0.00';
if ($jumlah_mapel_dinilai > 0) {
    $rata_keseluruhan = number_format($total_rata / $jumlah_mapel_dinilai, 2);
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nilai Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="daftar_siswa.php">SIACAD SMK</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="daftar_siswa.php">Daftar siswa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="daftar_nilai_siswa.php">Nilai Siswa</a>
                </li>
            </ul>
        </div>
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle"
                    type="button"
                    id="dropdownMenuButton"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                AKUN
            </button>

            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                <span class="dropdown-item-text">
                    user:
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>

                <span class="dropdown-item-text">
                    Role:
                    <?php echo htmlspecialchars($_SESSION['role']); ?>
                </span>
                <span class="dropdown-item-text">
                    <a href="proses_logout.php" class="btn btn-sm btn-outline-danger mt-2">Logout</a>
                </span>
            </div>
        </div>
    </div>
</nav>

<main class="container py-5">

    <!-- Bagian 1: Identitas Siswa -->
    <div class="text-center mb-4">
        <h1 class="h2 fw-normal mb-2">Nilai Siswa</h1>
        <h2 class="h4 fw-normal mb-2"><?php echo htmlspecialchars($siswa['nama']); ?></h2>
        <p class="mb-1">
            NIS: <strong><?php echo htmlspecialchars($siswa['nis']); ?></strong>
        </p>
        <p class="mb-0">
            Kelas: <strong><?php echo htmlspecialchars($siswa['kelas']); ?></strong>
        </p>
    </div>

    <!-- Bagian 2: Card Rata-rata Nilai Keseluruhan -->
    <div class="row justify-content-center mb-4">
        <div class="col-12 col-sm-8 col-md-5 col-lg-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="card-body">
                    <h3 class="h5 fw-normal mb-3">Rata-rata Nilai</h3>
                    <div class="display-5 fw-normal">
                        <?php echo $rata_keseluruhan; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian 3: Tabel Nilai Tiap Mapel -->
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white p-4">
            <h2 class="h4 mb-1">Daftar Nilai</h2>
            <p class="text-secondary mb-0">Data nilai mata pelajaran Anda.</p>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Mata Pelajaran</th>
                            <th>KKM</th>
                            <th>Pengetahuan</th>
                            <th>Keterampilan</th>
                            <th>Rata-rata</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($data)) { ?>

                            <?php
                            $no = 1;
                            foreach ($data as $item) {
                            ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($item['nama_mapel']); ?></td>
                                    <td><?php echo $item['kkm_tampil']; ?></td>
                                    <td><?php echo $item['nilai_pengetahuan'] ?? '-'; ?></td>
                                    <td><?php echo $item['nilai_keterampilan'] ?? '-'; ?></td>
                                    <td><strong><?php echo $item['rata_tampil']; ?></strong></td>
                                    <td><?php echo $item['keterangan_tampil']; ?></td>
                                </tr>
                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">
                                    Belum ada data mata pelajaran atau nilai untuk Anda.
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