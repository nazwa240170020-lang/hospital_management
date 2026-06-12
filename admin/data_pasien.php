<!-- CREATE, UPDATE, READ, DELETE Berada dalam 1 file php -->
<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$kode_error = '';
$kode_value = $nama_value = $alamat_value = $no_telp_value = '';
$gol_darah_value = $umur_value = $jenis_kelamin_value = '';

/* ===================== SIMPAN / UPDATE ===================== */
if (isset($_POST['save'])) {
    $kode_pasien   = trim($_POST['kode_pasien']);
    $nama          = $_POST['nama_pasien'];
    $alamat        = $_POST['alamat'];
    $no_telp       = $_POST['no_telp'];
    $gol_darah     = $_POST['gol_darah'];
    $umur          = $_POST['umur'];
    $jenis_kelamin = $_POST['jenis_kelamin'];

    $kode_value = $kode_pasien;
    $nama_value = $nama;
    $alamat_value = $alamat;
    $no_telp_value = $no_telp;
    $gol_darah_value = $gol_darah;
    $umur_value = $umur;
    $jenis_kelamin_value = $jenis_kelamin;

    if ($kode_pasien == '') {
        $kode_error = "Kode Pasien wajib diisi!";
    }
// TIDAK DAPAT CREATE/TAMBAH SAAT kode_pasien DI DUPLIKASI KARENA MERUPAKAH PRIMARY KEY
    if ($kode_error == '' && empty($_POST['id_edit'])) {
        $cek = mysqli_prepare($koneksi,"SELECT kode_pasien FROM pasien WHERE kode_pasien=?");
        mysqli_stmt_bind_param($cek,"s",$kode_pasien);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);
        if (mysqli_stmt_num_rows($cek) > 0) {
            $kode_error = "Kode Pasien sudah terdaftar!";
        }
        mysqli_stmt_close($cek);
    }

    if ($kode_error == '') {
        if (!empty($_POST['id_edit'])) {
            $stmt = mysqli_prepare($koneksi,"
                UPDATE pasien SET
                nama_pasien=?, alamat=?, no_telp=?, gol_darah=?, umur=?, jenis_kelamin=?
                WHERE kode_pasien=?
            ");
            mysqli_stmt_bind_param($stmt,"ssssiss",
                $nama,$alamat,$no_telp,$gol_darah,$umur,$jenis_kelamin,$kode_pasien
            );
        } else {

            // QUERY CREATE (INSERT)
            $stmt = mysqli_prepare($koneksi,"
                INSERT INTO pasien (kode_pasien,nama_pasien,alamat,no_telp,gol_darah,umur,jenis_kelamin)
                VALUES (?,?,?,?,?,?,?)
            ");
            mysqli_stmt_bind_param($stmt,"sssssis",
                $kode_pasien,$nama,$alamat,$no_telp,$gol_darah,$umur,$jenis_kelamin
            );
        }

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("Location: data_pasien.php");
        exit();
    }
}

/* ===================== DELETE ===================== */
if (isset($_GET['hapus'])) {
    $stmt = mysqli_prepare($koneksi,"DELETE FROM pasien WHERE kode_pasien=?");
    mysqli_stmt_bind_param($stmt,"s",$_GET['hapus']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: data_pasien.php");
    exit();
}


/* ===== READ untuk Menampilkan Data ke Tabel ===== */
$keyword = $_GET['search'] ?? '';
if ($keyword != '') {
    $like = "%$keyword%";
    $stmt = mysqli_prepare($koneksi,"
        SELECT * FROM pasien
        WHERE kode_pasien LIKE ? OR nama_pasien LIKE ?
        ORDER BY CAST(kode_pasien AS UNSIGNED) ASC
    ");
    mysqli_stmt_bind_param($stmt,"ss",$like,$like);
    mysqli_stmt_execute($stmt);
    $data = mysqli_stmt_get_result($stmt);
} else {
    $data = mysqli_query($koneksi,"
        SELECT * FROM pasien ORDER BY CAST(kode_pasien AS UNSIGNED) ASC
    ");
}

/* ===================== EDIT ===================== */
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = mysqli_prepare($koneksi,"SELECT * FROM pasien WHERE kode_pasien=?");
    mysqli_stmt_bind_param($stmt,"s",$_GET['edit']);
    mysqli_stmt_execute($stmt);
    $edit = mysqli_stmt_get_result($stmt)->fetch_assoc();
    mysqli_stmt_close($stmt);

    $kode_value = $edit['kode_pasien'];
    $nama_value = $edit['nama_pasien'];
    $alamat_value = $edit['alamat'];
    $no_telp_value = $edit['no_telp'];
    $gol_darah_value = $edit['gol_darah'];
    $umur_value = $edit['umur'];
    $jenis_kelamin_value = $edit['jenis_kelamin'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Pasien - Hospital Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<style>
/* ===== CSS  ===== */
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
    width:250px;background:#FF8C42;min-height:100vh;
    transition:width 0.3s;position:relative;
}
.sidebar.collapsed{width:70px;}
.sidebar .logo{
    color:#FFF6EE;text-align:center;font-size:1.2rem;
    font-weight:bold;padding:20px 10px;
    border-bottom:1px solid rgba(255,255,255,0.3);
}
.sidebar ul{list-style:none;padding:0;margin:0;}
.sidebar ul li a{
    display:flex;align-items:center;padding:15px 20px;
    color:#FFF1E8;text-decoration:none;font-weight:600;
}
.sidebar ul li a:hover,
.sidebar ul li a.active{background:#E67323;}
.sidebar ul li a i{width:25px;margin-right:15px;}
.sidebar.collapsed .label{display:none;}
.toggle-btn{
    position:absolute;top:15px;right:-20px;
    background:#E67323;color:#fff;
    width:35px;height:35px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;
}

.main{flex:1;padding:20px;}
.content{
    background:rgba(255,242,224,.95);
    padding:25px;border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
}
h1{text-align:center;color:#B34D00;}
.search-box{text-align:center;margin-bottom:20px;}
.search-box input{padding:10px 15px;border-radius:30px;border:2px solid #C7742E;width:260px;}
.search-box button{padding:10px 28px;border-radius:30px;border:none;background:#FF8C42;color:#fff;font-weight:700;}
.form-tambah{max-width:700px;margin:0 auto 30px;background:#FFF6EE;padding:25px;border-radius:20px;}
.form-tambah input,.form-tambah select{
    width:100%;padding:14px;margin-bottom:15px;
    border-radius:15px;border:2px solid #FFB380;
}
.button-group{text-align:center;}
.button-group button,.button-link{
    padding:12px 36px;border-radius:30px;border:none;
    background:linear-gradient(135deg,#FF8C42,#FFB380);
    color:#fff;font-weight:700;
}
table{width:100%;border-collapse:collapse;background:#FFB380;border-radius:12px;}
th,td{padding:14px;}
th{background:#FF944D;}
tr:nth-child(even){background:#FFC299;}
a.update{background:#FF8C42;color:#fff;padding:6px 10px;border-radius:8px;}
a.delete{background:#B34D00;color:#fff;padding:6px 10px;border-radius:8px;}
</style>
</head>

<body>

<!-- SIDEBAR  -->
<div class="sidebar" id="sidebar">
    <div class="logo">Hospital Admin</div>
    <div class="toggle-btn" onclick="toggleSidebar()">≡</div>
    <ul>
        <li><a href="dashboard.php" data-tooltip="Dashboard"><i class="fas fa-tachometer-alt"></i><span class="label">Dashboard</span></a></li>
        <li><a href="data_dokter.php" data-tooltip="Data Dokter"><i class="fas fa-user-md"></i><span class="label">Data Dokter</span></a></li>
        <li><a class="active" data-tooltip="Data Pasien"><i class="fas fa-users"></i><span class="label">Data Pasien</span></a></li>
        <li><a href="data_obat.php" data-tooltip="Data Obat"><i class="fas fa-pills"></i><span class="label">Data Obat</span></a></li>
        <li><a href="data_kamar.php" data-tooltip="Data Kamar"><i class="fas fa-bed"></i><span class="label">Data Kamar</span></a></li>
        <li><a href="jadwal_dokter.php" data-tooltip="Jadwal Dokter"><i class="fas fa-calendar-alt"></i><span class="label">Jadwal Dokter</span></a></li>
        <li><a href="laporan_rawat.php" data-tooltip="Laporan Rawat"><i class="fas fa-file-alt"></i><span class="label">Laporan</span></a></li>
        <li><a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a></li>
    </ul>
</div>

<div class="main">
<div class="content">
<h1>Data Pasien</h1>

<div class="search-box">
<form>
<input name="search" placeholder="Cari pasien..." value="<?= htmlspecialchars($keyword) ?>">
<button>Search</button>
</form>
</div>

<form method="POST" class="form-tambah">
<input type="hidden" name="id_edit" value="<?= $edit['kode_pasien'] ?? '' ?>">

<input name="kode_pasien" placeholder="Kode Pasien" value="<?= htmlspecialchars($kode_value) ?>" required>
<?= $kode_error ? "<span style='color:red'>$kode_error</span>" : "" ?>

<input name="nama_pasien" placeholder="Nama Pasien" value="<?= htmlspecialchars($nama_value) ?>" required>
<input name="alamat" placeholder="Alamat" value="<?= htmlspecialchars($alamat_value) ?>" required>
<input name="no_telp" placeholder="No Telp" value="<?= htmlspecialchars($no_telp_value) ?>" required>

<select name="gol_darah" required>
<option value="">Gol Darah</option>
<?php foreach(['A','B','AB','O'] as $g): ?>
<option <?= $gol_darah_value==$g?'selected':'' ?>><?= $g ?></option>
<?php endforeach ?>
</select>

<input type="number" name="umur" placeholder="Umur" value="<?= $umur_value ?>" required>

<select name="jenis_kelamin" required>
<option value="">Jenis Kelamin</option>
<option value="L" <?= $jenis_kelamin_value=='L'?'selected':'' ?>>Laki-laki</option>
<option value="P" <?= $jenis_kelamin_value=='P'?'selected':'' ?>>Perempuan</option>
</select>

<div class="button-group">
<button name="save"><?= $edit?'Update':'Save' ?></button>
<a href="data_pasien.php" class="button-link">New</a>
</div>
</form>

<table>
<tr>
<th>No</th><th>Kode</th><th>Nama</th><th>Alamat</th>
<th>Telp</th><th>Gol</th><th>Umur</th><th>JK</th><th>Aksi</th>
</tr>

<!-- READ untuk Menampilkan Data ke Tabel HTML -->
<?php $no=1; while($p=mysqli_fetch_assoc($data)): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $p['kode_pasien'] ?></td>
<td><?= $p['nama_pasien'] ?></td>
<td><?= $p['alamat'] ?></td>
<td><?= $p['no_telp'] ?></td>
<td><?= $p['gol_darah'] ?></td>
<td><?= $p['umur'] ?></td>
<td><?= $p['jenis_kelamin'] ?></td>
<td>
<a href="?edit=<?= $p['kode_pasien'] ?>" class="update"><i class="fas fa-edit"></i></a>
<a href="?hapus=<?= $p['kode_pasien'] ?>" class="delete" onclick="return confirm('Hapus data ini?')">
<i class="fas fa-trash-alt"></i></a>
</td>
</tr>
<?php endwhile ?>
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
