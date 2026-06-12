<!-- CREATE, UPDATE, READ, DELETE Berada dalam 1 file php -->
<?php
session_start();
include "../config/koneksi.php";


if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// ===== VARIABEL ERROR & FORM =====
$kode_error = '';
$kode_value = '';
$nama_value = '';
$jenis_value = '';
$stok_value = '';
$harga_value = '';

// Tambah / Update Data Obat
if (isset($_POST['save'])) {
    $kode  = trim($_POST['kode_obat']);
    $nama  = $_POST['nama_obat'];
    $jenis = $_POST['jenis_obat'];
    $stok  = $_POST['stok'];
    $harga = $_POST['harga'];

    $kode_value  = $kode;
    $nama_value  = $nama;
    $jenis_value = $jenis;
    $stok_value  = $stok;
    $harga_value = $harga;

    if ($kode == '') {
        $kode_error = "Kode Obat wajib diisi!";
    }

    // TIDAK DAPAT CREATE/TAMBAH SAAT kode_obat DI DUPLIKASI KARENA MERUPAKAH PRIMARY KEY
    if ($kode_error == '' && empty($_POST['id_edit'])) {
        $cek = mysqli_prepare($koneksi, "SELECT kode_obat FROM obat WHERE kode_obat=?");
        mysqli_stmt_bind_param($cek, "s", $kode);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);
        if (mysqli_stmt_num_rows($cek) > 0) {
            $kode_error = "Kode Obat sudah ada, silakan gunakan kode lain.";
        }
        mysqli_stmt_close($cek);
    }

    if ($kode_error == '') {
        if (!empty($_POST['id_edit'])) {

            // Update obat
            $stmt = mysqli_prepare($koneksi,
                "UPDATE obat SET kode_obat=?, nama_obat=?, jenis_obat=?, stok=?, harga=? WHERE kode_obat=?");
            mysqli_stmt_bind_param($stmt, "sssdds", $kode, $nama, $jenis, $stok, $harga, $_POST['id_edit']);
        } else {

            // QUERY CREATE (INSERT)
            $stmt = mysqli_prepare($koneksi,
                "INSERT INTO obat (kode_obat, nama_obat, jenis_obat, stok, harga) VALUES (?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "sssdd", $kode, $nama, $jenis, $stok, $harga);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: data_obat.php");
        exit();
    }
}

// Hapus Data Obat
if (isset($_GET['hapus'])) {
    $kode = $_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM obat WHERE kode_obat=?");
    mysqli_stmt_bind_param($stmt, "s", $kode);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: data_obat.php");
    exit();
}

/* ===== READ untuk Menampilkan Data ke Tabel ===== */
$keyword = $_GET['search'] ?? '';
if ($keyword != '') {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM obat WHERE kode_obat LIKE ? OR nama_obat LIKE ? OR jenis_obat LIKE ? ORDER BY kode_obat ASC");
    $like = "%$keyword%";
    mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $data = mysqli_stmt_get_result($stmt);
} else {
    $data = mysqli_query($koneksi, "SELECT * FROM obat ORDER BY kode_obat ASC");
}

// Edit
$edit = null;
if (isset($_GET['edit'])) {
    $kode = $_GET['edit'];
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM obat WHERE kode_obat=?");
    mysqli_stmt_bind_param($stmt, "s", $kode);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $edit = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    $kode_value  = $edit['kode_obat'];
    $nama_value  = $edit['nama_obat'];
    $jenis_value = $edit['jenis_obat'];
    $stok_value  = $edit['stok'];
    $harga_value = $edit['harga'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Data Obat - Hospital Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
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

/* ===== SIDEBAR ===== */
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
    font-size:1.3rem;
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
    transition:0.3s;
}
.sidebar ul li a:hover,
.sidebar ul li a.active{background:#E67323;}
.sidebar ul li a i{width:25px; font-size:1.1rem; text-align:center; margin-right:15px;}
.sidebar.collapsed ul li a .label{display:none;}
.sidebar.collapsed ul li a:hover::after{
    content:attr(data-tooltip);
    position:absolute;
    left:100%; top:50%; transform:translateY(-50%);
    background:rgba(0,0,0,0.75); color:#FFF; padding:5px 10px; border-radius:5px; white-space:nowrap; z-index:10;
}

/* ===== MAIN CONTENT ===== */
.main{flex:1; padding:20px;}
.toggle-btn{
    position:absolute; top:15px; right:-20px; background:#E67323; color:#FFF; border-radius:50%;
    width:35px; height:35px; display:flex; justify-content:center; align-items:center;
    cursor:pointer; font-weight:bold; z-index:100;
}

/* ===== CONTENT ===== */
.content{
    background: rgba(255,242,224,.95);
    backdrop-filter: blur(6px);
    padding:25px 20px;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
}
.content h1{color:#B34D00; text-align:center; margin-bottom:25px;}

/* ===== SEARCH BOX ===== */
.search-box{display:flex; justify-content:center; margin-bottom:20px;}
.search-box input{padding:10px 15px; border-radius:30px; border:2px solid #C7742E; width:280px;}
.search-box button{margin-left:10px; padding:10px 28px; border-radius:30px; border:none; background:#FF8C42; color:#FFF6EE; font-weight:700; cursor:pointer;}
.search-box button:hover{background:#E67323;}

/* ===== FORM ===== */
.form-tambah{
    max-width:700px;
    margin:0 auto 30px;
    background:#FFF6EE;
    padding:25px 30px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(255,140,66,0.2);
}
.form-tambah input,
.form-tambah textarea{
    width:100%;
    padding:14px 20px;
    margin-bottom:18px;
    border-radius:15px;
    border:2px solid #FFB380;
    background:#FFF6EE;
    font-size:1rem;
    transition:all 0.3s ease;
    outline:none;
    box-shadow:inset 0 2px 5px rgba(0,0,0,0.05);
}
.form-tambah input:focus,
.form-tambah textarea:focus{
    border-color:#FF8C42;
    box-shadow:0 0 8px rgba(255,140,66,0.4);
}
.form-tambah input::placeholder,
.form-tambah textarea::placeholder{color:#B34D00; opacity:0.7; font-weight:500;}
.form-tambah span{display:block; font-size:0.85rem; color:red; margin-top:-12px; margin-bottom:12px;}
.button-group{text-align:center;}
.form-tambah button,
.button-link{
    display:inline-block;
    background: linear-gradient(135deg,#FF8C42,#FFB380);
    color:#FFF6EE;
    font-weight:700;
    padding:12px 36px;
    margin:10px 5px 0;
    border-radius:30px;
    border:none;
    cursor:pointer;
    transition:all 0.3s ease;
    box-shadow:0 6px 12px rgba(255,140,66,0.3);
}
.form-tambah button:hover,
.button-link:hover{
    background: linear-gradient(135deg,#E67323,#FF944D);
    box-shadow:0 8px 20px rgba(255,140,66,0.4);
    transform: translateY(-2px);
}

/* ===== TABLE ===== */
table{
    width:100%; border-collapse:collapse; background-color:#FFB380; color:#7A3E2E; border-radius:12px; overflow:hidden; box-shadow:0 6px 18px rgba(122,62,46,0.3); transition:all 0.3s ease;
}
table th, table td{padding:14px 18px; border-bottom:1px solid rgba(122,62,46,0.25);}
table th{background:#FF944D;}
table tr:nth-child(even){background:#FFC299;}
table tr:hover{background:#FFD2A6; transform:scale(1.01); transition:0.2s;}
table a{font-weight:600; text-decoration:none; padding:6px 10px; border-radius:8px; display:inline-flex; align-items:center; gap:5px;}
table a.update{background:#FF8C42; color:#FFF6EE;}
table a.update:hover{background:#E67323; color:#FFF6EE;}
table a.delete{background:#B34D00; color:#FFF6EE;}
table a.delete:hover{background:#9B3B00; color:#FFF6EE;}

@media(max-width:700px){.content{padding:15px;}}
</style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="logo">Hospital Admin</div>
    <div class="toggle-btn" onclick="toggleSidebar()">≡</div>
    <ul>
        <li><a href="dashboard.php" data-tooltip="Dashboard"><i class="fas fa-tachometer-alt"></i> <span class="label">Dashboard</span></a></li>
        <li><a href="data_dokter.php" data-tooltip="Data Dokter"><i class="fas fa-user-md"></i> <span class="label">Data Dokter</span></a></li>
        <li><a href="data_pasien.php" data-tooltip="Data Pasien"><i class="fas fa-users"></i> <span class="label">Data Pasien</span></a></li>
        <li><a href="data_obat.php" class="active" data-tooltip="Data Obat"><i class="fas fa-pills"></i> <span class="label">Data Obat</span></a></li>
        <li><a href="data_kamar.php" data-tooltip="Data Kamar"><i class="fas fa-bed"></i> <span class="label">Data Kamar</span></a></li>
        <li><a href="jadwal_dokter.php" data-tooltip="Jadwal Dokter"><i class="fas fa-calendar-alt"></i> <span class="label">Jadwal Dokter</span></a></li>
        <li><a href="laporan_rawat.php" data-tooltip="Laporan"><i class="fas fa-file-alt"></i> <span class="label">Laporan</span></a></li>
        <li><a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i> <span class="label">Logout</span></a></li>
    </ul>
</div>

<div class="main">
    <div class="content">
        <h1>Data Obat</h1>

        <div class="search-box">
            <form method="GET">
                <input type="text" name="search" placeholder="Cari Obat..." value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <form method="POST" class="form-tambah" action="">
            <input type="hidden" name="id_edit" value="<?= $edit['kode_obat'] ?? '' ?>">
            <input type="text" name="kode_obat" placeholder="Kode Obat" value="<?= htmlspecialchars($kode_value) ?>" required>
            <?php if($kode_error != ''): ?>
                <span><?= $kode_error ?></span>
            <?php endif; ?>
            <input type="text" name="nama_obat" placeholder="Nama Obat" value="<?= htmlspecialchars($nama_value) ?>" required>
            <input type="text" name="jenis_obat" placeholder="Jenis Obat" value="<?= htmlspecialchars($jenis_value) ?>" required>
            <input type="number" name="stok" placeholder="Stok Obat" value="<?= htmlspecialchars($stok_value) ?>" min="0" required>
            <input type="number" name="harga" placeholder="Harga Obat" value="<?= htmlspecialchars($harga_value) ?>" min="0" step="0.01" required>

            <div class="button-group">
                <button type="submit" name="save"><i class="fas fa-save"></i> <?= $edit ? 'Update' : 'Save' ?></button>
                <a href="data_obat.php" class="button-link"><i class="fas fa-plus"></i> New</a>
            </div>
        </form>

        <table>
            <tr>
                <th>No</th>
                <th>Kode Obat</th>
                <th>Nama Obat</th>
                <th>Jenis Obat</th>
                <th>Stok</th>
                <th>Harga</th>
                <th>Aksi</th>
            </tr>
            
<!-- READ untuk Menampilkan Data ke Tabel HTML -->
            <?php $no=1; while($d=mysqli_fetch_assoc($data)): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($d['kode_obat']) ?></td>
                <td><?= htmlspecialchars($d['nama_obat']) ?></td>
                <td><?= htmlspecialchars($d['jenis_obat']) ?></td>
                <td><?= $d['stok'] ?></td>
                <td>Rp <?= number_format($d['harga'],0,',','.') ?></td>
                <td>
                    <a href="?edit=<?= $d['kode_obat'] ?>" class="update"><i class="fas fa-edit"></i></a>
                    <a href="?hapus=<?= $d['kode_obat'] ?>" class="delete" onclick="return confirm('Hapus data ini?')"><i class="fas fa-trash-alt"></i></a>
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
