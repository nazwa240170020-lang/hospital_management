<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$kode_rawat = $_GET['kode_rawat'] ?? null;
if (!$kode_rawat) {
    exit("Data tidak valid");
}
$data = mysqli_fetch_assoc(mysqli_query($koneksi,"
    SELECT 
        r.*,
        p.nama_pasien,
        d.nama_dokter,
        d.spesialis,
        k.tipe_kamar,
        k.nomor_kamar,

        (DATEDIFF(IFNULL(r.tanggal_keluar, CURDATE()), r.tanggal_masuk)+1) AS lama_inap,
        (k.harga * (DATEDIFF(IFNULL(r.tanggal_keluar, CURDATE()), r.tanggal_masuk)+1)) AS biaya_kamar,
        SUM(ro.subtotal) AS biaya_obat

    FROM rawat_inap r
    JOIN pasien p ON r.kode_pasien=p.kode_pasien
    JOIN dokter d ON r.nip=d.nip
    JOIN kamar k ON r.nomor_kamar=k.nomor_kamar
    LEFT JOIN resep_obat ro ON r.kode_rawat=ro.kode_rawat
    WHERE r.kode_rawat='$kode_rawat'
    GROUP BY r.kode_rawat
"));

if (!$data) {
    exit("Data tidak ditemukan");
}

$biaya_kamar = $data['biaya_kamar'] ?? 0;
$biaya_obat  = $data['biaya_obat'] ?? 0;
$biaya_periksa = $data['biaya_pemeriksaan'] ?? 0;
$total = $biaya_kamar + $biaya_obat + $biaya_periksa;
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
    box-shadow:0 8px 22px rgba(0,0,0,.2);
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
.info div{
    font-size:.95rem;
}
.table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
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
.ttd{
    text-align:center;
}
.ttd div{
    margin-top:60px;
    font-weight:bold;
}
@media print{
    body{background:#fff}
}
</style>
</head>

<body onload="window.print()">

<div class="container">
<div class="kuitansi">

    <div class="header">
        <h2>Bukit Indah Hospital</h2>
        <p>Kuitansi Rawat Inap</p>
    </div>

    <div class="info">
        <div><strong>Kode Rawat:</strong><br><?= $data['kode_rawat'] ?></div>
        <div><strong>Tanggal Masuk:</strong><br><?= $data['tanggal_masuk'] ?></div>

        <div><strong>Nama Pasien:</strong><br><?= $data['nama_pasien'] ?></div>
        <div><strong>Tanggal Keluar:</strong><br><?= $data['tanggal_keluar'] ?: '-' ?></div>

        <div><strong>Dokter:</strong><br><?= $data['nama_dokter'].' ('.$data['spesialis'].')' ?></div>
        <div><strong>Kamar:</strong><br><?= $data['tipe_kamar'].' - '.$data['nomor_kamar'] ?></div>
    </div>

    <table class="table">
        <tr>
            <th>Keterangan</th>
            <th>Biaya</th>
        </tr>
        <tr>
            <td>Biaya Kamar (<?= $data['lama_inap'] ?> hari)</td>
            <td>Rp <?= number_format($biaya_kamar,0,',','.') ?></td>
        </tr>
        <tr>
            <td>Biaya Obat</td>
            <td>Rp <?= number_format($biaya_obat,0,',','.') ?></td>
        </tr>
        <tr>
            <td>Biaya Pemeriksaan</td>
            <td>Rp <?= number_format($biaya_periksa,0,',','.') ?></td>
        </tr>
    </table>

    <div class="total">
        Total Biaya: Rp <?= number_format($total,0,',','.') ?>
    </div>

    <div class="footer">
        <div><?= date('d-m-Y') ?></div>
        <div class="ttd">
            Pasien
            <div>( ___________ )</div>
        </div>
    </div>

</div>
</div>

</body>
</html>
