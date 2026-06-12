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

$pasien = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pasien WHERE id_user='$id_user'"));


/* ===== SAVE / UPDATE ===== */
if (isset($_POST['submit'])) {
    $nama_pasien   = mysqli_real_escape_string($koneksi, $_POST['nama_pasien']);
    $umur          = mysqli_real_escape_string($koneksi, $_POST['umur']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $alamat        = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_telp       = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $gol_darah     = mysqli_real_escape_string($koneksi, $_POST['gol_darah']);

    if ($pasien) {
        mysqli_query($koneksi,"UPDATE pasien SET
            nama_pasien='$nama_pasien',
            umur='$umur',
            jenis_kelamin='$jenis_kelamin',
            alamat='$alamat',
            no_telp='$no_telp',
            gol_darah='$gol_darah'
            WHERE id_user='$id_user'");
    } else {

        // QUERY CREATE (INSERT)
        mysqli_query($koneksi,"INSERT INTO pasien
            (id_user,nama_pasien,umur,jenis_kelamin,alamat,no_telp,gol_darah)
            VALUES
            ('$id_user','$nama_pasien','$umur','$jenis_kelamin','$alamat','$no_telp','$gol_darah')");
    }

    echo "<script>alert('Profil berhasil disimpan');location='daftar_pasien.php';</script>";
    exit();
}

$nama_tampil = htmlspecialchars($pasien['nama_pasien'] ?? 'Pasien');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Pasien</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:
      linear-gradient(rgba(0,0,0,.55),rgba(0,0,0,.55)),
      url("../assets/RS_Pondok_Indah_-_Pondok_Indah.jpg") center/cover fixed;
    display:flex;
}

/* ===== SIDEBAR ===== */
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
.main{
    flex:1;
    padding:30px;
    margin-left:10px;
}

/* HEADER */
.header{
    text-align:center;
    color:#fff;
    margin-bottom:25px;
}
.header h1{margin:0;font-size:1.6rem;}
.header p{margin-top:4px;opacity:.9;font-size:.95rem;}

/* CONTENT */
.content{
    background:rgba(255,255,255,.82);
    backdrop-filter:blur(16px);
    border-radius:22px;
    padding:25px;
    box-shadow:0 20px 40px rgba(0,0,0,.25);
}

/* FORM */
.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
    margin-bottom:20px;
}
.form-grid input,
.form-grid select,
.form-grid textarea{
    width:100%;
    padding:14px;
    border-radius:14px;
    border:none;
    background:rgba(255,255,255,.9);
    font-size:.95rem;
}
.form-grid textarea{
    grid-column:1 / -1;
    resize:none;
}
.form-actions{
    text-align:center;
}
.form-actions button{
    padding:14px 28px;
    border-radius:30px;
    background:#FF8C42;
    color:#fff;
    border:none;
    font-weight:700;
    cursor:pointer;
}

/* PROFILE VIEW */
.profile-box{
    margin-top:25px;
    background:rgba(255,255,255,.78);
    backdrop-filter:blur(14px);
    padding:20px;
    border-radius:18px;
}
.profile-box table{
    width:100%;
    border-collapse:collapse;
}
.profile-box td{
    padding:10px;
    border-bottom:1px solid rgba(0,0,0,.08);
}
.profile-box a{
    display:inline-block;
    margin-top:15px;
    padding:12px 24px;
    background:#FF8C42;
    color:#fff;
    border-radius:30px;
    text-decoration:none;
    font-weight:700;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">👤 <?= $nama_tampil ?></div>
    <a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
    <a class="active"><i class="fas fa-user"></i>Profil</a>
    <a href="daftar_rawat.php"><i class="fas fa-notes-medical"></i>Rawat Inap</a>
    <a href="riwayat_rawat.php"><i class="fas fa-clock-rotate-left"></i>Riwayat</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<!-- MAIN -->
<div class="main">
    <div class="header">
        <h1>Profil Saya</h1>
        <p>Kelola data pribadi pasien</p>
    </div>

    <div class="content">

        <form method="POST">
            <div class="form-grid">
                <input type="text" name="nama_pasien" value="<?= $pasien['nama_pasien'] ?? '' ?>" placeholder="Nama" required>
                <input type="number" name="umur" value="<?= $pasien['umur'] ?? '' ?>" placeholder="Umur" required>

                <select name="jenis_kelamin" required>
                    <option value="">Jenis Kelamin</option>
                    <option value="L" <?= ($pasien['jenis_kelamin'] ?? '')=='L'?'selected':'' ?>>Laki-laki</option>
                    <option value="P" <?= ($pasien['jenis_kelamin'] ?? '')=='P'?'selected':'' ?>>Perempuan</option>
                </select>

                <select name="gol_darah">
                    <option value="">Golongan Darah</option>
                    <?php foreach(['A','B','AB','O'] as $g): ?>
                        <option value="<?= $g ?>" <?= ($pasien['gol_darah'] ?? '')==$g?'selected':'' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                </select>

                <textarea name="alamat" rows="3" placeholder="Alamat" required><?= $pasien['alamat'] ?? '' ?></textarea>
                <input type="text" name="no_telp" value="<?= $pasien['no_telp'] ?? '' ?>" placeholder="No. Telepon">
            </div>

            <div class="form-actions">
                <button type="submit" name="submit">Simpan Profil</button>
            </div>
        </form>

        <?php if($pasien): ?>
        <div class="profile-box">
            <h3>Data Profil</h3>
            <table>
                <tr><td>Nama</td><td><?= $pasien['nama_pasien'] ?></td></tr>
                <tr><td>Umur</td><td><?= $pasien['umur'] ?></td></tr>
                <tr><td>Jenis Kelamin</td><td><?= $pasien['jenis_kelamin']=='L'?'Laki-laki':'Perempuan' ?></td></tr>
                <tr><td>Alamat</td><td><?= $pasien['alamat'] ?></td></tr>
                <tr><td>No. Telp</td><td><?= $pasien['no_telp'] ?></td></tr>
                <tr><td>Gol. Darah</td><td><?= $pasien['gol_darah'] ?></td></tr>
            </table>

            <a href="daftar_rawat.php">➕ Daftar Rawat Inap</a>
        </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
