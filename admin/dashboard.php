<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// ===== DATA COUNT =====
$jumlah_dokter = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM dokter"));
$jumlah_pasien = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pasien"));
$jumlah_rawat  = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM rawat_inap"));
$jumlah_obat   = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM obat"));
$jumlah_kamar  = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM kamar"));

// ===== DATA KAMAR (Pie Chart) =====
$kamar_kosong = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM kamar WHERE status='kosong'"));
$kamar_terisi = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM kamar WHERE status='terisi'"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin - Bukit Indah Hospital</title>

<!-- Font Awesome untuk ikon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
.sidebar.collapsed{
    width:70px;
}
.sidebar .logo{
    color:#FFF6EE;
    text-align:center;
    font-size:1.2rem;
    font-weight:bold;
    padding:20px 10px;
    border-bottom:1px solid rgba(255,255,255,0.3);
}
.sidebar ul{
    list-style:none;
    padding:0;
    margin:0;
}
.sidebar ul li{
    position:relative;
}
.sidebar ul li a{
    display:flex;
    align-items:center;
    padding:15px 20px;
    color:#FFF1E8;
    text-decoration:none;
    font-weight:600;
    transition:0.3s;
    white-space:nowrap;
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
    transition: transform 0.3s;
}
/* Tooltip saat collapse */
.sidebar.collapsed ul li a .label{
    display:none;
}
.sidebar.collapsed ul li a:hover::after{
    content:attr(data-tooltip);
    position:absolute;
    left:100%;
    top:50%;
    transform:translateY(-50%);
    background:rgba(0,0,0,0.75);
    color:#FFF;
    padding:5px 10px;
    border-radius:5px;
    white-space:nowrap;
    z-index:10;
}

/* ===== MAIN CONTENT ===== */
.main{
    flex:1;
    padding:20px;
}
.toggle-btn{
    position:absolute;
    top:15px;
    right:-20px;
    background:#E67323;
    color:#FFF;
    border-radius:50%;
    width:35px;
    height:35px;
    display:flex;
    justify-content:center;
    align-items:center;
    cursor:pointer;
    font-weight:bold;
    transition:0.3s;
    z-index:100;
}

/* ===== CONTENT CARDS ===== */
.content{
    background:rgba(255,242,224,.95);
    padding:25px 20px;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(255,140,66,.35);
    animation:fadeIn .8s ease;
}
.content h1{
    text-align:center;
    color:#B34D00;
    font-size:1.4rem;
}
.cards{
    display:grid;
    grid-template-columns:1fr;
    gap:18px;
    margin:25px 0;
}
.card{
    background:rgba(255,179,128,.85);
    padding:25px;
    border-radius:18px;
    text-align:center;
    box-shadow:0 8px 22px rgba(255,140,66,.45);
    transition:transform 0.3s, box-shadow 0.3s;
}
.card:hover{
    transform:translateY(-5px);
    box-shadow:0 12px 28px rgba(255,140,66,.6);
}
.card h3{margin:0;font-size:1rem;}
.card .number{
    font-size:3rem;
    font-weight:800;
    color:#B34D00;
}

/* ===== CHARTS ===== */
.charts-container{
    display:grid;
    grid-template-columns:1fr;
    gap:20px;
    margin-top:30px;
}
.chart-box{
    background:#FFF;
    padding:20px;
    border-radius:16px;
    box-shadow:0 6px 18px rgba(0,0,0,.15);
}
.chart-box canvas{
    width:100%!important;
    height:320px!important;
}

/* ===== DESKTOP ===== */
@media(min-width:768px){
    .cards{grid-template-columns:repeat(auto-fit,minmax(220px,1fr));}
    .charts-container{grid-template-columns:1fr 1fr;}
}

@keyframes fadeIn{
    from{opacity:0;transform:translateY(15px);}
    to{opacity:1;transform:translateY(0);}
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="logo">Hospital Admin</div>
    <div class="toggle-btn" onclick="toggleSidebar()">≡</div>
    <ul>
        <li><a href="dashboard.php" class="active" data-tooltip="Dashboard"><i class="fas fa-tachometer-alt"></i> <span class="label">Dashboard</span></a></li>
        <li><a href="data_dokter.php" data-tooltip="Data Dokter"><i class="fas fa-user-md"></i> <span class="label">Data Dokter</span></a></li>
        <li><a href="data_pasien.php" data-tooltip="Data Pasien"><i class="fas fa-users"></i> <span class="label">Data Pasien</span></a></li>
        <li><a href="data_obat.php" data-tooltip="Data Obat"><i class="fas fa-pills"></i> <span class="label">Data Obat</span></a></li>
        <li><a href="data_kamar.php" data-tooltip="Data Kamar"><i class="fas fa-bed"></i> <span class="label">Data Kamar</span></a></li>
        <li><a href="jadwal_dokter.php" data-tooltip="Jadwal Dokter"><i class="fas fa-calendar-alt"></i> <span class="label">Jadwal Dokter</span></a></li>
        <li><a href="laporan_rawat.php" data-tooltip="Laporan Rawat"><i class="fas fa-file-alt"></i> <span class="label">Laporan</span></a></li>
        <li><a href="../logout.php" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i> <span class="label">Logout</span></a></li>
    </ul>
</div>

<!-- MAIN CONTENT -->
<div class="main">
    <div class="content">
        <h1>Dashboard Admin Bukit Indah Hospital</h1>
        <div class="cards">
            <div class="card"><h3> Dokter</h3><div class="number"><?= $jumlah_dokter ?></div></div>
            <div class="card"><h3> Pasien</h3><div class="number"><?= $jumlah_pasien ?></div></div>
            <div class="card"><h3> Rawat Inap</h3><div class="number"><?= $jumlah_rawat ?></div></div>
            <div class="card"><h3> Obat</h3><div class="number"><?= $jumlah_obat ?></div></div>
            <div class="card"><h3> Kamar</h3><div class="number"><?= $jumlah_kamar ?></div></div>
        </div>

        <div class="charts-container">
            <div class="chart-box">
                <h3>📊 Statistik Rumah Sakit</h3>
                <canvas id="chartData"></canvas>
            </div>
            <div class="chart-box">
                <h3>🥧 Status Kamar</h3>
                <canvas id="chartKamar"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Sidebar toggle
function toggleSidebar(){
    document.getElementById('sidebar').classList.toggle('collapsed');
}

// Counter animasi
document.querySelectorAll('.number').forEach(counter=>{
    const target=+counter.innerText;
    counter.innerText=0;
    const run=()=>{
        const now=+counter.innerText;
        const inc=Math.ceil(target/40);
        if(now<target){
            counter.innerText=now+inc;
            setTimeout(run,25);
        }else counter.innerText=target;
    };
    run();
});

// Bar Chart
new Chart(document.getElementById('chartData'),{
    type:'bar',
    data:{
        labels:['Dokter','Pasien','Rawat Inap','Obat','Kamar'],
        datasets:[{
            label:'Statistik Rumah Sakit',
            data:[
                <?= $jumlah_dokter ?>,
                <?= $jumlah_pasien ?>,
                <?= $jumlah_rawat ?>,
                <?= $jumlah_obat ?>,
                <?= $jumlah_kamar ?>
            ],
            backgroundColor:['#FF8C42','#FFB380','#FF7043','#FFA726','#FFCC80'],
            borderRadius:12
        }]
    },
    options:{
        responsive:true,
        plugins:{legend:{display:false}},
        scales:{y:{beginAtZero:true}}
    }
});

// Pie Chart Kamar
new Chart(document.getElementById('chartKamar'),{
    type:'doughnut',
    data:{
        labels:['Kosong','Terisi'],
        datasets:[{
            data:[<?= $kamar_kosong ?>, <?= $kamar_terisi ?>],
            backgroundColor:['#66BB6A','#FF7043'],
            borderWidth:0
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        cutout:'65%',
        plugins:{legend:{position:'bottom'}}
    }
});
</script>

</body>
</html>
