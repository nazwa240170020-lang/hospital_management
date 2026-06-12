<!-- CREATE, UPDATE, READ, DELETE Berada dalam 1 file php -->
<?php 
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// === SAVE / UPDATE JADWAL DOKTER ===
if (isset($_POST['save'])) {
    $id_jadwal = $_POST['id_jadwal'];
    $nip       = $_POST['nip'];
    $hari      = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];

    if ($id_jadwal == '') {

        // QUERY CREATE (INSERT)
        $stmt = mysqli_prepare($koneksi, "INSERT INTO jadwal_dokter (nip,hari,jam_mulai,jam_selesai) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssss", $nip, $hari, $jam_mulai, $jam_selesai);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $stmt = mysqli_prepare($koneksi, "UPDATE jadwal_dokter SET nip=?, hari=?, jam_mulai=?, jam_selesai=? WHERE id_jadwal=?");
        mysqli_stmt_bind_param($stmt, "ssssi", $nip, $hari, $jam_mulai, $jam_selesai, $id_jadwal);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: jadwal_dokter.php");
    exit();
}

// === DELETE JADWAL ===
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM jadwal_dokter WHERE id_jadwal=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: jadwal_dokter.php");
    exit();
}

// === EDIT JADWAL ===
$edit = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

   
    // QUERY CREATE (INSERT)
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM jadwal_dokter WHERE id_jadwal=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $edit = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

// Ambil semua jadwal
$jadwal = mysqli_query($koneksi, "
    SELECT j.*, d.nama_dokter, d.spesialis, d.nip 
    FROM jadwal_dokter j
    JOIN dokter d ON j.nip = d.nip
    ORDER BY j.hari, j.jam_mulai
");

// Ambil semua dokter
$dokter = mysqli_query($koneksi, "SELECT * FROM dokter ORDER BY nama_dokter ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Jadwal Dokter - Hospital Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
*{box-sizing:border-box;}
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background: linear-gradient(rgba(0,0,0,.4),rgba(0,0,0,.4)),
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
    position:relative;
}
.sidebar .logo{
    color:#FFF6EE;
    text-align:center;
    font-size:1.3rem;
    font-weight:bold;
    padding:20px 10px;
    border-bottom:1px solid rgba(255,255,255,0.3);
}
.sidebar ul{
    list-style:none;
    padding:0;
    margin:0;
}
.sidebar ul li a{
    display:flex;
    align-items:center;
    padding:15px 20px;
    color:#FFF6EE;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
}
.sidebar ul li a:hover,
.sidebar ul li a.active{
    background:#E67323;
}
.sidebar ul li a i{
    width:25px;
    font-size:1.1rem;
    text-align:center;
    margin-right:15px;
}

/* ===== MAIN CONTENT ===== */
.main{flex:1; padding:20px;}
.content{
    background: rgba(255,242,224,.95);
    backdrop-filter: blur(6px);
    padding:25px 20px;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
}
.content h1{color:#B34D00; text-align:center; margin-bottom:25px;}

/* ===== FORM TAMBAH ===== */
.form-tambah{
    max-width:700px;
    margin:0 auto 30px;
    background:#FFF6EE;
    padding:25px 30px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(255,140,66,0.2);
    display:flex;
    flex-wrap:wrap;
    gap:15px;
    justify-content:space-between;
}
.form-tambah select, 
.form-tambah input[type="text"], 
.form-tambah input[type="time"]{
    width: calc(50% - 10px);
    padding:12px 15px;
    border-radius:12px;
    border:2px solid #C7742E;
    font-size:1rem;
    outline:none;
    transition:0.3s;
}
.form-tambah select:focus,
.form-tambah input:focus{
    border-color:#FF8C42;
}
.button-group{
    display:flex;
    justify-content:space-between;
    width:100%;
    margin-top:10px;
}
.button-group button{
    width:48%;
    padding:12px 0;
    border-radius:30px;
    border:none;
    font-weight:700;
    font-size:1.1rem;
    cursor:pointer;
    box-shadow:0 5px 15px rgba(255,140,66,0.6);
    transition:0.3s;
}
.button-group button.save{
    background:#FF8C42;
    color:#FFF6EE;
}
.button-group button.save:hover{
    background:#E67323;
    box-shadow:0 7px 25px rgba(230,115,35,0.9);
}
.button-link{
    display:inline-block;
    background: linear-gradient(135deg,#FF8C42,#FFB380);
    color:#FFF6EE;
    font-weight:700;
    padding:12px 36px;
    border-radius:30px;
    text-decoration:none;
    transition:all 0.3s ease;
    box-shadow:0 6px 12px rgba(255,140,66,0.3);
    font-size:1rem;
    text-align:center;
}
.button-link:hover{
    background: linear-gradient(135deg,#E67323,#FF944D);
    box-shadow:0 8px 20px rgba(255,140,66,0.4);
    transform: translateY(-2px);
}

/* ===== TABLE ===== */
table{
    width:100%;
    border-collapse:collapse;
    background-color:#FFB380;
    color:#7A3E2E;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 6px 18px rgba(122,62,46,0.3);
}
table th, table td{padding:14px 18px; border-bottom:1px solid rgba(122,62,46,0.25);}
table th{background:#FF944D; font-weight:700;}
table tr:nth-child(even){background:#FFC299;}
table tr:hover{background:#FFD2A6; transition:0.2s;}
table a{
    font-weight:600;
    text-decoration:none;
    padding:6px 12px;
    border-radius:8px;
    transition:0.3s;
}
table a.update{background:#FF8C42; color:#FFF6EE;}
table a.update:hover{background:#E67323; color:#FFF6EE;}
table a.delete{background:#B34D00; color:#FFF6EE;}
table a.delete:hover{background:#9B3B00; color:#FFF6EE;}

@media(max-width:700px){
    .form-tambah{flex-direction:column;}
    .form-tambah select,
    .form-tambah input[type="text"],
    .form-tambah input[type="time"]{width:100%;}
    .button-group{flex-direction:column;}
    .button-group button{width:100%; margin:5px 0;}
}
</style>
</head>
<body>

<div class="sidebar">
    <div class="logo">Hospital Admin</div>
    <ul>
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="data_dokter.php"><i class="fas fa-user-md"></i> Data Dokter</a></li>
        <li><a href="data_pasien.php"><i class="fas fa-users"></i> Data Pasien</a></li>
        <li><a href="data_obat.php"><i class="fas fa-pills"></i> Data Obat</a></li>
        <li><a href="data_kamar.php"><i class="fas fa-bed"></i> Data Kamar</a></li>
        <li><a href="jadwal_dokter.php" class="active"><i class="fas fa-calendar-alt"></i> Jadwal Dokter</a></li>
        <li><a href="laporan_rawat.php"><i class="fas fa-file-alt"></i> Laporan</a></li>
        <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>

<div class="main">
    <div class="content">
        <h1>Jadwal Dokter</h1>

        <?php if(!$edit){ ?>
            <a href="?tambah=1" class="button-link"><i class="fas fa-plus"></i> Tambah Jadwal</a>
        <?php } ?>

        <?php if($edit || isset($_GET['tambah'])): ?>
        <form method="POST" class="form-tambah">
            <input type="hidden" name="id_jadwal" value="<?= $edit ? htmlspecialchars($edit['id_jadwal']) : '' ?>">
            <select name="nip" required>
                <option value="">-- Pilih Dokter --</option>
                <?php while ($d = mysqli_fetch_assoc($dokter)) { ?>
                    <option value="<?= $d['nip'] ?>" <?= $edit && $edit['nip']==$d['nip'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d['nama_dokter']) ?> (<?= htmlspecialchars($d['spesialis']) ?>) | NIP: <?= htmlspecialchars($d['nip']) ?>
                    </option>
                <?php } ?>
            </select>
            <select name="hari" required>
    <option value="">-- Pilih Hari --</option>
    <option value="Senin" <?= $edit && $edit['hari']=='Senin' ? 'selected' : '' ?>>Senin</option>
    <option value="Selasa" <?= $edit && $edit['hari']=='Selasa' ? 'selected' : '' ?>>Selasa</option>
    <option value="Rabu" <?= $edit && $edit['hari']=='Rabu' ? 'selected' : '' ?>>Rabu</option>
    <option value="Kamis" <?= $edit && $edit['hari']=='Kamis' ? 'selected' : '' ?>>Kamis</option>
    <option value="Jumat" <?= $edit && $edit['hari']=='Jumat' ? 'selected' : '' ?>>Jumat</option>
    <option value="Sabtu" <?= $edit && $edit['hari']=='Sabtu' ? 'selected' : '' ?>>Sabtu</option>
    <option value="Minggu" <?= $edit && $edit['hari']=='Minggu' ? 'selected' : '' ?>>Minggu</option>
</select>

            <input type="time" name="jam_mulai" value="<?= $edit ? htmlspecialchars($edit['jam_mulai']) : '' ?>" required>
            <input type="time" name="jam_selesai" value="<?= $edit ? htmlspecialchars($edit['jam_selesai']) : '' ?>" required>
            <div class="button-group">
                <button type="submit" name="save" class="save"><?= $edit ? 'Update Jadwal' : 'Simpan Jadwal' ?></button>
                <a href="jadwal_dokter.php" class="button-link"><i class="fas fa-times"></i> Batal</a>
            </div>
        </form>
        <?php endif; ?>

        <table>
            <tr>
                <th>No</th>
                <th>NIP</th>
                <th>Nama Dokter</th>
                <th>Spesialis</th>
                <th>Hari</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th>Aksi</th>
            </tr>
            
<!-- READ untuk Menampilkan Data ke Tabel HTML -->
            <?php $no=1; while($j = mysqli_fetch_assoc($jadwal)): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($j['nip']) ?></td>
                <td><?= htmlspecialchars($j['nama_dokter']) ?></td>
                <td><?= htmlspecialchars($j['spesialis']) ?></td>
                <td><?= htmlspecialchars($j['hari']) ?></td>
                <td><?= htmlspecialchars($j['jam_mulai']) ?></td>
                <td><?= htmlspecialchars($j['jam_selesai']) ?></td>
                <td>
                    <a href="?edit=<?= urlencode($j['id_jadwal']) ?>" class="update"><i class="fas fa-edit"></i> Update</a>
                    <a href="?hapus=<?= urlencode($j['id_jadwal']) ?>" class="delete" onclick="return confirm('Hapus jadwal ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
