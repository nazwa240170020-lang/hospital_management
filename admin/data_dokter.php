<!-- CREATE, UPDATE, READ, DELETE Berada dalam 1 file php -->
<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$nip_value = '';
$nama_value = '';
$spesialis_value = '';
$telp_value = '';
$alamat_value = '';
$error = '';

/* ===== SAVE / UPDATE ===== */
if (isset($_POST['save'])) {
    $nip       = trim($_POST['nip']);
    $nama      = trim($_POST['nama_dokter']);
    $spesialis = trim($_POST['spesialis']);
    $telp      = trim($_POST['no_telp']);
    $alamat    = trim($_POST['alamat']);

    $nip_value = $nip;
    $nama_value = $nama;
    $spesialis_value = $spesialis;
    $telp_value = $telp;
    $alamat_value = $alamat;

    if ($nip == '' || $nama == '') {
        $error = "NIP dan Nama wajib diisi!";
    }

    // TIDAK DAPAT CREATE/TAMBAH SAAT NIP DI DUPLIKASI KARENA MERUPAKAH PRIMARY KEY
    if ($error == '' && empty($_POST['nip_lama'])) {
        $cek = mysqli_prepare($koneksi,"SELECT nip FROM dokter WHERE nip=?");
        mysqli_stmt_bind_param($cek,"s",$nip);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);
        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "NIP sudah terdaftar!";
        }
        mysqli_stmt_close($cek);
    }

    if ($error == '') {
        if (!empty($_POST['nip_lama'])) {

            // UPDATE
            $stmt = mysqli_prepare($koneksi,"
                UPDATE dokter SET
                nip=?, nama_dokter=?, spesialis=?, no_telp=?, alamat=?
                WHERE nip=?
            ");
            mysqli_stmt_bind_param($stmt,"ssssss",
                $nip,$nama,$spesialis,$telp,$alamat,$_POST['nip_lama']
            );
        } else {

            // QUERY CREATE (INSERT)
            $stmt = mysqli_prepare($koneksi,"
                INSERT INTO dokter (nip,nama_dokter,spesialis,no_telp,alamat)
                VALUES (?,?,?,?,?)
            ");
            mysqli_stmt_bind_param($stmt,"sssss",
                $nip,$nama,$spesialis,$telp,$alamat
            );
        }

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: data_dokter.php");
        exit();
    }
}

/* ===== DELETE ===== */
if (isset($_GET['hapus'])) {
    $stmt = mysqli_prepare($koneksi,"DELETE FROM dokter WHERE nip=?");
    mysqli_stmt_bind_param($stmt,"s",$_GET['hapus']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: data_dokter.php");
    exit();
}

/* ===== READ untuk Menampilkan Data ke Tabel ===== */
$keyword = $_GET['search'] ?? '';
if ($keyword != '') {
    $like = "%$keyword%";
    $stmt = mysqli_prepare($koneksi,"
        SELECT * FROM dokter
        WHERE nip LIKE ? OR nama_dokter LIKE ? OR spesialis LIKE ?
        ORDER BY nip ASC
    ");
    mysqli_stmt_bind_param($stmt,"sss",$like,$like,$like);
    mysqli_stmt_execute($stmt);
    $data = mysqli_stmt_get_result($stmt);
} else {
    $data = mysqli_query($koneksi,"SELECT * FROM dokter ORDER BY nip ASC");
}

/* ===== EDIT ===== */
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = mysqli_prepare($koneksi,"SELECT * FROM dokter WHERE nip=?");
    mysqli_stmt_bind_param($stmt,"s",$_GET['edit']);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);

    $nip_value = $edit['nip'];
    $nama_value = $edit['nama_dokter'];
    $spesialis_value = $edit['spesialis'];
    $telp_value = $edit['no_telp'];
    $alamat_value = $edit['alamat'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Dokter - Hospital Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<style>
    
/* ===== CSS SIDEBAR ===== */
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

/* SIDEBAR */
.sidebar{
    width:250px;
    background:#FF8C42;
    min-height:100vh;
    transition:width 0.3s;
    position:relative;
}
.sidebar.collapsed{width:70px;}
.sidebar .logo{
    color:#FFF6EE;
    text-align:center;
    font-size:1.2rem;
    font-weight:bold;
    padding:20px 10px;
    border-bottom:1px solid rgba(255,255,255,0.3);
}
.sidebar ul{list-style:none;padding:0;margin:0;}
.sidebar ul li a{
    display:flex;
    align-items:center;
    padding:15px 20px;
    color:#FFF1E8;
    text-decoration:none;
    font-weight:600;
    white-space:nowrap;
}
.sidebar ul li a:hover,
.sidebar ul li a.active{background:#E67323;}
.sidebar ul li a i{width:25px;margin-right:15px;}
.sidebar.collapsed .label{display:none;}
.sidebar.collapsed ul li a:hover::after{
    content:attr(data-tooltip);
    position:absolute;
    left:100%;
    top:50%;
    transform:translateY(-50%);
    background:rgba(0,0,0,.75);
    color:#fff;
    padding:5px 10px;
    border-radius:5px;
    white-space:nowrap;
}

.toggle-btn{
    position:absolute;
    top:15px;
    right:-20px;
    background:#E67323;
    color:#fff;
    width:35px;
    height:35px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    font-weight:bold;
    z-index:100;
}

/* MAIN */
.main{flex:1;padding:20px;}
.content{
    background:rgba(255,242,224,.95);
    padding:25px;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
}
h1{text-align:center;color:#B34D00;}

/* SEARCH */
.search-box{text-align:center;margin-bottom:20px;}
.search-box input{padding:10px;border-radius:30px;border:2px solid #C7742E;}
.search-box button{padding:10px 30px;border-radius:30px;border:none;background:#FF8C42;color:#fff;}

/* FORM */
.form-tambah{
    max-width:700px;margin:0 auto 30px;
    background:#FFF6EE;padding:25px;border-radius:20px;
}
.form-tambah input,.form-tambah textarea{
    width:100%;padding:14px;margin-bottom:15px;
    border-radius:15px;border:2px solid #FFB380;
}
span.error{color:red;font-size:.9rem;}
.button-group{text-align:center;}
.button-group button,.button-link{
    background:linear-gradient(135deg,#FF8C42,#FFB380);
    color:#fff;font-weight:bold;
    padding:12px 35px;border-radius:30px;border:none;
}

/* TABLE */
table{width:100%;border-collapse:collapse;background:#FFB380;}
th,td{padding:14px;text-align:center;}
th{background:#FF944D;}
tr:nth-child(even){background:#FFC299;}
.update{background:#FF8C42;color:#fff;padding:6px 10px;border-radius:8px;}
.delete{background:#B34D00;color:#fff;padding:6px 10px;border-radius:8px;}
</style>
</head>

<body>

<!-- SIDEBAR  -->
<div class="sidebar" id="sidebar">
    <div class="logo">Hospital Admin</div>
    <div class="toggle-btn" onclick="toggleSidebar()">≡</div>
    <ul>
        <li><a href="dashboard.php" data-tooltip="Dashboard"><i class="fas fa-tachometer-alt"></i><span class="label">Dashboard</span></a></li>
        <li><a class="active" data-tooltip="Data Dokter"><i class="fas fa-user-md"></i><span class="label">Data Dokter</span></a></li>
        <li><a href="data_pasien.php" data-tooltip="Data Pasien"><i class="fas fa-users"></i><span class="label">Data Pasien</span></a></li>
        <li><a href="data_obat.php" data-tooltip="Data Obat"><i class="fas fa-pills"></i><span class="label">Data Obat</span></a></li>
        <li><a href="data_kamar.php" data-tooltip="Data Kamar"><i class="fas fa-bed"></i><span class="label">Data Kamar</span></a></li>
        <li><a href="jadwal_dokter.php" data-tooltip="Jadwal Dokter"><i class="fas fa-calendar-alt"></i><span class="label">Jadwal Dokter</span></a></li>
        <li><a href="laporan_rawat.php" data-tooltip="Laporan Rawat"><i class="fas fa-file-alt"></i><span class="label">Laporan</span></a></li>
        <li><a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a></li>
    </ul>
</div>

<div class="main">
<div class="content">
<h1>Data Dokter</h1>

<div class="search-box">
<form method="GET">
    <input type="text" name="search" placeholder="Cari Dokter..." value="<?= htmlspecialchars($keyword) ?>">
    <button type="submit">Search</button>
</form>
</div>

<form method="POST" class="form-tambah">
<input type="hidden" name="nip_lama" value="<?= $edit['nip'] ?? '' ?>">

<input type="text" name="nip" placeholder="NIP" value="<?= htmlspecialchars($nip_value) ?>" required>
<input type="text" name="nama_dokter" placeholder="Nama Dokter" value="<?= htmlspecialchars($nama_value) ?>" required>
<input type="text" name="spesialis" placeholder="Spesialis" value="<?= htmlspecialchars($spesialis_value) ?>">
<input type="text" name="no_telp" placeholder="No Telepon" value="<?= htmlspecialchars($telp_value) ?>">
<textarea name="alamat" placeholder="Alamat"><?= htmlspecialchars($alamat_value) ?></textarea>

<?php if($error): ?><span class="error"><?= $error ?></span><?php endif; ?>

<div class="button-group">
<button type="submit" name="save">
<i class="fas fa-save"></i> <?= $edit?'Update':'Save' ?>
</button>
<a href="data_dokter.php" class="button-link">New</a>
</div>
</form>

<table>
<tr>
<th>No</th><th>NIP</th><th>Nama</th><th>Spesialis</th><th>Telp</th><th>Alamat</th><th>Aksi</th>
</tr>


<!-- READ untuk Menampilkan Data ke Tabel HTML -->
<?php $no=1; while($d=mysqli_fetch_assoc($data)): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $d['nip'] ?></td>
<td><?= $d['nama_dokter'] ?></td>
<td><?= $d['spesialis'] ?></td>
<td><?= $d['no_telp'] ?></td>
<td><?= $d['alamat'] ?></td>
<td>
<a href="?edit=<?= $d['nip'] ?>" class="update"><i class="fas fa-edit"></i></a>
<a href="?hapus=<?= $d['nip'] ?>" class="delete" onclick="return confirm('Hapus data?')">
<i class="fas fa-trash"></i></a>
</td>
</tr>
<?php endwhile; ?>
</table>

</div>
</div>

<script>
function toggleSidebar(){
    document.getElementById('sidebar').classList.toggle('collapsed');
}
</script>

</body>
</html>
