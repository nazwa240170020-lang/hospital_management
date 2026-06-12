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
    mysqli_query($koneksi,"SELECT * FROM dokter WHERE id_user='$id_user'")
);

/* ===== READ  ===== */
$list_dokter = mysqli_query($koneksi,"
    SELECT nip, nama_dokter, spesialis
    FROM dokter
    WHERE id_user IS NULL
    ORDER BY nama_dokter
");


/* ===== SAVE / UPDATE ===== */
if (isset($_POST['submit'])) {
    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);

    mysqli_query($koneksi,"
        UPDATE dokter
        SET id_user='$id_user'
        WHERE nip='$nip' AND id_user IS NULL
    ");

    header("Location: daftar_dokter.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Dokter</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<style>
*{box-sizing:border-box}
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:
      linear-gradient(rgba(0,0,0,.45),rgba(0,0,0,.45)),
      url("../assets/RS_Pondok_Indah_-_Pondok_Indah.jpg") center/cover fixed;
    display:flex;
}

/* ===== SIDEBAR ===== */
.sidebar{
    width:250px;
    background:#FF8C42;
    min-height:100vh;
}
.sidebar .logo{
    color:#FFF6EE;
    text-align:center;
    font-size:1.2rem;
    font-weight:bold;
    padding:20px;
    border-bottom:1px solid rgba(255,255,255,.3);
}
.sidebar a{
    display:flex;
    align-items:center;
    gap:10px;
    padding:15px 20px;
    color:#FFF1E8;
    text-decoration:none;
    font-weight:600;
}
.sidebar a:hover,
.sidebar a.active{ background:#E67323; }

/* ===== MAIN ===== */
.main{
    flex:1;
    padding:20px;
}
.content{
    max-width:520px;
    margin:40px auto;
    background:rgba(255,242,224,.95);
    padding:28px;
    border-radius:22px;
    box-shadow:0 10px 30px rgba(255,140,66,.35);
}
.content h1{
    text-align:center;
    color:#B34D00;
    margin:0 0 6px;
}
.content p{
    text-align:center;
    font-size:.9rem;
    opacity:.85;
    margin-bottom:20px;
}

/* ===== FORM ===== */
select,button{
    width:100%;
    padding:14px;
    border-radius:14px;
    border:none;
    font-size:.95rem;
}
select{ background:#fff; }
button{
    margin-top:16px;
    background:#FF8C42;
    color:#fff;
    font-weight:700;
    cursor:pointer;
}
button:hover{ background:#E67323; }

/* ===== PROFILE ===== */
.profile-box{
    background:rgba(255,255,255,.9);
    border-radius:18px;
    padding:20px;
}
.profile-box table{
    width:100%;
    border-collapse:collapse;
}
.profile-box td{
    padding:10px;
    border-bottom:1px solid rgba(0,0,0,.08);
}
.profile-box td:first-child{
    font-weight:600;
    color:#7A3E2E;
}

.actions{
    margin-top:22px;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
}
.actions a{
    text-align:center;
    padding:14px;
    border-radius:30px;
    text-decoration:none;
    font-weight:700;
    color:#fff;
}
.actions .pasien{ background:#28A745; }
.actions .dashboard{ background:#FF8C42; }
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">Hospital Dokter</div>
    <a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
    <a class="active"><i class="fas fa-user"></i>Profil</a>
    <a href="data_pasien.php"><i class="fas fa-users"></i>Data Pasien</a>
    <a href="input_diagnosa.php"><i class="fas fa-stethoscope"></i>Diagnosa</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<!-- MAIN -->
<div class="main">
    <div class="content">

<?php if(!$dokter): ?>

        <h1>Pilih Profil Dokter</h1>
        <p>Pilih nama dokter yang telah didaftarkan oleh admin</p>

        <form method="POST">
            <select name="nip" required>
                <option value="">-- Pilih Nama Dokter --</option>
                <?php while($d=mysqli_fetch_assoc($list_dokter)): ?>
                    <option value="<?= $d['nip'] ?>">
                        <?= $d['nama_dokter'] ?> (<?= $d['spesialis'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit" name="submit">Hubungkan Profil</button>
        </form>

<?php else: ?>

        <h1>Profil Dokter</h1>
        <p>Data profil yang terhubung dengan akun Anda</p>

        <div class="profile-box">
            <table>
                <tr><td>Nama</td><td><?= $dokter['nama_dokter'] ?></td></tr>
                <tr><td>Spesialis</td><td><?= $dokter['spesialis'] ?></td></tr>
                <tr><td>No. Telp</td><td><?= $dokter['no_telp'] ?: '-' ?></td></tr>
                <tr><td>Alamat</td><td><?= $dokter['alamat'] ?: '-' ?></td></tr>
            </table>
        </div>

        <div class="actions">
            <a href="data_pasien.php" class="pasien">
                <i class="fas fa-users"></i> Data Pasien
            </a>
            <a href="dashboard.php" class="dashboard">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>

<?php endif; ?>

    </div>
</div>

</body>
</html>
