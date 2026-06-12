<?php
session_start();
include "../config/koneksi.php";

/* ===== PROTEKSI ADMIN / DOKTER ===== */
if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

/* ===== QUERY KUITANSI (KONSISTEN DENGAN INPUT DIAGNOSA) ===== */
if (isset($_GET['all'])) {

    $query = "
        SELECT 
            r.kode_rawat,
            r.tanggal_masuk,
            r.tanggal_keluar,
            r.biaya_pemeriksaan,
            p.nama_pasien,
            d.nama_dokter,

            (DATEDIFF(IFNULL(r.tanggal_keluar, CURDATE()), r.tanggal_masuk)+1) AS lama_inap,
            (k.harga * (DATEDIFF(IFNULL(r.tanggal_keluar, CURDATE()), r.tanggal_masuk)+1)) AS harga_kamar,
            IFNULL(SUM(ro.subtotal),0) AS harga_obat,

            (
                (k.harga * (DATEDIFF(IFNULL(r.tanggal_keluar, CURDATE()), r.tanggal_masuk)+1))
                + IFNULL(SUM(ro.subtotal),0)
                + r.biaya_pemeriksaan
            ) AS total_biaya

        FROM rawat_inap r
        JOIN pasien p ON r.kode_pasien=p.kode_pasien
        JOIN dokter d ON r.nip=d.nip
        JOIN kamar k ON r.nomor_kamar=k.nomor_kamar
        LEFT JOIN resep_obat ro ON r.kode_rawat=ro.kode_rawat
        GROUP BY r.kode_rawat
        ORDER BY r.tanggal_masuk DESC
    ";

} elseif (isset($_GET['kode_rawat'])) {

    $kode_rawat = mysqli_real_escape_string($koneksi,$_GET['kode_rawat']);

    $query = "
        SELECT 
            r.kode_rawat,
            r.tanggal_masuk,
            r.tanggal_keluar,
            r.biaya_pemeriksaan,
            p.nama_pasien,
            d.nama_dokter,

            (DATEDIFF(IFNULL(r.tanggal_keluar, CURDATE()), r.tanggal_masuk)+1) AS lama_inap,
            (k.harga * (DATEDIFF(IFNULL(r.tanggal_keluar, CURDATE()), r.tanggal_masuk)+1)) AS harga_kamar,
            IFNULL(SUM(ro.subtotal),0) AS harga_obat,

            (
                (k.harga * (DATEDIFF(IFNULL(r.tanggal_keluar, CURDATE()), r.tanggal_masuk)+1))
                + IFNULL(SUM(ro.subtotal),0)
                + r.biaya_pemeriksaan
            ) AS total_biaya

        FROM rawat_inap r
        JOIN pasien p ON r.kode_pasien=p.kode_pasien
        JOIN dokter d ON r.nip=d.nip
        JOIN kamar k ON r.nomor_kamar=k.nomor_kamar
        LEFT JOIN resep_obat ro ON r.kode_rawat=ro.kode_rawat
        WHERE r.kode_rawat='$kode_rawat'
        GROUP BY r.kode_rawat
    ";

} else {
    exit("Data tidak valid");
}

$data = mysqli_query($koneksi,$query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kuitansi Rawat Inap</title>

<style>
*{box-sizing:border-box}
body{
    font-family:'Segoe UI',sans-serif;
    background:#f2f2f2;
    margin:0;
    padding:20px;
}
.container{
    max-width:800px;
    margin:auto;
}
.kuitansi{
    background:#FFF6EE;
    padding:30px;
    border-radius:16px;
    margin-bottom:30px;
    box-shadow:0 8px 22px rgba(0,0,0,.2);
    page-break-after:always;
}
.header{
    text-align:center;
    border-bottom:3px solid #FF8C42;
    padding-bottom:15px;
    margin-bottom:20px;
}
.header h2{
    margin:0;
    color:#B34D00;
}
.header p{
    margin:5px 0 0;
    font-size:.9rem;
}
.info{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
    margin-bottom:20px;
}
.table{
    width:100%;
    border-collapse:collapse;
}
.table th,.table td{
    padding:12px;
    border-bottom:1px solid #ddd;
}
.table th{
    background:#FF8C42;
    color:#fff;
    text-align:left;
}
.total{
    text-align:right;
    font-size:1.1rem;
    margin-top:15px;
    font-weight:bold;
    color:#B34D00;
}
.footer{
    margin-top:40px;
    display:flex;
    justify-content:space-between;
    font-size:.9rem;
}
.ttd{text-align:center;}
.ttd div{margin-top:60px;font-weight:bold;}
@media print{
    body{background:#fff}
}
</style>
</head>

<body onload="window.print()">

<div class="container">

<?php while($r=mysqli_fetch_assoc($data)): ?>
<div class="kuitansi">

<div class="header">
    <h2>Bukit Indah Hospital</h2>
    <p>Kuitansi Rawat Inap</p>
</div>

<div class="info">
    <div><b>Kode Rawat</b><br><?= $r['kode_rawat'] ?></div>
    <div><b>Tanggal Masuk</b><br><?= $r['tanggal_masuk'] ?></div>

    <div><b>Nama Pasien</b><br><?= $r['nama_pasien'] ?></div>
    <div><b>Tanggal Keluar</b><br><?= $r['tanggal_keluar'] ?: '-' ?></div>

    <div><b>Dokter</b><br><?= $r['nama_dokter'] ?></div>
    <div><b>Lama Inap</b><br><?= $r['lama_inap'] ?> hari</div>
</div>

<table class="table">
<tr><th>Keterangan</th><th>Biaya</th></tr>
<tr><td>Biaya Kamar</td><td>Rp <?= number_format($r['harga_kamar'],0,',','.') ?></td></tr>
<tr><td>Biaya Obat</td><td>Rp <?= number_format($r['harga_obat'],0,',','.') ?></td></tr>
<tr><td>Biaya Pemeriksaan</td><td>Rp <?= number_format($r['biaya_pemeriksaan'],0,',','.') ?></td></tr>
</table>

<div class="total">
    Total Biaya: Rp <?= number_format($r['total_biaya'],0,',','.') ?>
</div>

<div class="footer">
    <div> <?= date('d-m-Y') ?></div>
    <div class="ttd">
        Dokter
        <div>( ___________ )</div>
    </div>
</div>

</div>
<?php endwhile; ?>

</div>

</body>
</html>
