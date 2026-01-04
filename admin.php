<?php
session_start();
include 'koneksi.php';
if($_SESSION['role'] != 'admin') header("location:index.php");

$page = $_GET['page'] ?? 'jajan'; // Default halaman Uang Jajan
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// --- LOGIC PENYIMPANAN DATA ---

// 1. INPUT UANG JAJAN (Masuk/Keluar Manual)
if(isset($_POST['simpan_jajan'])){
    mysqli_query($conn, "INSERT INTO transaksi (tanggal,jenis,nominal,keterangan,santri_id,created_by) VALUES ('".date('Y-m-d')."','$_POST[jenis]','$_POST[nominal]','$_POST[keterangan]','$_POST[santri_id]','$_SESSION[id]')");
    echo "<script>alert('Data Uang Jajan Disimpan'); window.location='admin.php?page=jajan';</script>";
}

// 2. INPUT SYAHRIAH (Ceklis Manual)
if(isset($_POST['bayar_syahriah'])){
    $cek = mysqli_query($conn, "SELECT * FROM syahriah WHERE santri_id='$_POST[santri_id]' AND bulan='$_POST[bulan]' AND tahun='$_POST[tahun]'");
    if(mysqli_num_rows($cek) == 0){
        mysqli_query($conn, "INSERT INTO syahriah (santri_id,bulan,tahun,nominal,tanggal_bayar) VALUES ('$_POST[santri_id]','$_POST[bulan]','$_POST[tahun]','$_POST[nominal]','".date('Y-m-d')."')");
    }
    echo "<script>alert('Syahriah Berhasil Diinput'); window.location='admin.php?page=syahriah&bulan=$_POST[bulan]&tahun=$_POST[tahun]';</script>";
}

// 3. PROSES TRANSFER (OTOMATIS RELASI)
if(isset($_POST['simpan_transfer'])){
    $santri_id = $_POST['santri_id'];
    $nominal = $_POST['nominal'];
    $ket_transfer = $_POST['keterangan_transfer']; // 'jajan' atau 'syahriah'
    $catatan = $_POST['catatan'];
    $tgl = date('Y-m-d');

    // A. Simpan ke tabel TRANSFER (Log induk)
    mysqli_query($conn, "INSERT INTO transfer (tanggal,santri_id,nominal,keterangan_transfer,catatan_tambahan,created_by) VALUES ('$tgl','$santri_id','$nominal','$ket_transfer','$catatan','$_SESSION[id]')");

    // B. LOGIKA OTOMATIS
    if($ket_transfer == 'jajan'){
        // Masukkan ke tabel TRANSAKSI (Menambah Saldo)
        $ket_transaksi = "Transfer Masuk: " . $catatan;
        mysqli_query($conn, "INSERT INTO transaksi (tanggal,jenis,nominal,keterangan,santri_id,created_by) VALUES ('$tgl','masuk','$nominal','$ket_transaksi','$santri_id','$_SESSION[id]')");
        $msg = "Transfer dicatat & Saldo Santri bertambah!";
    } 
    else if($ket_transfer == 'syahriah'){
        // Masukkan ke tabel SYAHRIAH (Otomatis Ceklis Bulan Ini)
        $bln_ini = date('m'); $thn_ini = date('Y');
        // Cek dulu biar ga dobel bayar bulan ini
        $cek = mysqli_query($conn, "SELECT * FROM syahriah WHERE santri_id='$santri_id' AND bulan='$bln_ini' AND tahun='$thn_ini'");
        if(mysqli_num_rows($cek) == 0){
            mysqli_query($conn, "INSERT INTO syahriah (santri_id,bulan,tahun,nominal,tanggal_bayar) VALUES ('$santri_id','$bln_ini','$thn_ini','$nominal','$tgl')");
            $msg = "Transfer dicatat & Syahriah bulan ini LUNAS!";
        } else {
            $msg = "Transfer dicatat, tapi Syahriah bulan ini sudah lunas sebelumnya.";
        }
    }
    echo "<script>alert('$msg'); window.location='admin.php?page=transfer';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Keuangan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; }
        body { background-color: #f4f6f9; display: flex; min-height: 100vh; flex-direction: column; }
        .sidebar { min-width: 250px; background: linear-gradient(180deg, var(--main), #146c43); color: white; min-height: 100vh; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 12px 20px; display: block; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(0,0,0,0.2); color: #fff; font-weight: bold; border-left: 4px solid var(--gold); }
        .content { flex: 1; padding: 20px; }
        .card-menu { border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar">
        <div class="p-3 text-center border-bottom border-secondary">
            <h4 class="fw-bold mb-0"><i class="bi bi-mosque"></i> Admin</h4>
            <small>Keuangan Pesantren</small>
        </div>
        <a href="?page=jajan" class="<?= $page=='jajan'?'active':'' ?>"><i class="bi bi-wallet2"></i> Uang Jajan</a>
        <a href="?page=syahriah" class="<?= $page=='syahriah'?'active':'' ?>"><i class="bi bi-calendar-check"></i> Laporan Syahriah</a>
        <a href="?page=transfer" class="<?= $page=='transfer'?'active':'' ?>"><i class="bi bi-bank"></i> Laporan Transfer</a>
        <div class="mt-4">
            <a href="admin_data.php" class="bg-dark text-warning"><i class="bi bi-database"></i> Kelola Data Master</a>
            <a href="logout.php" class="bg-danger mt-2"><i class="bi bi-box-arrow-left"></i> Logout</a>
        </div>
    </div>

    <div class="content">
        
        <?php if($page == 'jajan'): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="bi bi-wallet2 text-success"></i> Saldo & Uang Jajan</h3>
                <button class="btn btn-warning text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#modalJajan"><i class="bi bi-plus-lg"></i> Input Manual</button>
            </div>
            
            <div class="card card-menu p-3">
                <table class="table table-hover">
                    <thead class="table-light"><tr><th>Santri</th><th>Kobong</th><th>Total Masuk</th><th>Total Keluar</th><th>Saldo Akhir</th></tr></thead>
                    <tbody>
                        <?php 
                        $q = mysqli_query($conn, "SELECT s.*, k.nama_kobong FROM santri s LEFT JOIN kobong k ON s.kobong_id=k.id WHERE s.status='aktif'");
                        while($r=mysqli_fetch_array($q)){
                            $in = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE santri_id='$r[id]' AND jenis='masuk'"))['t'];
                            $out = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE santri_id='$r[id]' AND jenis='keluar'"))['t'];
                        ?>
                        <tr>
                            <td><?= $r['nama_santri'] ?></td>
                            <td><?= $r['nama_kobong'] ?></td>
                            <td class="text-success"><?= rupiah($in??0) ?></td>
                            <td class="text-danger"><?= rupiah($out??0) ?></td>
                            <td class="fw-bold text-primary"><?= rupiah(($in??0)-($out??0)) ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="modal fade" id="modalJajan"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Input Jajan/Pemasukan Manual</h5></div><div class="modal-body">
                <form method="post">
                    <select name="santri_id" class="form-select mb-2" required><?php $qs=mysqli_query($conn,"SELECT * FROM santri WHERE status='aktif' ORDER BY nama_santri"); while($s=mysqli_fetch_array($qs)){echo "<option value='$s[id]'>$s[nama_santri]</option>";} ?></select>
                    <select name="jenis" class="form-select mb-2"><option value="masuk">Pemasukan (Topup)</option><option value="keluar">Pengeluaran (Jajan)</option></select>
                    <input type="number" name="nominal" class="form-control mb-2" placeholder="Nominal" required>
                    <input type="text" name="keterangan" class="form-control mb-2" placeholder="Keterangan" required>
                    <button type="submit" name="simpan_jajan" class="btn btn-success w-100">Simpan</button>
                </form>
            </div></div></div></div>

        <?php elseif($page == 'syahriah'): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="bi bi-calendar-check text-success"></i> Laporan Syahriah</h3>
                <form method="get" class="d-flex gap-2">
                    <input type="hidden" name="page" value="syahriah">
                    <select name="bulan" class="form-select" onchange="this.form.submit()">
                        <?php for($i=1;$i<=12;$i++){ $b=str_pad($i,2,'0',STR_PAD_LEFT); echo "<option value='$b' ".($bulan==$b?'selected':'').">Bulan $b</option>"; } ?>
                    </select>
                    <select name="tahun" class="form-select" onchange="this.form.submit()">
                        <?php for($i=2024;$i<=2030;$i++){ echo "<option value='$i' ".($tahun==$i?'selected':'').">$i</option>"; } ?>
                    </select>
                </form>
            </div>

            <div class="card card-menu p-3">
                <table class="table table-bordered align-middle">
                    <thead class="table-success"><tr><th>Nama Santri</th><th class="text-center">Status Pembayaran (<?= "$bulan/$tahun" ?>)</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($conn, "SELECT s.*, k.nama_kobong FROM santri s LEFT JOIN kobong k ON s.kobong_id=k.id WHERE s.status='aktif'");
                        while($r=mysqli_fetch_array($q)){
                            // Cek apakah sudah bayar bulan ini
                            $bayar = mysqli_query($conn, "SELECT * FROM syahriah WHERE santri_id='$r[id]' AND bulan='$bulan' AND tahun='$tahun'");
                            $lunas = mysqli_num_rows($bayar) > 0;
                            $data_bayar = mysqli_fetch_assoc($bayar);
                        ?>
                        <tr>
                            <td>
                                <span class="fw-bold"><?= $r['nama_santri'] ?></span><br>
                                <small class="text-muted"><?= $r['nama_kobong'] ?></small>
                            </td>
                            <td class="text-center">
                                <?php if($lunas): ?>
                                    <span class="badge bg-success fs-6"><i class="bi bi-check-circle-fill"></i> LUNAS</span><br>
                                    <small class="text-muted">Tgl: <?= $data_bayar['tanggal_bayar'] ?></small>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Belum Bayar</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if(!$lunas): ?>
                                    <form method="post">
                                        <input type="hidden" name="santri_id" value="<?= $r['id'] ?>">
                                        <input type="hidden" name="bulan" value="<?= $bulan ?>">
                                        <input type="hidden" name="tahun" value="<?= $tahun ?>">
                                        <input type="hidden" name="nominal" value="100000"> <button type="submit" name="bayar_syahriah" class="btn btn-outline-success btn-sm">Ceklis Bayar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        <?php elseif($page == 'transfer'): ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="card card-menu bg-white p-3 mb-3">
                        <h5 class="text-primary mb-3"><i class="bi bi-cloud-arrow-down-fill"></i> Input Transfer Baru</h5>
                        <form method="post">
                            <div class="mb-2">
                                <label>Pilih Santri</label>
                                <select name="santri_id" class="form-select select2" required>
                                    <option value="">-- Cari Nama --</option>
                                    <?php $qs=mysqli_query($conn,"SELECT * FROM santri WHERE status='aktif' ORDER BY nama_santri"); while($s=mysqli_fetch_array($qs)){echo "<option value='$s[id]'>$s[nama_santri]</option>";} ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label>Nominal Transfer</label>
                                <input type="number" name="nominal" class="form-control" placeholder="Rp..." required>
                            </div>
                            <div class="mb-2">
                                <label class="fw-bold text-danger">Peruntukan Dana (Otomatis)</label>
                                <select name="keterangan_transfer" class="form-select" required>
                                    <option value="jajan">Uang Jajan (Masuk ke Saldo)</option>
                                    <option value="syahriah">Bayar Syahriah (Ceklis Bulan Ini)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Catatan</label>
                                <input type="text" name="catatan" class="form-control" placeholder="Cth: Transfer BNI dari Ayah">
                            </div>
                            <button type="submit" name="simpan_transfer" class="btn btn-primary w-100">Simpan & Proses</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card card-menu p-3">
                        <h5>Riwayat Transfer Masuk</h5>
                        <table class="table table-striped table-sm">
                            <thead><tr><th>Tanggal</th><th>Santri</th><th>Nominal</th><th>Peruntukan</th><th>Catatan</th></tr></thead>
                            <tbody>
                                <?php 
                                $qt = mysqli_query($conn, "SELECT t.*, s.nama_santri FROM transfer t JOIN santri s ON t.santri_id=s.id ORDER BY t.id DESC LIMIT 20");
                                while($t=mysqli_fetch_array($qt)){
                                    $badge = $t['keterangan_transfer']=='syahriah' ? 'bg-success' : 'bg-info';
                                ?>
                                <tr>
                                    <td><?= $t['tanggal'] ?></td>
                                    <td><?= $t['nama_santri'] ?></td>
                                    <td class="fw-bold"><?= rupiah($t['nominal']) ?></td>
                                    <td><span class="badge <?= $badge ?>"><?= strtoupper($t['keterangan_transfer']) ?></span></td>
                                    <td><small><?= $t['catatan_tambahan'] ?></small></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>