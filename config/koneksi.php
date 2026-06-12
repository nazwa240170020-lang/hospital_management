<?php
$koneksi = mysqli_connect("localhost", "root", "", "db_rumahsakit");

if (!$koneksi) {
    die("Database gagal terhubung: " . mysqli_connect_error());
}
?>