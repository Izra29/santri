<?php
session_start();
include 'koneksi.php';

// Cek Login Superadmin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin'){
    header("location:index.php");
    exit;
}

$bln_ini = date('m');
$thn_ini = date('Y');

// --- 1. LOGIK KEUANGAN (SAFE MODE: ?? 0 AGAR TIDAK ERROR) ---

// A. Uang Jajan
$q_in = mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE jenis='masuk'");
$in_jajan = mysqli_fetch_assoc($q_in)['t'] ?? 0;

$q_out = mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE jenis='keluar'");
$out_jajan = mysqli_fetch_assoc($q_out)['t'] ?? 0;

$saldo_jajan = $in_jajan - $out_jajan;

// B. Syahriah Bulan Ini
$q_spp = mysqli_query($conn, "SELECT SUM(nominal) as t FROM syahriah WHERE bulan='$bln_ini' AND tahun='$thn_ini'");
$syahriah_bln = mysqli_fetch_assoc($q_spp)['t'] ?? 0;

// C. Pengeluaran Bulan Ini
$q_keluar_bln = mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE jenis='keluar' AND MONTH(tanggal)='$bln_ini' AND YEAR(tanggal)='$thn_ini'");
$keluar_bln = mysqli_fetch_assoc($q_keluar_bln)['t'] ?? 0;

// D. Santri Aktif
$q_santri = mysqli_query($conn, "SELECT id FROM santri WHERE status='aktif'");
$jml_santri = mysqli_num_rows($q_santri);

// --- 2. LOGIK REKAP HARIAN ---
$q_harian = mysqli_query($conn, "SELECT DATE(tanggal) as tgl, 
    SUM(CASE WHEN jenis='masuk' THEN nominal ELSE 0 END) as tot_masuk,
    SUM(CASE WHEN jenis='keluar' THEN nominal ELSE 0 END) as tot_keluar
    FROM transaksi 
    GROUP BY DATE(tanggal) 
    ORDER BY tgl DESC LIMIT 7");

// --- 3. LOGIK EVALUASI ---
$jabatan_wajib = ['rois','sekretaris','kurikulum','ubudiah','kebersihan','peralatan','keamanan'];
$sudah_lapor_arr = [];
// Cek tabel laporan_bulanan jika ada (antisipasi jika tabel belum dibuat)
$cek_tabel = mysqli_query($conn, "SHOW TABLES LIKE 'laporan_bulanan'");
if(mysqli_num_rows($cek_tabel) > 0){
    $q_lapor = mysqli_query($conn, "SELECT * FROM laporan_bulanan WHERE bulan='$bln_ini' AND tahun='$thn_ini'");
    while($l = mysqli_fetch_assoc($q_lapor)){ $sudah_lapor_arr[] = $l['role']; }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Pimpinan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; --dark: #0a3622; }
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .sidebar { min-width: 260px; background: var(--main); color: white; min-height: 100vh; position: fixed; z-index:999; transition:0.3s; }
        .sidebar a { color: rgba(255,255,255,0.85); text-decoration: none; display: block; padding: 15px 20px; }
        .sidebar a:hover, .sidebar a.active { background: var(--dark); border-left: 5px solid var(--gold); color: white; }
        .main-content { margin-left: 260px; padding: 30px; transition:0.3s; }
        .card-stat { border: none; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        @media (max-width: 768px) { .sidebar { margin-left: -260px; } .sidebar.active { margin-left: 0; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="p-4 fw-bold border-bottom border-secondary d-flex justify-content-between">
        <span><i class="bi bi-buildings"></i> Pimpinan</span>
        <span class="d-md-none cursor-pointer" onclick="toggleNav()">X</span>
    </div>
    <a href="#" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
    <small class="text-white-50 ms-3 mt-3 d-block">MONITORING</small>
    <a href="admin.php"><i class="bi bi-wallet2 me-2"></i> Keuangan</a>
    <a href="admin_data.php"><i class="bi bi-database me-2"></i> Data Master</a>
    <a href="profil.php"><i class="bi bi-gear me-2"></i> Pengaturan</a>
    <a href="logout.php" class="text-danger bg-dark mt-3"><i class="bi bi-box-arrow-left me-2"></i> Logout</a>
</div>

<div class="main-content">
    <button class="btn btn-dark d-md-none mb-3" onclick="toggleNav()"><i class="bi bi-list"></i> Menu</button>
    <h4 class="fw-bold text-dark mb-4">Ringkasan Pondok</h4>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card card-stat bg-white p-3 h-100">
                <small class="text-muted">Total Saldo Santri</small>
                <h3 class="fw-bold text-primary mb-0"><?= rupiah($saldo_jajan) ?></h3>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-stat bg-white p-3 h-100">
                <small class="text-muted">Syahriah Masuk (Bulan Ini)</small>
                <h3 class="fw-bold text-success mb-0"><?= rupiah($syahriah_bln) ?></h3>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-stat bg-white p-3 h-100">
                <small class="text-muted">Santri Aktif</small>
                <h3 class="fw-bold text-dark mb-0"><?= $jml_santri ?></h3>
            </div>
        </div>
    </div>

    <div class="card card-stat border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Arus Kas Harian (7 Hari Terakhir)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0 text-center">
                    <thead class="table-light"><tr><th>Tanggal</th><th>Masuk</th><th>Keluar</th><th>Selisih</th></tr></thead>
                    <tbody>
                        <?php if(mysqli_num_rows($q_harian) > 0): ?>
                            <?php while($h = mysqli_fetch_array($q_harian)){ $sel=$h['tot_masuk']-$h['tot_keluar']; ?>
                            <tr>
                                <td><?= date('d M', strtotime($h['tgl'])) ?></td>
                                <td class="text-success"><?= rupiah($h['tot_masuk']) ?></td>
                                <td class="text-danger"><?= rupiah($h['tot_keluar']) ?></td>
                                <td class="fw-bold"><?= rupiah($sel) ?></td>
                            </tr>
                            <?php } ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-muted py-4">Belum ada transaksi minggu ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-stat border-0 shadow-sm">
        <div class="card-header bg-white fw-bold text-danger">⚠️ Laporan Sekbid Belum Masuk</div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <?php 
                $ada=false; 
                foreach($jabatan_wajib as $jb){ 
                    if(!in_array($jb, $sudah_lapor_arr)){ 
                        echo "<li class='list-group-item d-flex justify-content-between'><span class='text-uppercase'>$jb</span><span class='badge bg-danger'>Pending</span></li>"; 
                        $ada=true; 
                    } 
                } 
                if(!$ada) echo "<div class='text-center text-success py-2'>Semua Aman!</div>"; 
                ?>
            </ul>
        </div>
    </div>
</div>

<script>function toggleNav(){document.getElementById('sidebar').classList.toggle('active');}</script>
</body>
</html>