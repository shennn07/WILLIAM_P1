<?php
require_once 'database.php';

$sql = 
"SELECT mapel.*, guru.nama AS nama_guru
FROM mapel
LEFT JOIN guru ON mapel.guru_id = guru.id
order by mapel.id desc";

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
    <title>Daftar Mapel</title>

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
                    <a class="nav-link active" aria-current="page" href="daftar_mapel.php">Daftar mapel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="form_mapel.php">Form Mapel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="daftar_user.php">Daftar user</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5">
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h2 class="h4 mb-1">Daftar Mapel</h2>
                    <p class="text-secondary mb-0">Data mata pelajaran yang terdaftar di sistem.</p>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Kode Mapel</th>
                            <th>Nama Mapel</th>
                            <th>Nama Guru</th>
                            <th>Kelola</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($data)) { ?>

                            <?php foreach ($data as $item) { ?>
                                <tr>
                                    <td><?php echo $item["id"]; ?></td>
                                    <td><?php echo $item["kode_mapel"]; ?></td>
                                    <td><?php echo $item["nama_mapel"]; ?></td>
                                    <td><?php echo $item["nama_guru"]; ?></td>
                                    <td>
                                        <a href="form_edit_mapel.php?id=<?php echo $item["id"]; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <a href="proses_hapus_mapel.php?id=<?php echo $item["id"]; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus mapel ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php } else { ?>

                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">
                                    Belum ada data mapel.
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

