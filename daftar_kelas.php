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

// ID akun guru yang sedang login.
// Session ini berisi users.id, bukan guru.id.
$user_id = (int) $_SESSION['id'];

// Mengikuti JOIN pada daftar_mengajar.php.
// Dikelompokkan berdasarkan kelas saja agar setiap kelas tampil sekali.
$sql = "SELECT siswa.kelas
        FROM mengajar
        JOIN mapel ON mengajar.mapel_id = mapel.id
        JOIN guru ON mapel.guru_id = guru.id
        JOIN siswa ON mengajar.siswa_id = siswa.id
        WHERE guru.user_id = '$user_id'
          AND siswa.kelas IS NOT NULL
          AND TRIM(siswa.kelas) <> ''
        GROUP BY siswa.kelas
        ORDER BY siswa.kelas";

$result = mysqli_query($koneksi, $sql);

if (!$result) {
    http_response_code(500);
    exit("Data kelas gagal dibaca.");
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
    <title>Daftar Kelas</title>

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
            <h2 class="h4 mb-1">Daftar Kelas yang Diajar</h2>

            <p class="text-secondary mb-0">
                Pilih kelas untuk melihat nilai siswa.
            </p>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($data)) { ?>

                            <?php foreach ($data as $item) { ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($item['kelas']); ?>
                                    </td>

                                    <td>
                                        <a href="daftar_nilai.php?kelas=<?php echo urlencode($item['kelas']); ?>"
                                           class="btn btn-sm btn-primary">
                                            Lihat
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="2"
                                    class="text-center text-secondary py-4">
                                    Belum ada kelas yang ditugaskan kepada Anda.
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