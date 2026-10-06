<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("location: form_login.php");
    exit;
}

$role = $_SESSION['role'] ?? '';

switch ($role) {
    case 'admin':
        header("location: daftar_user.php");
        exit;
    case 'siswa':
        header("location: daftar_siswa.php");
        exit;
    case 'guru':
        header("location: daftar_guru.php");
        exit;
    default:
        session_destroy();
        header("location: form_login.php");
        exit;
}
?>