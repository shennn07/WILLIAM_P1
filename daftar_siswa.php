<?php
require_once 'database.php';
session_start();

$sql = 
"SELECT
    siswa.id AS id_siswa,
    users.*,
    siswa.nama,
    siswa.nis,
    siswa.kelas
FROM siswa
LEFT JOIN users ON siswa.user_id = users.id
ORDER BY siswa.id DESC"
;
$result = mysqli_query($koneksi, $sql);

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
    <title>Daftar User</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="#">SIACAD SMK</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="daftar_siswa.php">Daftar siswa</a>
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

    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
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
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h2 class="h4 mb-1">Daftar User</h2>
                    <p class="text-secondary mb-0">Data akun pengguna yang terdaftar di sistem.</p>
                </div>


            </div>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>nama</th>
                            <th>nis</th>
                            <th>kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($data)) { ?>

                            <?php foreach ($data as $item) { ?>
                                <tr>
                                    <td><?php echo $item["id_siswa"]; ?></td>
                                    <td><?php echo $item["username"]; ?></td>
                                    <td><?php echo $item["nama"]; ?></td>
                                    <td><?php echo $item["nis"]; ?></td>
                                    <td>
                                        <?php echo $item["kelas"] ?? '<span class="text-secondary fst-italic">Belum ada kelas</span>'; ?>
                                    </td>
                                    <td>
                                        <a href="form_edit_user.php?id=<?php echo $item["id"]; ?>&role=<?php echo $item["role"]; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <a href="proses_hapus_user.php?id=<?php echo $item["id"]; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    Belum ada data user.
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
