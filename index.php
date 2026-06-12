<?php
session_start();
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['role'] == 'dokter') {
        header("Location: dokter/dashboard.php");
    } elseif ($_SESSION['role'] == 'pasien') {
        header("Location: pasien/dashboard.php");
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Hospital Management System</title>
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

/* ULTRA SMOOTH RUNNING TEXT */
.running-text-wrapper {
  position: absolute;
  top: 5%;
  width: 100%;
  overflow: hidden;
}

.running-text-inner {
  display: inline-block;
  white-space: nowrap;
  color: white;
  font-size: 26px;
  font-weight: 600;
  text-shadow: 0 3px 8px rgba(0,0,0,0.8);
  padding-right: 100%; /* ruang agar looping mulus */
  animation: scroll 15s linear infinite;
}

.running-text-inner span {
  padding-right: 50px; /* jarak antar duplikasi */
}

@keyframes scroll {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(0); }
}

/* HERO TEXT dengan box shadow aesthetic */
.hero-text {
  position: absolute;
  top: 18%;
  width: 100%;
  text-align: center;
  color: #ffffff;
  background: rgba(255, 255, 255, 0.05); /* transparan lembut */
  padding: 20px 30px;
  border-radius: 15px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.6), 0 0 15px rgba(255,255,255,0.2);
}

.hero-text h2 {
  font-size: 36px;
  font-weight: 600;
  margin-bottom: 10px;
  text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
}

.hero-text p {
  font-size: 18px;
  text-shadow: 1px 1px 6px rgba(0,0,0,0.6);
}

/* LOGIN BOX */
.container {
  background: #ffffff;
  width: 400px;
  padding: 35px 30px;
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.3);
  text-align: center;
  z-index: 2;
}

.container h1 {
  font-size: 28px;
  color: #e76f51;
}

.subtitle {
  font-size: 14px;
  color: #666;
  margin-bottom: 30px;
}

.btn {
  display: block;
  width: 100%;
  background: #e76f51;
  color: white;
  padding: 12px;
  border-radius: 8px;
  text-decoration: none;
  margin-bottom: 14px;
  transition: 0.3s;
}

.btn:hover {
  background: #d65a3a;
  transform: translateY(-2px);
}
</style>
</head>
<body>

<!-- ULTRA SMOOTH RUNNING TEXT -->
<div class="running-text-wrapper">
  <div class="running-text-inner">
    <span>Selamat Datang di Hospital Bukit Indah, Sistem Rawat Inap Terintegrasi dan Terpercaya</span>
    <span>Selamat Datang di Hospital Bukit Indah, Sistem Rawat Inap Terintegrasi dan Terpercaya</span>
     <span>Selamat Datang di Hospital Bukit Indah, Sistem Rawat Inap Terintegrasi dan Terpercaya</span>
      <span>Selamat Datang di Hospital Bukit Indah, Sistem Rawat Inap Terintegrasi dan Terpercaya</span>
       <span>Selamat Datang di Hospital Bukit Indah, Sistem Rawat Inap Terintegrasi dan Terpercaya</span>
        <span>Selamat Datang di Hospital Bukit Indah, Sistem Rawat Inap Terintegrasi dan Terpercaya</span>
         <span>Selamat Datang di Hospital Bukit Indah, Sistem Rawat Inap Terintegrasi dan Terpercaya</span>

  </div>
</div>

<!-- HERO TEXT dengan box shadow aesthetic -->
<div class="hero-text">
  <h2>Selamat Datang di Bukit Indah Hospital</h2>
  <p>Sistem Informasi Rawat Inap</p>
</div>

<!-- LOGIN BOX -->
<div class="container">
  <h1>Selamat Datang</h1>
  <p class="subtitle">Hospital Bukit Indah Lhokseumawe</p>
  <a href="login.php" class="btn">Login</a>
  <a href="register.php" class="btn">Daftar</a>
</div>

</body>
</html>
