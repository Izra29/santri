<?php
session_start();
include 'koneksi.php';
if($_SESSION['role'] != 'superadmin') header("location:index.php");

// LOGIC DATA (Sama seperti sebelumnya)
$jml_santri = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM santri WHERE status='aktif'"));
$jml_alumni = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM santri WHERE status='lulus'"));
$jml_pengurus = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE role='pengurus'"));
$jml_sekbid = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE role IN ('keamanan','kebersihan','ubudiah','kurikulum','peralatan','rois')"));

$in_jajan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE jenis='masuk'"))['t'];
$out_jajan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE jenis='keluar'"))['t'];
$saldo_jajan = $in_jajan - $out_jajan;

$bln_ini = date('m'); $thn_ini = date('Y');
$syahriah_bln = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as t FROM syahriah WHERE bulan='$bln_ini' AND tahun='$thn_ini'"))['t'];
$keluar_bln = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE jenis='keluar' AND MONTH(tanggal)='$bln_ini' AND YEAR(tanggal)='$thn_ini'"))['t'];

$q_harian = mysqli_query($conn, "SELECT DATE(tanggal) as tgl, SUM(CASE WHEN jenis='masuk' THEN nominal ELSE 0 END) as tot_masuk, SUM(CASE WHEN jenis='keluar' THEN nominal ELSE 0 END) as tot_keluar FROM transaksi GROUP BY DATE(tanggal) ORDER BY tgl DESC LIMIT 7");

$jabatan_wajib = ['rois','sekretaris','kurikulum','ubudiah','kebersihan','peralatan','keamanan'];
$sudah_lapor_arr = [];
$q_lapor = mysqli_query($conn, "SELECT * FROM laporan_bulanan WHERE bulan='$bln_ini' AND tahun='$thn_ini'");
while($l = mysqli_fetch_assoc($q_lapor)){ $sudah_lapor_arr[] = $l['role']; }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Pimpinan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; --dark: #0a3622; }
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        
        /* SIDEBAR DESKTOP & MOBILE */
        .sidebar { min-width: 260px; background: var(--main); color: white; min-height: 100vh; position: fixed; z-index: 999; transition: 0.3s; }
        .sidebar .brand { padding: 20px; font-size: 1.4rem; font-weight: bold; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar a { color: rgba(255,255,255,0.85); text-decoration: none; display: block; padding: 15px 20px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: var(--dark); border-left: 5px solid var(--gold); color: white; }
        
        .main-content { margin-left: 260px; padding: 30px; transition: 0.3s; }
        .card-stat { border: none; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .icon-box { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; }

        /* RESPONSIF UNTUK HP (Layar < 768px) */
        @media (max-width: 768px) {
            .sidebar { margin-left: -260px; } /* Sembunyikan sidebar */
            .sidebar.active { margin-left: 0; } /* Munculkan jika aktif */
            .main-content { margin-left: 0; padding: 15px; }
            .mobile-nav { display: block !important; } /* Tampilkan tombol menu */
        }
        .mobile-nav { display: none; background: var(--main); color: white; padding: 10px 20px; margin-bottom: 20px; border-radius: 10px; }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="brand d-flex justify-content-between align-items-center">
        <span><i class="bi bi-buildings"></i> Pimpinan</span>
        <button class="btn btn-sm text-white d-md-none" onclick="toggleMenu()"><i class="bi bi-x-lg"></i></button>
    </div>
    <a href="#" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <small class="text-white-50 ms-3 mt-3 d-block">MONITORING</small>
    <a href="admin.php"><i class="bi bi-wallet2 me-2"></i> Keuangan</a>
    <a href="admin_data.php"><i class="bi bi-database me-2"></i> Data Master</a>
    <small class="text-white-50 ms-3 mt-3 d-block">AKUN</small>
    <a href="profil.php"><i class="bi bi-gear me-2"></i> Pengaturan</a>
    <a href="logout.php" class="text-danger bg-dark mt-3"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
</div>

<div class="main-content">
    
    <div class="mobile-nav d-flex justify-content-between align-items-center shadow-sm" onclick="toggleMenu()">
        <span class="fw-bold"><i class="bi bi-list fs-4"></i> Menu Pimpinan</span>
    </div>

    <h4 class="fw-bold text-dark mb-4 mt-2">Ringkasan Pondok</h4>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card card-stat bg-white p-3 h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary me-3"><i class="bi bi-piggy-bank"></i></div>
                    <div><small class="text-muted d-block">Saldo Uang Jajan</small><h4 class="fw-bold text-primary mb-0"><?= rupiah($saldo_jajan) ?></h4></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-stat bg-white p-3 h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-success bg-opacity-10 text-success me-3"><i class="bi bi-cash-coin"></i></div>
                    <div><small class="text-muted d-block">Pemasukan Syahriah</small><h4 class="fw-bold text-success mb-0"><?= rupiah($syahriah_bln??0) ?></h4></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-stat bg-white p-3 h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning me-3"><i class="bi bi-people"></i></div>
                    <div><small class="text-muted d-block">Santri Aktif</small><h4 class="fw-bold text-dark mb-0"><?= $jml_santri ?></h4></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="card card-stat border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3">Rekapan Keuangan Harian</div>
                <div class="card-body p-0">
                    <div class="table-responsive"> <table class="table table-striped mb-0 text-center" style="min-width: 400px;">
                            <thead class="table-light"><tr><th>Tanggal</th><th>Masuk</th><th>Keluar</th><th>Selisih</th></tr></thead>
                            <tbody>
                                <?php if(mysqli_num_rows($q_harian)>0): while($h=mysqli_fetch_array($q_harian)){ $sel=$h['tot_masuk']-$h['tot_keluar'];?>
                                <tr><td><?= date('d/m',strtotime($h['tgl'])) ?></td><td class="text-success"><?= rupiah($h['tot_masuk']) ?></td><td class="text-danger"><?= rupiah($h['tot_keluar']) ?></td><td class="fw-bold"><?= rupiah($sel) ?></td></tr>
                                <?php } else: echo "<tr><td colspan='4'>Kosong</td></tr>"; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card card-stat border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold py-3 text-danger">⚠️ Belum Lapor Bulan Ini</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php $ada=false; foreach($jabatan_wajib as $jb){ if(!in_array($jb, $sudah_lapor_arr)){ 
                            echo "<li class='list-group-item d-flex justify-content-between'><span class='text-uppercase fw-bold'>$jb</span><span class='badge bg-danger'>Pending</span></li>"; $ada=true; 
                        }} if(!$ada) echo "<div class='text-center text-success py-3'>Semua Sudah Lapor!</div>"; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleMenu() {
        document.getElementById('sidebar').classList.toggle('active');
    }
</script>
</body>
</html>