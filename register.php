<?php
include "config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama       = $_POST['nama'];
    $email      = $_POST['email'];
    $alamat     = $_POST['alamat'];
    $no_telp    = $_POST['no_telp'];
    $username   = $_POST['username'];
    $password   = $_POST['password'];
    $role       = $_POST['role'];
    $tanggal    = date("Y-m-d H:i:s");

    // INSERT USERS
    mysqli_query($koneksi,"
        INSERT INTO users (nama, username, password, role, tanggal_daftar)
        VALUES ('$nama','$username','$password','$role','$tanggal')
    ");

    $id_user = mysqli_insert_id($koneksi);

    // JIKA ROLE PASIEN → INSERT KE TABEL PASIEN
    if ($role == 'pasien') {
        $kode_pasien = 'PSN'.time();

        mysqli_query($koneksi,"
            INSERT INTO pasien (id_user, kode_pasien, nama_pasien, alamat, no_telp)
            VALUES ('$id_user','$kode_pasien','$nama','$alamat','$no_telp')
        ");
    }

    echo "<script>alert('Registrasi berhasil!'); window.location='login.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Form Registrasi</title>

<!-- STYLE LANGSUNG (ANTI DITIMPA) -->
<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: Arial, sans-serif;
}

body {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  
  background:
    linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)),
    url("assets/RS_Pondok_Indah_-_Pondok_Indah.jpg");

  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

.container {
  background: white;
  width: 420px;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.25);
}

h1 {
  text-align: center;
  margin-bottom: 20px;
  color: #e76f51;
}

label {
  font-weight: bold;
  display: block;
  margin-top: 12px;
}

input, textarea, select {
  width: 100%;
  padding: 10px;
  margin-top: 5px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

textarea {
  resize: vertical;
}

input:focus,
textarea:focus,
select:focus {
  border-color: #e76f51;
  outline: none;
}

/* BUTTON */
button {
  width: 100%;
  margin-top: 20px;
  background: #e76f51;
  color: white;
  border: none;
  padding: 10px;
  font-size: 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
}

button:hover {
  background: #d65a3a;
}

.btn-batal {
  display: block;
  text-align: center;
  margin-top: 12px;
  background: #ccc;
  color: black;
  padding: 10px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
}

.btn-batal:hover {
  background: #999;
}
</style>
</head>

<body>

<div class="container">
    <h1>Form Registrasi</h1>

    <form method="POST">

        <label>Nama Lengkap</label>
        <input type="text" name="nama" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Alamat</label>
        <textarea name="alamat"></textarea>

        <label>No. Telepon</label>
        <input type="text" name="no_telp">

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Pilih Role</label>
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="dokter">Dokter</option>
            <option value="pasien">Pasien</option>
        </select>

        <button type="submit">Daftar</button>
        <a href="index.php" class="btn-batal">Batal</a>

    </form>
</div>

</body>
</html>