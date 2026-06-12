<?php
session_start();
include "config/koneksi.php";

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['role'] == 'dokter') {
        header("Location: dokter/dashboard.php");
    } else {
        header("Location: pasien/dashboard.php");
    }
    exit();
}


if (isset($_POST['login'])) {

    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    $query = mysqli_query($koneksi,
        "SELECT * FROM users 
         WHERE username='$username' 
         AND password='$password'"
    );
if ($data = mysqli_fetch_assoc($query)) {

    // HAPUS SESSION LAMA TOTAL
    $_SESSION = [];

    // SET SESSION BARU
    $_SESSION['id_user'] = $data['id_user'];
    $_SESSION['nama']    = $data['nama'];
    $_SESSION['role']    = $data['role'];


        if ($data['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } elseif ($data['role'] == 'dokter') {
            header("Location: dokter/dashboard.php");
        } else {
            header("Location: pasien/dashboard.php");
        }
        exit();
    } else {
        echo "<script>alert('Username atau password salah!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login - Bukit Indah Hospital</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

body {
  height: 100vh;
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

/* ===== HERO TEXT ===== */
.hero-text {
  position: absolute;
  top: 12%;
  width: 100%;
  text-align: center;
  color: #fff;
  padding: 0 20px;
}

.hero-text h2 {
  font-size: 36px;
  font-weight: 600;
  margin-bottom: 10px;
  text-shadow: 0 4px 12px rgba(0,0,0,0.7);
}

.hero-text p {
  font-size: 18px;
  font-weight: 300;
  line-height: 1.6;
  text-shadow: 0 3px 10px rgba(0,0,0,0.7);
}

/* ===== LOGIN BOX ===== */
.container {
  background: #ffffff;
  width: 360px;
  padding: 30px 25px;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
  text-align: center;
  z-index: 2;
}

.container h1 {
  margin-bottom: 20px;
  color: #e76f51;
}

label {
  display: block;
  text-align: left;
  font-weight: 500;
  margin-top: 10px;
}

input {
  width: 100%;
  padding: 10px;
  margin-top: 5px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

input:focus {
  border-color: #e76f51;
  outline: none;
}

button {
  width: 100%;
  background: #e76f51;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 16px;
  transition: 0.3s;
}

button:hover {
  background: #d65a3a;
  transform: translateY(-2px);
}

p {
  margin-top: 15px;
  font-size: 14px;
}

a {
  color: #e76f51;
  font-weight: 600;
  text-decoration: none;
}
</style>
</head>
<body>

<!-- HERO TEXT -->
<div class="hero-text">
    <h2>Selamat Datang di Bukit Indah Hospital</h2>
    
</div>

<!-- LOGIN FORM -->
<div class="container">
    <h1>Login</h1>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" name="login">Login</button>
    </form>

    <p>Belum punya akun? <a href="register.php">Daftar</a></p>
</div>

</body>
</html>
