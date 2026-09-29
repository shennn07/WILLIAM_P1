<?php
$host     = "localhost";
$username = "root";
$pass = ""; 
$db = "db_project1";

$koneksi = mysqli_connect($host, $username, $pass, $db);

// if (!$koneksi) {
//     die("Koneksi ke database gagal: " . mysqli_connect_error());
// }elseif ($koneksi) {
//     echo "Koneksi ke database berhasil!"  . "<br>". "<br>";
// }

?>