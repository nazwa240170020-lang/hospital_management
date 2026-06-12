<?php
session_start();
include "../config/koneksi.php";

/* ===================== CEK SESSION ===================== */
$id_user = $_SESSION['id_user'] ?? null;
$role    = $_SESSION['role'] ?? '';

if (!$id_user || $role != 'pasien') {
    header("Location: ../login.php");
    exit();
}

/* ===================== DATA PASIEN ===================== */
$pasien = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT * FROM pasien WHERE id_user='$id_user'"
));

if (!$pasien) {
    echo "<script>alert('Lengkapi profil pasien terlebih dahulu');location='daftar_pasien.php';</script>";
    exit();
}

$kode_pasien = $pasien['kode_pasien'];
$nama_tampil = htmlspecialchars($pasien['nama_pasien'] ?? 'Pasien');

/* ===================== READ RIWAYAT RAWAT ===================== */
$riwayat = mysqli_query($koneksi,"
    SELECT 
        r.*,
        d.nama_dokter,
        d.spesialis,
        k.tipe_kamar,
        k.nomor_kamar,
        (DATEDIFF(IFNULL(r.tanggal_keluar, CURDATE()), r.tanggal_masuk)+1) AS lama_inap,
        (k.harga * (DATEDIFF(IFNULL(r.tanggal_keluar, CURDATE()), r.tanggal_masuk)+1)) AS harga_kamar,
        GROUP_CONCAT(o.nama_obat SEPARATOR ', ') AS nama_obat,
        SUM(ro.subtotal) AS total_harga_obat
    FROM rawat_inap r
    JOIN dokter d ON r.nip=d.nip
    JOIN kamar k ON r.nomor_kamar=k.nomor_kamar
    LEFT JOIN resep_obat ro ON r.kode_rawat=ro.kode_rawat
    LEFT JOIN obat o ON ro.kode_obat=o.kode_obat
    WHERE r.kode_pasien='$kode_pasien'
    GROUP BY r.kode_rawat
    ORDER BY r.tanggal_masuk DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Riwayat Rawat Inap</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

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

/* ===== SIDEBAR (SAMA DENGAN PROFIL) ===== */
.sidebar{
    width:240px;
    background:#FF8C42;
    backdrop-filter:blur(14px);
    min-height:calc(100vh - 40px);
    margin:20px;
    padding:28px 18px;
    border-radius:22px;
    box-shadow:0 18px 40px rgba(0,0,0,.28);
}
.sidebar .logo{
    color:#fff;
    font-weight:700;
    font-size:1.05rem;
    margin-bottom:36px;
    text-align:center;
}
.sidebar a{
    display:flex;
    align-items:center;
    gap:12px;
    color:#fff;
    text-decoration:none;
    padding:13px 14px;
    margin-bottom:8px;
    border-radius:14px;
    font-weight:600;
}
.sidebar a:hover,
.sidebar a.active{
    background:rgba(255,255,255,.18);
}

/* ===== MAIN ===== */
.main{
    flex:1;
    padding:30px;
    margin-left:10px;
}
.header{
    text-align:center;
    color:#fff;
    margin-bottom:25px;
}
.content{
    background:rgba(255,255,255,.82);
    backdrop-filter:blur(16px);
    border-radius:22px;
    padding:25px;
    box-shadow:0 20px 40px rgba(0,0,0,.25);
}

/* TABLE */
.table-box{
    background:#fff;
    border-radius:18px;
    overflow-x:auto;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:12px;
    border-bottom:1px solid #ddd;
    font-size:.9rem;
}
th{
    background:rgba(255,140,66,.25);
}
.print-btn{
    background:#FF8C42;
    color:#fff;
    border:none;
    border-radius:8px;
    padding:6px 12px;
    font-size:.8rem;
    cursor:pointer;
}
@media print{
    .sidebar,.header,.print-btn{display:none}
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">👤 <?= $nama_tampil ?></div>
    <a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
    <a href="daftar_pasien.php"><i class="fas fa-user"></i>Profil</a>
    <a href="daftar_rawat.php"><i class="fas fa-notes-medical"></i>Rawat Inap</a>
    <a class="active"><i class="fas fa-clock-rotate-left"></i>Riwayat</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<!-- MAIN -->
<div class="main">
<div class="header">
    <h1>Riwayat Rawat Inap</h1>
    <p><?= $nama_tampil ?></p>
</div>

<div class="content">
<div class="table-box">
<table>
<tr>
<th>No</th>
<th>Dokter</th>
<th>Kamar</th>
<th>Harga Kamar</th>
<th>Resep Obat</th>
<th>Harga Obat</th>
<th>Biaya Pemeriksaan</th>
<th>Total</th>
<th>Aksi</th>
</tr>

<?php $no=1; while($r=mysqli_fetch_assoc($riwayat)):
$harga_kamar=$r['harga_kamar']??0;
$harga_obat=$r['total_harga_obat']??0;
$biaya=$r['biaya_pemeriksaan']??0;
$total=$harga_kamar+$harga_obat+$biaya;
?>
<tr>
<td><?= $no++ ?></td>
<td><?= $r['nama_dokter'] ?></td>
<td><?= $r['tipe_kamar'].' - '.$r['nomor_kamar'] ?></td>
<td>Rp <?= number_format($harga_kamar,0,',','.') ?></td>
<td><?= $r['nama_obat'] ?: '-' ?></td>
<td>Rp <?= number_format($harga_obat,0,',','.') ?></td>
<td>Rp <?= number_format($biaya,0,',','.') ?></td>
<td><b>Rp <?= number_format($total,0,',','.') ?></b></td>
<td>
<a class="print-btn" href="print_kuitansi.php?kode_rawat=<?= $r['kode_rawat'] ?>" target="_blank">
<i class="fas fa-print"></i> Print
</a>
</td>
</tr>
<?php endwhile ?>
</table>
</div>
</div>
</div>

</body>
</html>
