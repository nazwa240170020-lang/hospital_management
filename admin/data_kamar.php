<!-- CREATE, UPDATE, READ, DELETE Berada dalam 1 file php -->
<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

/* ===== VARIABEL ERROR & FORM ===== */
$nomor_error = '';
$nomor_value = '';
$tipe_value  = '';
$lantai_value= '';
$status_value= '';
$harga_value = '';

/* ===== SAVE / UPDATE ===== */
if (isset($_POST['save'])) {
    $nomor  = trim($_POST['nomor_kamar']);
    $tipe   = $_POST['tipe_kamar'];
    $lantai = $_POST['lantai'];
    $status = $_POST['status'];

    $nomor_value  = $nomor;
    $tipe_value   = $tipe;
    $lantai_value = $lantai;
    $status_value = $status;
    $harga_value = '';


    if ($nomor == '') {
        $nomor_error = "Nomor kamar wajib diisi!";
    }

    // TIDAK DAPAT CREATE/TAMBAH SAAT nomor_kamar DI DUPLIKASI KARENA MERUPAKAH PRIMARY KEY
    if ($nomor_error == '' && empty($_POST['id_edit'])) {
        $cek = mysqli_prepare($koneksi, "SELECT nomor_kamar FROM kamar WHERE nomor_kamar=?");
        mysqli_stmt_bind_param($cek, "i", $nomor);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);
        if (mysqli_stmt_num_rows($cek) > 0) {
            $nomor_error = "Nomor kamar sudah terdaftar!";
        }
        mysqli_stmt_close($cek);
    }

    if ($nomor_error == '') {
        if (!empty($_POST['id_edit'])) {

            // UPDATE
            $stmt = mysqli_prepare($koneksi,
                "UPDATE kamar SET nomor_kamar=?, tipe_kamar=?, lantai=?, status=? WHERE nomor_kamar=?");
            mysqli_stmt_bind_param($stmt, "isisi",
                $nomor,
                $tipe,
                $lantai,
                $status,
                $harga,
                $_POST['id_edit']
            );
        } else {

           // QUERY CREATE (INSERT)
            $stmt = mysqli_prepare($koneksi,
                "INSERT INTO kamar (nomor_kamar, tipe_kamar, lantai, status, harga) VALUES (?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "isis",
                $nomor,
                $tipe,
                $lantai,
                $status,
                $harga
            );
        }

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: data_kamar.php");
        exit();
    }
}

/* ===== DELETE ===== */
if (isset($_GET['hapus'])) {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM kamar WHERE nomor_kamar=?");
    mysqli_stmt_bind_param($stmt, "i", $_GET['hapus']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: data_kamar.php");
    exit();
}

/* ===== READ untuk Menampilkan Data ke Tabel ===== */ 
$keyword = $_GET['search'] ?? '';
if ($keyword != '') {
    $like = "%$keyword%";
    $stmt = mysqli_prepare($koneksi,
        "SELECT * FROM kamar 
         WHERE nomor_kamar LIKE ? OR tipe_kamar LIKE ? OR status LIKE ?
         ORDER BY nomor_kamar ASC");
    mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $data = mysqli_stmt_get_result($stmt);
} else {
    $data = mysqli_query($koneksi, "SELECT * FROM kamar ORDER BY nomor_kamar ASC");
}

/* ===== EDIT ===== */
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM kamar WHERE nomor_kamar=?");
    mysqli_stmt_bind_param($stmt, "i", $_GET['edit']);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $edit = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    $nomor_value   = $edit['nomor_kamar'];
    $tipe_value    = $edit['tipe_kamar'];
    $lantai_value  = $edit['lantai'];
    $status_value  = $edit['status'];
    $harga_value  = $edit['harga'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Data Kamar - Hospital Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
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
    transition:.3s;
}
.sidebar.collapsed{width:70px;}
.sidebar .logo{
    color:#FFF6EE;
    text-align:center;
    font-size:1.3rem;
    font-weight:bold;
    padding:20px;
}
.sidebar ul{list-style:none;padding:0;margin:0;}
.sidebar ul li a{
    display:flex;
    align-items:center;
    padding:15px 20px;
    color:#FFF1E8;
    text-decoration:none;
    font-weight:600;
}
.sidebar ul li a.active,
.sidebar ul li a:hover{background:#E67323;}
.sidebar ul li a i{width:25px;margin-right:15px;}
.sidebar.collapsed .label{display:none;}

/* MAIN */
.main{flex:1;padding:20px;}
.toggle-btn{
    position:absolute; top:15px; right:-20px;
    width:35px; height:35px;
    background:#E67323; color:#fff;
    border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;
}

/* CONTENT */
.content{
    background:rgba(255,242,224,.95);
    padding:25px;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
}
.content h1{text-align:center;color:#B34D00;margin-bottom:25px;}

/* SEARCH */
.search-box{text-align:center;margin-bottom:20px;}
.search-box input{
    padding:10px 15px;
    border-radius:30px;
    border:2px solid #C7742E;
    width:280px;
}
.search-box button{
    padding:10px 25px;
    border-radius:30px;
    border:none;
    background:#FF8C42;
    color:#fff;
    font-weight:bold;
}

/* FORM */
.form-tambah{
    max-width:700px;
    margin:0 auto 30px;
    background:#FFF6EE;
    padding:25px;
    border-radius:20px;
}
.form-tambah input,
.form-tambah select{
    width:100%;
    padding:14px;
    margin-bottom:15px;
    border-radius:15px;
    border:2px solid #FFB380;
}
.form-tambah span{color:red;font-size:.85rem;}
.button-group{text-align:center;}
.button-group button,
.button-link{
    background:linear-gradient(135deg,#FF8C42,#FFB380);
    color:#fff;
    font-weight:bold;
    padding:12px 35px;
    border-radius:30px;
    border:none;
    margin:5px;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    background:#FFB380;
    border-radius:12px;
}
th,td{padding:14px;text-align:center;}
th{background:#FF944D;}
tr:nth-child(even){background:#FFC299;}
a.update{background:#FF8C42;color:#fff;padding:6px 10px;border-radius:8px;}
a.delete{background:#B34D00;color:#fff;padding:6px 10px;border-radius:8px;}
</style>
</head>

<body>

<div class="sidebar" id="sidebar">
    <div class="logo">Hospital Admin</div>
    <div class="toggle-btn" onclick="toggleSidebar()">≡</div>
    <ul>
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span class="label">Dashboard</span></a></li>
        <li><a href="data_dokter.php"><i class="fas fa-user-md"></i><span class="label">Data Dokter</span></a></li>
        <li><a href="data_pasien.php"><i class="fas fa-users"></i><span class="label">Data Pasien</span></a></li>
        <li><a href="data_obat.php"><i class="fas fa-pills"></i><span class="label">Data Obat</span></a></li>
        <li><a href="data_kamar.php" class="active"><i class="fas fa-bed"></i><span class="label">Data Kamar</span></a></li>
        <li><a href="jadwal_dokter.php"><i class="fas fa-calendar-alt"></i><span class="label">Jadwal Dokter</span></a></li>
        <li><a href="laporan_rawat.php"><i class="fas fa-file-alt"></i><span class="label">Laporan</span></a></li>
        <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span class="label">Logout</span></a></li>
    </ul>
</div>

<div class="main">
<div class="content">
<h1>Data Kamar</h1>

<div class="search-box">
<form method="GET">
    <input type="text" name="search" placeholder="Cari Kamar..." value="<?= htmlspecialchars($keyword) ?>">
    <button type="submit">Search</button>
</form>
</div>

<form method="POST" class="form-tambah">
<input type="hidden" name="id_edit" value="<?= $edit['nomor_kamar'] ?? '' ?>">

<input type="number" name="nomor_kamar" placeholder="Nomor Kamar" value="<?= htmlspecialchars($nomor_value) ?>" required>
<?php if($nomor_error): ?><span><?= $nomor_error ?></span><?php endif; ?>

<input type="text" name="tipe_kamar" placeholder="Tipe Kamar" value="<?= htmlspecialchars($tipe_value) ?>" required>
<input type="number" name="lantai" placeholder="Lantai" value="<?= htmlspecialchars($lantai_value) ?>" required>

<select name="status" required>
    <option value="">-- Status --</option>
    <option value="Kosong" <?= $status_value=='Kosong'?'selected':'' ?>>Kosong</option>
    <option value="Terisi" <?= $status_value=='Terisi'?'selected':'' ?>>Terisi</option>
</select>

<input type="number" name="harga" placeholder="Harga Kamar" value="<?= htmlspecialchars($harga_value) ?>" required>

<div class="button-group">
<button type="submit" name="save"><i class="fas fa-save"></i> <?= $edit?'Update':'Save' ?></button>
<a href="data_kamar.php" class="button-link"><i class="fas fa-plus"></i> New</a>
</div>
</form>

<table>
<tr>
<th>No</th>
<th>Nomor</th>
<th>Tipe</th>
<th>Lantai</th>
<th>Status</th>
<th>Harga</th>
<th>Aksi</th>
</tr>

<!-- READ untuk Menampilkan Data ke Tabel HTML -->
<?php $no=1; while($d=mysqli_fetch_assoc($data)): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= $d['nomor_kamar'] ?></td>
<td><?= htmlspecialchars($d['tipe_kamar']) ?></td>
<td><?= $d['lantai'] ?></td>
<td><?= $d['status'] ?></td>
<td>Rp <?= number_format($d['harga'],0,',','.') ?></td>

<td>
<a href="?edit=<?= $d['nomor_kamar'] ?>" class="update"><i class="fas fa-edit"></i></a>
<a href="?hapus=<?= $d['nomor_kamar'] ?>" class="delete" onclick="return confirm('Hapus data?')"><i class="fas fa-trash"></i></a>
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