<?php
require_once 'database.php';

$sql = 
"SELECT users.*,
COALESCE(siswa.nama, guru.nama) AS nama_pengguna,
mapel.nama_mapel AS mapel_guru

FROM users
LEFT JOIN siswa ON users.id = siswa.user_id
LEFT JOIN guru ON users.id = guru.user_id
LEFT JOIN mapel ON guru.id = mapel.guru_id
order by users.id desc
";

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
                    <a class="nav-link active" aria-current="page" href="daftar_user.php">Daftar User</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="form.php">Form</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="daftar_mapel.php">Daftar mapel</a>
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
                            <th>Role</th>
                            <th>Password</th>
                            <th>Nama Pengguna</th>
                            <th>Mapel</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($data)) { ?>

                            <?php foreach ($data as $item) { ?>
                                <tr>
                                    <td><?php echo $item["id"]; ?></td>
                                    <td><?php echo $item["username"]; ?></td>

                                    <td>
                                        <span class="badge text-bg-secondary">
                                            <?php echo $item["role"]; ?>
                                        </span>
                                    </td>

                                    <td><?php echo $item["password"]; ?></td>

                                    <td>
                                        <?php
                                        echo $item["nama_pengguna"]
                                            ? $item["nama_pengguna"]
                                            : '<span class="text-secondary fst-italic">Belum ada profil</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo $item["mapel_guru"] ?? '<span class="text-secondary fst-italic">Belum ada mapel</span>'; ?>
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
