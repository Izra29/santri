<?php
session_start();
include 'koneksi.php';
// Cek Login: Admin & Superadmin Boleh Masuk
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin') header("location:index.php");

$page = $_GET['page'] ?? 'jajan';
$bulan = $_GET['bulan'] ?? date('m'); $tahun = $_GET['tahun'] ?? date('Y');

// LOGIC SIMPAN DATA (Sama seperti sebelumnya)
if(isset($_POST['simpan_jajan'])){ mysqli_query($conn, "INSERT INTO transaksi (tanggal,jenis,nominal,keterangan,santri_id,created_by) VALUES ('".date('Y-m-d')."','$_POST[jenis]','$_POST[nominal]','$_POST[keterangan]','$_POST[santri_id]','$_SESSION[id]')"); echo "<script>window.location='admin.php?page=jajan';</script>"; }
if(isset($_POST['bayar_syahriah'])){ if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM syahriah WHERE santri_id='$_POST[santri_id]' AND bulan='$_POST[bulan]' AND tahun='$_POST[tahun]'"))==0){ mysqli_query($conn, "INSERT INTO syahriah (santri_id,bulan,tahun,nominal,tanggal_bayar) VALUES ('$_POST[santri_id]','$_POST[bulan]','$_POST[tahun]','$_POST[nominal]','".date('Y-m-d')."')"); } echo "<script>window.location='admin.php?page=syahriah&bulan=$_POST[bulan]&tahun=$_POST[tahun]';</script>"; }
if(isset($_POST['simpan_transfer'])){ $sid=$_POST['santri_id']; $nom=$_POST['nominal']; $kt=$_POST['keterangan_transfer']; $cat=$_POST['catatan']; $tgl=date('Y-m-d'); mysqli_query($conn, "INSERT INTO transfer (tanggal,santri_id,nominal,keterangan_transfer,catatan_tambahan,created_by) VALUES ('$tgl','$sid','$nom','$kt','$cat','$_SESSION[id]')"); 
if($kt=='jajan'){ mysqli_query($conn, "INSERT INTO transaksi (tanggal,jenis,nominal,keterangan,santri_id,created_by) VALUES ('$tgl','masuk','$nom','Transfer: $cat','$sid','$_SESSION[id]')"); } 
else if($kt=='syahriah'){ $b=date('m');$t=date('Y'); if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM syahriah WHERE santri_id='$sid' AND bulan='$b' AND tahun='$t'"))==0){ mysqli_query($conn, "INSERT INTO syahriah (santri_id,bulan,tahun,nominal,tanggal_bayar) VALUES ('$sid','$b','$t','$nom','$tgl')"); }} echo "<script>window.location='admin.php?page=transfer';</script>"; }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Keuangan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; }
        body { background-color: #f4f6f9; overflow-x: hidden; }
        
        /* CSS Sidebar Responsif */
        .sidebar { min-width: 250px; background: linear-gradient(180deg, var(--main), #146c43); color: white; min-height: 100vh; position: fixed; z-index: 1000; transition: 0.3s; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 12px 20px; display: block; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(0,0,0,0.2); color: #fff; font-weight: bold; border-left: 4px solid var(--gold); }
        
        .content { margin-left: 250px; padding: 20px; transition: 0.3s; }
        
        @media (max-width: 768px) {
            .sidebar { margin-left: -250px; }
            .sidebar.active { margin-left: 0; }
            .content { margin-left: 0; padding: 15px; }
            .mobile-header { display: flex !important; }
        }
        .mobile-header { display: none; background: var(--main); color: white; padding: 10px 15px; border-radius: 8px; margin-bottom: 20px; justify-content: space-between; align-items: center; }
        .table-responsive { overflow-x: auto; }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="p-3 text-center border-bottom border-secondary d-flex justify-content-between align-items-center">
        <div><h4 class="fw-bold mb-0"><i class="bi bi-mosque"></i> Admin</h4><small>Keuangan Pesantren</small></div>
        <button class="btn btn-sm text-white d-md-none" onclick="toggleNav()"><i class="bi bi-x-lg"></i></button>
    </div>
    <a href="?page=jajan" class="<?= $page=='jajan'?'active':'' ?>"><i class="bi bi-wallet2"></i> Uang Jajan</a>
    <a href="?page=syahriah" class="<?= $page=='syahriah'?'active':'' ?>"><i class="bi bi-calendar-check"></i> Syahriah</a>
    <a href="?page=transfer" class="<?= $page=='transfer'?'active':'' ?>"><i class="bi bi-bank"></i> Transfer</a>
    <div class="mt-4">
        <a href="admin_data.php" class="bg-dark text-warning"><i class="bi bi-database"></i> Data Master</a>
        <a href="logout.php" class="bg-danger mt-2"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</div>

<div class="content">
    <div class="mobile-header shadow-sm" onclick="toggleNav()">
        <span class="fw-bold"><i class="bi bi-list fs-4 me-2"></i> Menu Keuangan</span>
    </div>

    <?php if($page == 'jajan'): ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-success">Uang Jajan</h4>
            <button class="btn btn-warning text-white btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalJajan"><i class="bi bi-plus-lg"></i> Input</button>
        </div>
        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table class="table table-hover" style="min-width: 600px;">
                    <thead class="table-light"><tr><th>Santri</th><th>Kobong</th><th>Masuk</th><th>Keluar</th><th>Saldo</th></tr></thead>
                    <tbody>
                        <?php $q=mysqli_query($conn, "SELECT s.*,k.nama_kobong FROM santri s LEFT JOIN kobong k ON s.kobong_id=k.id WHERE s.status='aktif'");
                        while($r=mysqli_fetch_array($q)){
                            $in=mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(nominal) as t FROM transaksi WHERE santri_id='$r[id]' AND jenis='masuk'"))['t'];
                            $out=mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(nominal) as t FROM transaksi WHERE santri_id='$r[id]' AND jenis='keluar'"))['t']; ?>
                        <tr><td><?= $r['nama_santri'] ?></td><td><?= $r['nama_kobong'] ?></td><td class="text-success"><?= rupiah($in??0) ?></td><td class="text-danger"><?= rupiah($out??0) ?></td><td class="fw-bold text-primary"><?= rupiah(($in??0)-($out??0)) ?></td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="modalJajan"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Input Manual</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">
            <form method="post">
                <select name="santri_id" class="form-select mb-2" required><?php $qs=mysqli_query($conn,"SELECT * FROM santri WHERE status='aktif' ORDER BY nama_santri"); while($s=mysqli_fetch_array($qs)){echo "<option value='$s[id]'>$s[nama_santri]</option>";} ?></select>
                <select name="jenis" class="form-select mb-2"><option value="masuk">Pemasukan</option><option value="keluar">Pengeluaran</option></select>
                <input type="number" name="nominal" class="form-control mb-2" placeholder="Nominal" required>
                <input type="text" name="keterangan" class="form-control mb-2" placeholder="Ket" required>
                <button type="submit" name="simpan_jajan" class="btn btn-success w-100">Simpan</button>
            </form>
        </div></div></div></div>

    <?php elseif($page == 'syahriah'): ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h4 class="fw-bold text-success">Laporan Syahriah</h4>
            <form method="get" class="d-flex gap-1 mt-2 mt-md-0"><input type="hidden" name="page" value="syahriah">
                <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()"><?php for($i=1;$i<=12;$i++){ $b=str_pad($i,2,'0',STR_PAD_LEFT); echo "<option value='$b' ".($bulan==$b?'selected':'').">$b</option>"; } ?></select>
                <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()"><?php for($i=2024;$i<=2030;$i++){ echo "<option value='$i' ".($tahun==$i?'selected':'').">$i</option>"; } ?></select>
            </form>
        </div>
        <div class="card border-0 shadow-sm p-3">
            <div class="table-responsive">
                <table class="table table-bordered align-middle" style="min-width: 500px;">
                    <thead class="table-success"><tr><th>Nama</th><th class="text-center">Status (<?= "$bulan/$tahun" ?>)</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php $q=mysqli_query($conn, "SELECT s.*,k.nama_kobong FROM santri s LEFT JOIN kobong k ON s.kobong_id=k.id WHERE s.status='aktif'");
                        while($r=mysqli_fetch_array($q)){
                            $cek=mysqli_num_rows(mysqli_query($conn,"SELECT * FROM syahriah WHERE santri_id='$r[id]' AND bulan='$bulan' AND tahun='$tahun'")); ?>
                        <tr><td><b><?= $r['nama_santri'] ?></b><br><small><?= $r['nama_kobong'] ?></small></td>
                        <td class="text-center"><?= $cek>0 ? "<span class='badge bg-success'>LUNAS</span>" : "<span class='badge bg-secondary'>Belum</span>" ?></td>
                        <td class="text-center"><?php if(!$cek): ?><form method="post"><input type="hidden" name="santri_id" value="<?= $r['id'] ?>"><input type="hidden" name="bulan" value="<?= $bulan ?>"><input type="hidden" name="tahun" value="<?= $tahun ?>"><input type="hidden" name="nominal" value="100000"><button type="submit" name="bayar_syahriah" class="btn btn-outline-success btn-sm">Bayar</button></form><?php endif; ?></td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif($page == 'transfer'): ?>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm p-3">
                    <h5 class="text-primary mb-3">Input Transfer</h5>
                    <form method="post">
                        <select name="santri_id" class="form-select mb-2" required><option value="">- Santri -</option><?php $qs=mysqli_query($conn,"SELECT * FROM santri WHERE status='aktif'"); while($s=mysqli_fetch_array($qs)){echo "<option value='$s[id]'>$s[nama_santri]</option>";} ?></select>
                        <input type="number" name="nominal" class="form-control mb-2" placeholder="Nominal" required>
                        <select name="keterangan_transfer" class="form-select mb-2" required><option value="jajan">Masuk Saldo Jajan</option><option value="syahriah">Bayar Syahriah</option></select>
                        <input type="text" name="catatan" class="form-control mb-3" placeholder="Catatan">
                        <button type="submit" name="simpan_transfer" class="btn btn-primary w-100">Proses</button>
                    </form>
                </div>
            </div>
            <div class="col-12 col-md-8">
                <div class="card border-0 shadow-sm p-3">
                    <h5>Riwayat Transfer</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm" style="min-width: 500px;">
                            <thead><tr><th>Tgl</th><th>Santri</th><th>Nominal</th><th>Untuk</th></tr></thead>
                            <tbody>
                                <?php $qt=mysqli_query($conn,"SELECT t.*,s.nama_santri FROM transfer t JOIN santri s ON t.santri_id=s.id ORDER BY t.id DESC LIMIT 10");
                                while($t=mysqli_fetch_array($qt)){ echo "<tr><td>$t[tanggal]</td><td>$t[nama_santri]</td><td class='fw-bold'>".rupiah($t['nominal'])."</td><td><span class='badge bg-info'>$t[keterangan_transfer]</span></td></tr>"; } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggleNav() { document.getElementById('sidebar').classList.toggle('active'); }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>