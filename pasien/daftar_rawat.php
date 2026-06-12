<!-- CREATE, UPDATE, READ, DELETE Berada dalam 1 file php -->
<?php
session_start();
include "../config/koneksi.php";

$id_user = $_SESSION['id_user'] ?? null;
$role    = $_SESSION['role'] ?? '';

if (!$id_user || $role != 'pasien') {
    header("Location: ../login.php");
    exit();
}

/* =====================READ DATA PASIEN ===================== */
$pasien = mysqli_fetch_assoc(mysqli_query(
    $koneksi,
    "SELECT * FROM pasien WHERE id_user='$id_user'"
));

if (!$pasien) {
    echo "<script>
        alert('Lengkapi profil pasien terlebih dahulu');
        window.location='daftar_pasien.php';
    </script>";
    exit();
}

$kode_pasien = $pasien['kode_pasien'];
$nama_tampil = htmlspecialchars($pasien['nama_pasien'] ?? 'Pasien');

$filter = $_GET['filter'] ?? '';
$whereFilter = "";

if ($filter == 'aktif') {
    $whereFilter = "AND (tanggal_keluar IS NULL OR tanggal_keluar='' OR tanggal_keluar='0000-00-00')";
} elseif ($filter == 'selesai') {
    $whereFilter = "AND status='Selesai'";
}

/* ===================== DROPDOWN ===================== */
$dokter = mysqli_query($koneksi,"SELECT * FROM dokter ORDER BY nama_dokter");
$kamar  = mysqli_query($koneksi,"
    SELECT nomor_kamar, tipe_kamar, harga
    FROM kamar
    WHERE status='Kosong'
    ORDER BY tipe_kamar
");

/* ===================== TAMBAH RAWAT ===================== */

/* ===== SAVE / UPDATE ===== */
if (isset($_POST['submit'])) {
    mysqli_query($koneksi,"
        INSERT INTO rawat_inap
        (kode_pasien,nip,nomor_kamar,tanggal_masuk,keluhan,diagnosa,biaya,status)
        VALUES (
            '$kode_pasien',
            '{$_POST['nip']}',
            '{$_POST['nomor_kamar']}',
            '{$_POST['tanggal_masuk']}',
            '".mysqli_real_escape_string($koneksi,$_POST['keluhan'])."',
            '',
            '{$_POST['biaya']}',
            'Menunggu'
        )
    ");

    mysqli_query($koneksi,"
        UPDATE kamar SET status='Terisi'
        WHERE nomor_kamar='{$_POST['nomor_kamar']}'
    ");

    header("Location: daftar_rawat.php");
    exit();
}

/* ===================== UPDATE KELUHAN ===================== */
if (isset($_POST['update_keluhan'])) {
    mysqli_query($koneksi,"
        UPDATE rawat_inap
        SET keluhan='".mysqli_real_escape_string($koneksi,$_POST['keluhan'])."'
        WHERE kode_rawat='{$_POST['kode_rawat']}'
        AND status!='Selesai'
    ");
    header("Location: daftar_rawat.php");
    exit();
}

/* ===================== HAPUS RAWAT ===================== */
if (isset($_GET['hapus'])) {
    $rawat = mysqli_fetch_assoc(mysqli_query($koneksi,"
        SELECT nomor_kamar FROM rawat_inap
        WHERE kode_rawat='{$_GET['hapus']}' AND status!='Selesai'
    "));

    if ($rawat) {
        mysqli_query($koneksi,"
            UPDATE kamar SET status='Kosong'
            WHERE nomor_kamar='{$rawat['nomor_kamar']}'
        ");
        mysqli_query($koneksi,"
            DELETE FROM rawat_inap WHERE kode_rawat='{$_GET['hapus']}'
        ");
    }
    header("Location: daftar_rawat.php");
    exit();
}

/* ===================== READ ===================== */
$riwayat = mysqli_query($koneksi,"
    SELECT r.*, d.nama_dokter, k.tipe_kamar
    FROM rawat_inap r
    JOIN dokter d ON r.nip=d.nip
    JOIN kamar k ON r.nomor_kamar=k.nomor_kamar
    WHERE r.kode_pasien='$kode_pasien'
    $whereFilter
    ORDER BY r.tanggal_masuk DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Rawat Inap</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<style>
/* ===================== UI TETAP (SIDEBAR DISAMAKAN) ===================== */
*{box-sizing:border-box}
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:
      linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.55)),
      url("../assets/RS_Pondok_Indah_-_Pondok_Indah.jpg") center/cover fixed;
    display:flex;
}

/* ===== SIDEBAR (SAMA DENGAN RIWAYAT) ===== */
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
.main{flex:1;padding:30px;margin-left:10px}
.header{text-align:center;color:#fff;margin-bottom:25px}
.content{
    background:rgba(255,255,255,.82);
    backdrop-filter:blur(16px);
    border-radius:22px;
    padding:25px;
    box-shadow:0 20px 40px rgba(0,0,0,.25);
}

/* FORM & TABLE (TIDAK DIUBAH) */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
.form-grid input,.form-grid select,.form-grid textarea{padding:14px;border-radius:14px;border:none}
.form-grid textarea{grid-column:1/-1}
.form-actions{text-align:center}
.form-actions button{padding:14px 28px;border-radius:30px;background:#FF8C42;color:#fff;border:none;font-weight:700}
.table-box{margin-top:25px;background:rgba(255,255,255,.78);border-radius:18px;overflow:hidden}
table{width:100%;border-collapse:collapse}
th,td{padding:12px;border-bottom:1px solid rgba(0,0,0,.08)}
th{background:rgba(255,140,66,.25)}
.badge{padding:4px 10px;border-radius:12px;font-size:.75rem;font-weight:700}
.Menunggu{background:#FFD54F}
.Proses{background:#81C784}
.Selesai{background:#BDBDBD}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">👤 <?= $nama_tampil ?></div>
    <a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
    <a href="daftar_pasien.php"><i class="fas fa-user"></i>Profil</a>
    <a class="active"><i class="fas fa-notes-medical"></i>Rawat Inap</a>
    <a href="riwayat_rawat.php"><i class="fas fa-clock-rotate-left"></i>Riwayat</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<!-- MAIN -->
<div class="main">
<div class="header"><h1>Rawat Inap</h1></div>

<div class="content">

<form method="POST">
<div class="form-grid">
<select name="nip" required>
<option value="">Pilih Dokter</option>
<?php while($d=mysqli_fetch_assoc($dokter)): ?>
<option value="<?= $d['nip'] ?>"><?= $d['nama_dokter'] ?></option>
<?php endwhile ?>
</select>

<select name="nomor_kamar" id="kamar" required>
<option value="">Pilih Kamar</option>
<?php while($k=mysqli_fetch_assoc($kamar)): ?>
<option value="<?= $k['nomor_kamar'] ?>" data-harga="<?= $k['harga'] ?>">
<?= $k['tipe_kamar'] ?> - No <?= $k['nomor_kamar'] ?>
</option>
<?php endwhile ?>
</select>

<input type="text" id="harga_kamar" placeholder="Harga Kamar" readonly>
<input type="hidden" name="biaya" id="biaya">
<input type="date" name="tanggal_masuk" value="<?= date('Y-m-d') ?>" required>
<textarea name="keluhan" placeholder="Keluhan pasien" required></textarea>
</div>
<div class="form-actions"><button name="submit">Daftar Rawat Inap</button></div>
</form>

<div class="table-box">
<table>
<tr>
<th>No</th><th>Dokter</th><th>Kamar</th><th>Status</th><th>Keluhan</th><th>Aksi</th>
</tr>
<?php $no=1; while($r=mysqli_fetch_assoc($riwayat)): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $r['nama_dokter'] ?></td>
<td><?= $r['tipe_kamar'].' - '.$r['nomor_kamar'] ?></td>
<td><span class="badge <?= $r['status'] ?>"><?= $r['status'] ?></span></td>
<td>
<?php if(isset($_GET['edit']) && $_GET['edit']==$r['kode_rawat']): ?>
<form method="POST">
<input type="hidden" name="kode_rawat" value="<?= $r['kode_rawat'] ?>">
<textarea name="keluhan" required><?= $r['keluhan'] ?></textarea>
<button name="update_keluhan">Simpan</button>
</form>
<?php else: ?>
<?= $r['keluhan'] ?>
<?php endif ?>
</td>
<td>
<?php if($r['status']!='Selesai'): ?>
<a href="?edit=<?= $r['kode_rawat'] ?>">✏️</a>
<a href="?hapus=<?= $r['kode_rawat'] ?>" onclick="return confirm('Hapus data?')">🗑️</a>
<?php endif ?>
</td>
</tr>
<?php endwhile ?>
</table>
</div>

</div>
</div>

<script>
document.getElementById('kamar').addEventListener('change',function(){
const h=this.options[this.selectedIndex].dataset.harga||0;
document.getElementById('harga_kamar').value=h?'Rp '+Number(h).toLocaleString('id-ID'):'';
document.getElementById('biaya').value=h;
});
</script>

</body>
</html>
