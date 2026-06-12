<!-- CREATE, UPDATE, READ, DELETE Berada dalam 1 file php -->
<?php
session_start();
include "../config/koneksi.php";

/* ===== PROTEKSI ADMIN ===== */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ===== SIMPAN / UPDATE ===== */
if (isset($_POST['save'])) {
    $kode_rawat  = $_POST['kode_rawat'];
    $kode_pasien = $_POST['kode_pasien'];
    $nip         = $_POST['nip'];
    $tgl_masuk   = $_POST['tanggal_masuk'];
    $tgl_keluar  = $_POST['tanggal_keluar'] ?: NULL;
    $diagnosa    = $_POST['diagnosa'];
    $biaya       = $_POST['biaya']; // biaya pemeriksaan

    if ($kode_rawat == '') {

        // QUERY CREATE (INSERT)
        mysqli_query($koneksi,"INSERT INTO rawat_inap
        (kode_pasien,nip,tanggal_masuk,tanggal_keluar,diagnosa,biaya)
        VALUES ('$kode_pasien','$nip','$tgl_masuk','$tgl_keluar','$diagnosa','$biaya')");
    } else {
        mysqli_query($koneksi,"UPDATE rawat_inap SET
            kode_pasien='$kode_pasien',
            nip='$nip',
            tanggal_masuk='$tgl_masuk',
            tanggal_keluar='$tgl_keluar',
            diagnosa='$diagnosa',
            biaya='$biaya'
        WHERE kode_rawat='$kode_rawat'");
    }
    header("Location: laporan_rawat.php");
    exit();
}

/* ===== DELETE ===== */
if (isset($_GET['hapus'])) {
    mysqli_query($koneksi,"DELETE FROM rawat_inap WHERE kode_rawat='".$_GET['hapus']."'");
    header("Location: laporan_rawat.php");
    exit();
}

/* ===== EDIT ===== */
$edit = null;
if (isset($_GET['edit'])) {
    $edit = mysqli_fetch_assoc(
        mysqli_query($koneksi,"SELECT * FROM rawat_inap WHERE kode_rawat='".$_GET['edit']."'")
    );
}

/* ===== MASTER DATA ===== */
$pasien = mysqli_query($koneksi,"SELECT * FROM pasien ORDER BY CAST(kode_pasien AS UNSIGNED)");
$dokter = mysqli_query($koneksi,"SELECT * FROM dokter ORDER BY nama_dokter");

/* ===== DATA RAWAT (TOTAL OTOMATIS, UI TETAP) ===== */
$data = mysqli_query($koneksi,"
    SELECT 
        r.*,
        p.nama_pasien,
        d.nama_dokter,

        (DATEDIFF(IFNULL(r.tanggal_keluar, CURDATE()), r.tanggal_masuk)+1) AS lama_inap,
        (k.harga * (DATEDIFF(IFNULL(r.tanggal_keluar, CURDATE()), r.tanggal_masuk)+1)) AS harga_kamar,
        IFNULL(SUM(ro.subtotal),0) AS harga_obat,

        (
            (k.harga * (DATEDIFF(IFNULL(r.tanggal_keluar, CURDATE()), r.tanggal_masuk)+1))
            + IFNULL(SUM(ro.subtotal),0)
            + r.biaya
        ) AS total_biaya

    FROM rawat_inap r
    JOIN pasien p ON r.kode_pasien=p.kode_pasien
    JOIN dokter d ON r.nip=d.nip
    JOIN kamar k ON r.nomor_kamar=k.nomor_kamar
    LEFT JOIN resep_obat ro ON r.kode_rawat=ro.kode_rawat
    GROUP BY r.kode_rawat
    ORDER BY r.tanggal_masuk DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan Rawat Inap - Hospital Admin</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<style>
/* ====== UI ASLI (TIDAK DIUBAH) ====== */
*{box-sizing:border-box;}
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background: linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)),
                url("../assets/RS_Pondok_Indah_-_Pondok_Indah.jpg");
    background-size:cover;
    background-attachment:fixed;
    display:flex;
}
.sidebar{
    width:250px;background:#FF8C42;min-height:100vh;position:relative;
}
.sidebar .logo{
    color:#FFF6EE;text-align:center;font-size:1.2rem;
    font-weight:bold;padding:20px;border-bottom:1px solid rgba(255,255,255,.3);
}
.sidebar ul{list-style:none;padding:0;margin:0;}
.sidebar ul li a{
    display:flex;align-items:center;
    padding:15px 20px;color:#FFF1E8;
    text-decoration:none;font-weight:600;
}
.sidebar ul li a:hover,
.sidebar ul li a.active{background:#E67323;}
.sidebar ul li a i{width:25px;margin-right:15px;}

.main{flex:1;padding:20px;}
.content{
    background:rgba(255,242,224,.95);
    padding:25px;border-radius:18px;
}
.form-tambah{
    max-width:720px;margin:0 auto 30px;
    background:#FFF6EE;padding:25px;border-radius:20px;
}
.form-tambah input,
.form-tambah select,
.form-tambah textarea{
    width:100%;padding:14px;margin-bottom:15px;
    border-radius:15px;border:2px solid #FFB380;
}
table{
    width:100%;border-collapse:collapse;
    background:#FFB380;border-radius:12px;
}
th,td{
    padding:14px;border-bottom:1px solid rgba(122,62,46,.25);
    text-align:center;
}
th{background:#FF944D;}
a.update{background:#FF8C42;color:#fff;padding:6px 10px;border-radius:8px;}
a.delete{background:#B34D00;color:#fff;padding:6px 10px;border-radius:8px;}
</style>
</head>

<body>

<div class="sidebar">
    <div class="logo">Hospital Admin</div>
    <ul>
        <li><a href="dashboard.php" class="active" data-tooltip="Dashboard"><i class="fas fa-tachometer-alt"></i> <span class="label">Dashboard</span></a></li>
        <li><a href="data_dokter.php" data-tooltip="Data Dokter"><i class="fas fa-user-md"></i> <span class="label">Data Dokter</span></a></li>
        <li><a href="data_pasien.php" data-tooltip="Data Pasien"><i class="fas fa-users"></i> <span class="label">Data Pasien</span></a></li>
        <li><a href="data_obat.php" data-tooltip="Data Obat"><i class="fas fa-pills"></i> <span class="label">Data Obat</span></a></li>
        <li><a href="data_kamar.php" data-tooltip="Data Kamar"><i class="fas fa-bed"></i> <span class="label">Data Kamar</span></a></li>
        <li><a href="jadwal_dokter.php" data-tooltip="Jadwal Dokter"><i class="fas fa-calendar-alt"></i> <span class="label">Jadwal Dokter</span></a></li>
        <li><a href="laporan_rawat.php" data-tooltip="Laporan Rawat"><i class="fas fa-file-alt"></i> <span class="label">Laporan</span></a></li>
        <li><a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i> <span class="label">Logout</span></a></li>
    </ul>
</div>

<div class="main">
<div class="content">
<h1 style="text-align:center">Laporan Rawat Inap</h1>

<form method="POST" class="form-tambah">
<input type="hidden" name="kode_rawat" value="<?= $edit['kode_rawat'] ?? '' ?>">

<select name="kode_pasien" required>
<option value="">-- Pilih Pasien --</option>
<?php while($p=mysqli_fetch_assoc($pasien)): ?>
<option value="<?= $p['kode_pasien'] ?>" <?= ($edit && $edit['kode_pasien']==$p['kode_pasien'])?'selected':'' ?>>
<?= $p['kode_pasien'].' - '.$p['nama_pasien'] ?>
</option>
<?php endwhile ?>
</select>

<select name="nip" required>
<option value="">-- Pilih Dokter --</option>
<?php while($d=mysqli_fetch_assoc($dokter)): ?>
<option value="<?= $d['nip'] ?>" <?= ($edit && $edit['nip']==$d['nip'])?'selected':'' ?>>
<?= $d['nama_dokter'] ?>
</option>
<?php endwhile ?>
</select>

<input type="date" name="tanggal_masuk" value="<?= $edit['tanggal_masuk'] ?? '' ?>" required>
<input type="date" name="tanggal_keluar" value="<?= $edit['tanggal_keluar'] ?? '' ?>">
<textarea name="diagnosa" required><?= $edit['diagnosa'] ?? '' ?></textarea>
<input type="number" name="biaya" placeholder="Biaya (Rp)" value="<?= $edit['biaya'] ?? '' ?>" required>

<button type="submit" name="save"><?= $edit?'Update':'Save' ?></button>
</form>

<table>
<tr>
<th>No</th><th>Kode</th><th>Nama Pasien</th><th>Dokter</th>
<th>Tgl Masuk</th><th>Tgl Keluar</th><th>Diagnosa</th><th>Biaya</th><th>Aksi</th>
</tr>

<!-- READ untuk Menampilkan Data ke Tabel HTML -->
<?php $no=1; while($r=mysqli_fetch_assoc($data)): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $r['kode_pasien'] ?></td>
<td><?= $r['nama_pasien'] ?></td>
<td><?= $r['nama_dokter'] ?></td>
<td><?= $r['tanggal_masuk'] ?></td>
<td><?= $r['tanggal_keluar'] ?: '-' ?></td>
<td><?= $r['diagnosa'] ?></td>
<td><b>Rp <?= number_format($r['total_biaya'],0,',','.') ?></b></td>
<td>
<a href="?edit=<?= $r['kode_rawat'] ?>" class="update"><i class="fas fa-edit"></i></a>
<a href="print_kuitansi.php?kode_rawat=<?= $r['kode_rawat'] ?>" target="_blank" class="update"><i class="fas fa-print"></i></a>
<a href="?hapus=<?= $r['kode_rawat'] ?>" class="delete" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a>
</td>
</tr>
<?php endwhile ?>
</table>

</div>
</div>

</body>
</html>
