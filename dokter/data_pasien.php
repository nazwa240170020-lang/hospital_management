
<!-- CREATE, UPDATE, READ, DELETE Berada dalam 1 file php --><?php
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
    echo "<script>
        alert('Profil dokter belum terhubung');
        window.location='daftar_dokter.php';
    </script>";
    exit();
}

$nip = $dokter['nip'];
$nama_dokter = htmlspecialchars($dokter['nama_dokter']);

/* ===== READ  ===== */
$pasien = mysqli_query($koneksi,"
    SELECT DISTINCT p.kode_pasien, p.nama_pasien, p.umur, p.jenis_kelamin,
           p.alamat, p.no_telp, p.gol_darah
    FROM rawat_inap r
    JOIN pasien p ON r.kode_pasien = p.kode_pasien
    WHERE r.nip='$nip'
    ORDER BY p.nama_pasien
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Pasien Dokter</title>

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
    background:rgba(255,242,224,.95);
    padding:25px;
    border-radius:22px;
    box-shadow:0 10px 30px rgba(255,140,66,.35);
}
.content h1{
    text-align:center;
    color:#B34D00;
    margin:0;
}
.content p{
    text-align:center;
    font-size:.9rem;
    opacity:.85;
    margin-top:4px;
}

/* ===== TABLE ===== */
.table-wrap{
    margin-top:22px;
    background:#fff;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 6px 18px rgba(0,0,0,.12);
}
table{
    width:100%;
    border-collapse:collapse;
}
th, td{
    padding:14px;
    border-bottom:1px solid #eee;
    font-size:.9rem;
}
th{
    background:#FF8C42;
    color:#fff;
    text-align:left;
}
tr:hover{ background:#FFF7EF; }

.badge{
    padding:4px 10px;
    border-radius:14px;
    font-size:.75rem;
    font-weight:700;
}
.badge.L{ background:#BBDEFB; color:#0D47A1; }
.badge.P{ background:#F8BBD0; color:#880E4F; }

/* EMPTY */
.empty{
    text-align:center;
    padding:40px;
    color:#777;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">👨‍⚕️ <?= $nama_dokter ?></div>
    <a href="dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
    <a href="daftar_dokter.php"><i class="fas fa-user"></i>Profil</a>
    <a class="active"><i class="fas fa-users"></i>Data Pasien</a>
    <a href="input_diagnosa.php"><i class="fas fa-stethoscope"></i>Diagnosa</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<!-- MAIN -->
<div class="main">
    <div class="content">
        <h1>Data Pasien</h1>
        <p>Pasien yang terdaftar dengan Anda</p>

        <?php if(mysqli_num_rows($pasien)>0): ?>
        <div class="table-wrap">
            <table>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Umur</th>
                    <th>JK</th>
                    <th>Gol. Darah</th>
                    <th>No. Telp</th>
                    <th>Alamat</th>
                </tr>
                <?php $no=1; while($p=mysqli_fetch_assoc($pasien)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $p['nama_pasien'] ?></td>
                    <td><?= $p['umur'] ?? '-' ?></td>
                    <td>
                        <span class="badge <?= $p['jenis_kelamin'] ?>">
                            <?= $p['jenis_kelamin']=='L'?'Laki-laki':'Perempuan' ?>
                        </span>
                    </td>
                    <td><?= $p['gol_darah'] ?? '-' ?></td>
                    <td><?= $p['no_telp'] ?? '-' ?></td>
                    <td><?= $p['alamat'] ?? '-' ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <?php else: ?>
            <div class="empty">
                <i class="fas fa-user-slash"></i><br><br>
                Belum ada pasien terdaftar
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
