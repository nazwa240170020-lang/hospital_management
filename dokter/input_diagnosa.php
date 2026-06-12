<!-- CREATE, UPDATE, READ, DELETE Berada dalam 1 file php -->
<?php
session_start();
include "../config/koneksi.php";

$id_user = $_SESSION['id_user'] ?? null;
$role    = $_SESSION['role'] ?? '';

if (!$id_user || $role != 'dokter') {
    header("Location: ../login.php");
    exit();
}

$dokter = mysqli_fetch_assoc(
    mysqli_query($koneksi,"SELECT nip, nama_dokter FROM dokter WHERE id_user='$id_user'")
);
if (!$dokter) {
    echo "<script>alert('Profil dokter belum terhubung');location='daftar_dokter.php';</script>";
    exit();
}
$nip         = $dokter['nip'];
$nama_dokter = htmlspecialchars($dokter['nama_dokter']);

/* ===== READ OBAT ===== */
$obat = mysqli_query($koneksi,"
    SELECT kode_obat, nama_obat, harga
    FROM obat
    ORDER BY nama_obat
");

/* ===== SAVE / UPDATE ===== */
if (isset($_POST['submit'])) {
    mysqli_begin_transaction($koneksi);
    try {
        $kode_rawat = intval($_POST['kode_rawat']);
        $diagnosa   = mysqli_real_escape_string($koneksi,$_POST['diagnosa']);
        $biaya      = intval($_POST['biaya_pemeriksaan']);

        mysqli_query($koneksi,"
            UPDATE rawat_inap SET
                diagnosa='$diagnosa',
                biaya_pemeriksaan='$biaya',
                status='Proses'
            WHERE kode_rawat='$kode_rawat' AND nip='$nip'
        ");

        /* Kembalikan stok lama */
        $old = mysqli_query($koneksi,"
            SELECT kode_obat, jumlah FROM resep_obat WHERE kode_rawat='$kode_rawat'
        ");
        while($r=mysqli_fetch_assoc($old)){
            mysqli_query($koneksi,"
                UPDATE obat SET stok = stok + {$r['jumlah']}
                WHERE kode_obat='{$r['kode_obat']}'
            ");
        }
        mysqli_query($koneksi,"DELETE FROM resep_obat WHERE kode_rawat='$kode_rawat'");

        /* Insert resep baru */
        if (!empty($_POST['obat'])) {
            foreach ($_POST['obat'] as $kode_obat => $v) {
                $jumlah = intval($_POST['jumlah'][$kode_obat]);
                $o = mysqli_fetch_assoc(mysqli_query($koneksi,"
                    SELECT harga, stok FROM obat WHERE kode_obat='$kode_obat'
                "));
                if ($o['stok'] < $jumlah) throw new Exception("Stok obat tidak mencukupi");

                $subtotal = $o['harga'] * $jumlah;

                mysqli_query($koneksi,"
                    INSERT INTO resep_obat (kode_rawat,kode_obat,jumlah,subtotal)
                    VALUES ('$kode_rawat','$kode_obat','$jumlah','$subtotal')
                ");
                mysqli_query($koneksi,"
                    UPDATE obat SET stok = stok - $jumlah
                    WHERE kode_obat='$kode_obat'
                ");
            }
        }

        mysqli_commit($koneksi);
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "<script>alert('".$e->getMessage()."');</script>";
    }
}

/* ===== DATA PASIEN + KELUHAN ===== */
$pasien = mysqli_query($koneksi,"
    SELECT r.*, r.keluhan,
           p.nama_pasien, p.umur,
           k.tipe_kamar, k.nomor_kamar, k.harga AS harga_kamar,
           (DATEDIFF(IFNULL(r.tanggal_keluar,CURDATE()),r.tanggal_masuk)+1) AS lama_inap
    FROM rawat_inap r
    JOIN pasien p ON r.kode_pasien=p.kode_pasien
    JOIN kamar k ON r.nomor_kamar=k.nomor_kamar
    WHERE r.nip='$nip'
    ORDER BY r.tanggal_masuk DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Input Diagnosa</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
body{margin:0;font-family:'Segoe UI',sans-serif;background:linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)),url("../assets/RS_Pondok_Indah_-_Pondok_Indah.jpg") center/cover fixed;display:flex;}
.sidebar{width:250px;background:#FF8C42;min-height:100vh;}
.sidebar .logo{color:#FFF6EE;text-align:center;font-weight:700;padding:20px;border-bottom:1px solid rgba(255,255,255,.3);}
.sidebar a{display:flex;align-items:center;gap:12px;padding:15px 22px;color:#FFF1E8;text-decoration:none;font-weight:600;}
.sidebar a:hover,.sidebar a.active{background:#E67323;border-left:5px solid #FFF6EE;}
.main{flex:1;padding:20px}
.content{background:rgba(255,242,224,.95);padding:25px;border-radius:20px;}
.patient{background:#FFE0B2;padding:20px;border-radius:16px;margin-bottom:25px}
.obat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;}
.obat-item{background:#FFF7EE;border:2px solid #FFD3B0;border-radius:14px;padding:12px;}
.table-card{background:#fff;border-radius:18px;padding:18px;margin-top:20px;box-shadow:0 12px 25px rgba(0,0,0,.18);}
table{width:100%;border-collapse:collapse}
th,td{padding:12px;border-bottom:1px solid #eee}
th{background:#FF8C42;color:#fff}
.total-row{background:#FFF3E6;font-weight:700}
.grand-total{background:#FF8C42;color:#fff}
.print-btn{display:inline-block;margin-top:12px;background:#FF8C42;color:#fff;padding:8px 14px;border-radius:10px;text-decoration:none;}
</style>
</head>

<body>
<div class="sidebar">
    <div class="logo"><i class="fas fa-user-doctor"></i><br><?= $nama_dokter ?></div>
    <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="daftar_dokter.php"><i class="fas fa-user"></i> Profil</a>
    <a href="data_pasien.php"><i class="fas fa-users"></i> Data Pasien</a>
    <a href="input_diagnosa.php" class="active"><i class="fas fa-stethoscope"></i> Diagnosa</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main">
<div class="content">
<h2>Input Diagnosa & Resep</h2>

<?php while($p=mysqli_fetch_assoc($pasien)):
$harga_kamar = $p['harga_kamar'] * $p['lama_inap'];
?>
<div class="patient">
<form method="POST">
<input type="hidden" name="kode_rawat" value="<?= $p['kode_rawat'] ?>">

<b><?= htmlspecialchars($p['nama_pasien']) ?></b> (<?= $p['umur'] ?> th)<br>
<b>Kamar:</b> <?= $p['tipe_kamar'].' - '.$p['nomor_kamar'] ?><br>
<b>Harga Kamar:</b> Rp <?= number_format($harga_kamar,0,',','.') ?><br><br>

<b>Keluhan Pasien</b><br>
<?= nl2br(htmlspecialchars($p['keluhan'] ?? '-')) ?>
<br><br>

<b>Diagnosa</b>
<textarea name="diagnosa" required><?= htmlspecialchars($p['diagnosa']) ?></textarea><br><br>

<b>Biaya Pemeriksaan</b>
<input type="number" name="biaya_pemeriksaan" value="<?= $p['biaya_pemeriksaan'] ?? 0 ?>" required><br><br>

<b>Resep Obat</b>
<div class="obat-grid">
<?php mysqli_data_seek($obat,0); while($o=mysqli_fetch_assoc($obat)): ?>
<div class="obat-item">
    <b><?= htmlspecialchars($o['nama_obat']) ?></b><br>
    Rp <?= number_format($o['harga'],0,',','.') ?><br>
    <input type="checkbox" name="obat[<?= $o['kode_obat'] ?>]" value="1"> Pilih
    <input type="number" name="jumlah[<?= $o['kode_obat'] ?>]" value="1" min="1">
</div>
<?php endwhile ?>
</div>

<br>
<button name="submit">Simpan</button>
</form>

<?php
$resep = mysqli_query($koneksi,"
    SELECT o.nama_obat, r.jumlah, r.subtotal
    FROM resep_obat r
    JOIN obat o ON r.kode_obat=o.kode_obat
    WHERE r.kode_rawat='{$p['kode_rawat']}'
");
$total_obat = 0;
?>
<div class="table-card">
<table>
<tr><th>Obat</th><th>Jumlah</th><th>Subtotal</th></tr>
<?php while($r=mysqli_fetch_assoc($resep)):
$total_obat += $r['subtotal']; ?>
<tr>
<td><?= $r['nama_obat'] ?></td>
<td><?= $r['jumlah'] ?></td>
<td>Rp <?= number_format($r['subtotal'],0,',','.') ?></td>
</tr>
<?php endwhile ?>
<tr class="total-row">
<td colspan="2">Total Obat</td>
<td>Rp <?= number_format($total_obat,0,',','.') ?></td>
</tr>
<tr class="total-row">
<td colspan="2">Biaya Pemeriksaan</td>
<td>Rp <?= number_format($p['biaya_pemeriksaan']??0,0,',','.') ?></td>
</tr>
<tr class="grand-total">
<td colspan="2">TOTAL AKHIR</td>
<td>Rp <?= number_format($harga_kamar + $total_obat + ($p['biaya_pemeriksaan']??0),0,',','.') ?></td>
</tr>
</table>
</div>

<a class="print-btn" href="print_kuitansi.php?kode_rawat=<?= $p['kode_rawat'] ?>" target="_blank">
<i class="fas fa-print"></i> Print Kuitansi
</a>
</div>
<?php endwhile ?>
</div>
</div>
</body>
</html>
