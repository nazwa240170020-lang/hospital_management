<?php
session_start();
include "../config/koneksi.php";

/* ===================== CEK SESSION ===================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'dokter') {
    header("Location: ../login.php");
    exit();
}

/* ===================== DATA DOKTER ===================== */
$id_user = $_SESSION['id_user'];
$dokter = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT nip, nama_dokter FROM dokter WHERE id_user='$id_user'"
));

if(!$dokter){
    header("Location: daftar_dokter.php");
    exit();
}

$nip = $dokter['nip'];
$nama_dokter = htmlspecialchars($dokter['nama_dokter']);

/* ===================== STATISTIK ===================== */
$total = mysqli_fetch_assoc(mysqli_query(
    $koneksi,"SELECT COUNT(*) total FROM rawat_inap WHERE nip='$nip'"
))['total'] ?? 0;

$aktif = mysqli_fetch_assoc(mysqli_query(
    $koneksi,"SELECT COUNT(*) total FROM rawat_inap 
              WHERE nip='$nip' 
              AND (tanggal_keluar IS NULL OR tanggal_keluar='')"
))['total'] ?? 0;

$selesai = $total - $aktif;

$baru = mysqli_fetch_assoc(mysqli_query(
    $koneksi,"SELECT COUNT(*) total FROM rawat_inap 
              WHERE nip='$nip' AND DATE(tanggal_masuk)=CURDATE()"
))['total'] ?? 0;

$warning = mysqli_num_rows(mysqli_query(
    $koneksi,"SELECT * FROM rawat_inap 
              WHERE nip='$nip' 
              AND (diagnosa IS NULL OR diagnosa='')"
));

$pasien_terakhir = mysqli_query($koneksi,"
    SELECT r.kode_rawat, p.nama_pasien, r.tanggal_masuk
    FROM rawat_inap r
    JOIN pasien p ON r.kode_pasien=p.kode_pasien
    WHERE r.nip='$nip'
    ORDER BY r.tanggal_masuk DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Dokter</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<style>
*{box-sizing:border-box}
body{
    margin:0;font-family:'Segoe UI',sans-serif;
    background:
    linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)),
    url("../assets/RS_Pondok_Indah_-_Pondok_Indah.jpg") center/cover fixed;
    display:flex;
}

/* SIDEBAR */
.sidebar{
    width:250px;background:#FF8C42;min-height:100vh
}
.sidebar .logo{
    color:#fff;text-align:center;font-weight:700;
    padding:20px;border-bottom:1px solid rgba(255,255,255,.3)
}
.sidebar a{
    display:flex;align-items:center;gap:10px;
    padding:15px 20px;color:#fff;text-decoration:none;font-weight:600
}
.sidebar a:hover,.sidebar a.active{background:#E67323}

/* MAIN */
.main{flex:1;padding:20px}
.content{
    background:rgba(255,242,224,.95);
    padding:25px;border-radius:18px;
    box-shadow:0 10px 30px rgba(255,140,66,.35)
}
h1{text-align:center;color:#B34D00}

/* WARNING */
.warning{
    background:#FFD54F;
    padding:14px;
    border-radius:16px;
    margin-bottom:18px;
    font-weight:600
}

/* ACTION */
.actions{
    display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px
}
.actions a{
    flex:1;min-width:180px;
    text-align:center;padding:12px;
    background:#FF8C42;color:#fff;
    border-radius:30px;text-decoration:none;font-weight:700
}

/* CARDS */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:18px;margin-bottom:25px
}
.card{
    background:rgba(255,255,255,.38);
    backdrop-filter:blur(14px);
    padding:24px;border-radius:22px;
    text-align:center;cursor:pointer;
    border:1px solid rgba(255,255,255,.25);
    box-shadow:0 10px 28px rgba(0,0,0,.2);
    transition:.35s
}
.card:hover{
    transform:translateY(-6px) scale(1.02);
    box-shadow:0 16px 36px rgba(0,0,0,.32);
}
.card i{
    font-size:2.1rem;color:#B34D00;margin-bottom:8px
}
.card .number{
    font-size:2.6rem;font-weight:800;color:#B34D00
}
.card span{font-size:.9rem;opacity:.85}
.badge{
    margin-top:6px;display:inline-block;
    padding:4px 12px;border-radius:20px;
    background:#E53935;color:#fff;font-size:.75rem
}

/* LIST */
.list{
    background:#fff;padding:18px;border-radius:16px
}
.list table{width:100%;border-collapse:collapse}
.list td{padding:10px;border-bottom:1px solid #eee}
.small{font-size:.8rem;color:#777}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">👨‍⚕️ <?= $nama_dokter ?></div>
    <a class="active"><i class="fas fa-home"></i>Dashboard</a>
    <a href="daftar_dokter.php"><i class="fas fa-user"></i>Profil</a>
    <a href="data_pasien.php"><i class="fas fa-users"></i>Data Pasien</a>
    <a href="input_diagnosa.php"><i class="fas fa-stethoscope"></i>Diagnosa</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<!-- MAIN -->
<div class="main">
<div class="content">
<h1>Dashboard Dokter</h1>

<?php if($warning>0): ?>
<div class="warning">⚠️ <?= $warning ?> pasien belum diisi diagnosa</div>
<?php endif; ?>

<div class="actions">
    <a href="input_diagnosa.php">➕ Input Diagnosa</a>
    <a href="data_pasien.php?filter=baru">🆕 Pasien Baru</a>
    <a href="print_kuitansi.php?all=1" target="_blank">🧾 Cetak Kuitansi</a>
</div>

<div class="cards">
    <div class="card" onclick="location.href='data_pasien.php?filter=aktif'">
        <i class="fas fa-procedures"></i>
        <div class="number"><?= $aktif ?></div>
        <span>Pasien Aktif</span>
    </div>

    <div class="card" onclick="location.href='data_pasien.php?filter=selesai'">
        <i class="fas fa-check-circle"></i>
        <div class="number"><?= $selesai ?></div>
        <span>Pasien Selesai</span>
    </div>

    <div class="card" onclick="location.href='data_pasien.php?filter=baru'">
        <i class="fas fa-user-plus"></i>
        <div class="number"><?= $baru ?></div>
        <span>Pasien Baru</span>
        <?php if($baru>0): ?><div class="badge">Hari Ini</div><?php endif; ?>
    </div>
</div>

<div class="list">
<h3>Pasien Terakhir</h3>
<table>
<?php while($p=mysqli_fetch_assoc($pasien_terakhir)): ?>
<tr>
<td>
<strong><?= $p['nama_pasien'] ?></strong><br>
<span class="small"><?= date('d M Y',strtotime($p['tanggal_masuk'])) ?></span>
</td>
<td align="right">
<a href="input_diagnosa.php?kode_rawat=<?= $p['kode_rawat'] ?>">📝</a>
<a href="print_kuitansi.php?kode_rawat=<?= $p['kode_rawat'] ?>" target="_blank">🧾</a>
</td>
</tr>
<?php endwhile ?>
</table>
</div>

</div>
</div>

</body>
</html>
