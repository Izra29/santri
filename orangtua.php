<?php
session_start();
include 'koneksi.php';
if($_SESSION['role'] != 'orangtua') header("location:index.php");

$santri_id = $_SESSION['santri_id'];
$anak = mysqli_fetch_assoc(mysqli_query($conn, "SELECT s.*, k.nama_kobong FROM santri s LEFT JOIN kobong k ON s.kobong_id=k.id WHERE s.id='$santri_id'"));
$in = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE santri_id='$santri_id' AND jenis='masuk'"))['t'];
$out = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE santri_id='$santri_id' AND jenis='keluar'"))['t'];
$saldo = $in - $out;
$bulan = $_GET['bulan'] ?? date('m'); $tahun = $_GET['tahun'] ?? date('Y');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Walisantri Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style> :root { --main: #0f5132; --gold: #d4ac0d; } .bg-islamic { background: linear-gradient(135deg, var(--main), #198754); color: white; } </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-islamic p-3 mb-4 shadow">
    <div class="container-fluid">
        <span class="navbar-brand"><i class="bi bi-person-heart"></i> Walisantri</span>
        <div><a href="profil.php" class="btn btn-warning btn-sm me-1"><i class="bi bi-gear"></i></a><a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a></div>
    </div>
</nav>
<div class="container">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-0 fw-bold"><?= $anak['nama_santri'] ?></h4>
                <p class="text-muted mb-0 small"><?= $anak['kelas'] ?> | <?= $anak['nama_kobong'] ?></p>
            </div>
            <div class="text-end">
                <small>Saldo</small><h2 class="text-primary fw-bold fs-4"><?= rupiah($saldo) ?></h2>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-success">Riwayat</h5>
            <form method="get" class="d-flex gap-1"><select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()"><?php for($i=1;$i<=12;$i++){ $b=str_pad($i,2,'0',STR_PAD_LEFT); echo "<option value='$b' ".($bulan==$b?'selected':'').">$b</option>"; } ?></select></form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive"> <table class="table table-striped mb-0">
                    <thead class="table-success"><tr><th>Tgl</th><th>Ket</th><th class="text-end">Masuk</th><th class="text-end">Keluar</th></tr></thead>
                    <tbody>
                        <?php $q=mysqli_query($conn, "SELECT * FROM transaksi WHERE santri_id='$santri_id' AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun' ORDER BY tanggal DESC");
                        if(mysqli_num_rows($q)==0) echo "<tr><td colspan='4' class='text-center'>Nihil</td></tr>";
                        while($r=mysqli_fetch_array($q)){ ?>
                        <tr>
                            <td><?= date('d/m', strtotime($r['tanggal'])) ?></td>
                            <td><?= $r['keterangan'] ?></td>
                            <td class="text-end text-success"><?= $r['jenis']=='masuk' ? rupiah($r['nominal']) : '-' ?></td>
                            <td class="text-end text-danger"><?= $r['jenis']=='keluar' ? rupiah($r['nominal']) : '-' ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>