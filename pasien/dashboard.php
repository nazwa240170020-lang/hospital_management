<!-- DASHBOARD PASIEN - FINAL AESTHETIC + NOTIF -->
<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pasien') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'] ?? 0;

/* ===== DATA PASIEN ===== */
$qPasien = mysqli_query($koneksi,"
    SELECT kode_pasien, nama_pasien
    FROM pasien
    WHERE id_user='$id_user'
");
if (!$pasien) {
    header("Location: daftar_pasien.php?status=belum");
    exit();
}


$kode_pasien = $pasien['kode_pasien'];
$nama_tampil = htmlspecialchars($pasien['nama_pasien']);

/* ===== GREETING ===== */
date_default_timezone_set("Asia/Jakarta");
$jam = (int)date("H");
if ($jam >= 5 && $jam < 11) { $salam="Selamat pagi"; $ikon="☀️"; }
elseif ($jam < 15) { $salam="Selamat siang"; $ikon="🌤️"; }
elseif ($jam < 18) { $salam="Selamat sore"; $ikon="🌇"; }
else { $salam="Selamat malam"; $ikon="🌙"; }

/* ===== HITUNG RAWAT ===== */
$qAktif = mysqli_query($koneksi,"
    SELECT COUNT(*) total FROM rawat_inap
    WHERE kode_pasien='$kode_pasien'
    AND (tanggal_keluar IS NULL OR tanggal_keluar='' OR tanggal_keluar='0000-00-00')
");
$aktif = mysqli_fetch_assoc($qAktif)['total'] ?? 0;

$qTotal = mysqli_query($koneksi,"
    SELECT COUNT(*) total FROM rawat_inap
    WHERE kode_pasien='$kode_pasien'
");
$total = mysqli_fetch_assoc($qTotal)['total'] ?? 0;

$selesai = $total - $aktif;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Pasien</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:
      linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.55)),
      url("../assets/RS_Pondok_Indah_-_Pondok_Indah.jpg") center/cover fixed;
    display:flex;
}

/* SIDEBAR */
.sidebar{
    width:240px;background:#FF8C42;
    min-height:calc(100vh - 40px);
    margin:20px;padding:28px 18px;
    border-radius:22px;
}
.sidebar .logo{color:#fff;font-weight:700;text-align:center;margin-bottom:36px}
.sidebar a{
    display:flex;align-items:center;gap:12px;
    color:#fff;text-decoration:none;
    padding:13px 14px;border-radius:14px;font-weight:600;
}
.sidebar a:hover,.sidebar a.active{background:rgba(255,255,255,.18)}

.main{flex:1;padding:30px}

/* HEADER */
.header{text-align:center;color:#fff;margin-bottom:25px}

/* CONTENT */
.content{
    background:rgba(255,255,255,.85);
    border-radius:22px;padding:26px;
}

/* CLOCK */
.clock{
    display:flex;justify-content:space-between;
    margin-bottom:14px;font-weight:600;color:#7A2E00;
}

/* NOTIF */
.notif{
    background:linear-gradient(135deg,#FF5252,#FF7043);
    color:#fff;padding:14px 18px;
    border-radius:16px;
    margin-bottom:18px;
    display:flex;align-items:center;gap:12px;
    font-weight:600;
    box-shadow:0 10px 25px rgba(0,0,0,.25);
}

/* CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(230px,1fr));
    gap:24px;
}
.card{
    background:linear-gradient(135deg,#FFE0B2,#FFB74D);
    border-radius:22px;padding:24px;
    text-align:center;cursor:pointer;
    box-shadow:0 15px 35px rgba(0,0,0,.22);
    transition:.35s ease;
}
.card:hover{transform:translateY(-8px) scale(1.03)}
.card i{font-size:2rem;color:#8A3A00}
.card h3{margin:8px 0;font-weight:700;color:#5C2500}
.card .number{font-size:2.6rem;font-weight:900;color:#7A2E00}

/* CHART */
.chart-box{
    margin-top:28px;
    padding:22px;
    background:#fff;
    border-radius:22px;
    box-shadow:0 12px 30px rgba(0,0,0,.18);
    display:flex;
    justify-content:center;
}
.chart-wrapper{
    width:320px; /* UKURAN SEDANG */
}
</style>
</head>

<body>

<div class="sidebar">
    <div class="logo">👤 <?= $nama_tampil ?></div>
    <a class="active"><i class="fas fa-home"></i>Dashboard</a>
    <a href="daftar_pasien.php"><i class="fas fa-user"></i>Profil</a>
    <a href="daftar_rawat.php"><i class="fas fa-notes-medical"></i>Rawat Inap</a>
    <a href="riwayat_rawat.php"><i class="fas fa-clock-rotate-left"></i>Riwayat</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<div class="main">
    <div class="header">
        <h1><?= $salam ?> <?= $ikon ?> <?= $nama_tampil ?></h1>
        <p>Dashboard Pasien Bukit Indah Hospital</p>
    </div>

    <div class="content">

        <div class="clock">
            <div>📅 <?= date('l, d F Y') ?></div>
            <div id="clock"></div>
        </div>

        <?php if($aktif > 0): ?>
        <div class="notif">
            <i class="fas fa-bell"></i>
            Anda sedang menjalani <b><?= $aktif ?></b> rawat inap aktif
        </div>
        <?php endif; ?>

        <div class="cards">
            <div class="card" onclick="location.href='daftar_rawat.php?filter=aktif'">
                <i class="fas fa-procedures"></i>
                <h3>Rawat Aktif</h3>
                <div class="number"><?= $aktif ?></div>
            </div>
            <div class="card" onclick="location.href='daftar_rawat.php'">
                <i class="fas fa-file-medical"></i>
                <h3>Total Rawat</h3>
                <div class="number"><?= $total ?></div>
            </div>
            <div class="card" onclick="location.href='daftar_rawat.php?filter=selesai'">
                <i class="fas fa-check-circle"></i>
                <h3>Selesai</h3>
                <div class="number"><?= $selesai ?></div>
            </div>
        </div>

        <div class="chart-box">
            <div class="chart-wrapper">
                <canvas id="rawatChart"></canvas>
            </div>
        </div>

    </div>
</div>

<script>
/* JAM */
function updateClock(){
    document.getElementById('clock').innerHTML =
        "🕒 " + new Date().toLocaleTimeString('id-ID');
}
setInterval(updateClock,1000);updateClock();

/* PIE */
new Chart(document.getElementById('rawatChart'),{
    type:'pie',
    data:{
        labels:['Rawat Aktif','Selesai'],
        datasets:[{
            data:[<?= $aktif ?>,<?= $selesai ?>],
            backgroundColor:['#FF8C42','#4CAF50']
        }]
    },
    options:{
        responsive:true,
        plugins:{legend:{position:'bottom'}}
    }
});
</script>

</body>
</html>
