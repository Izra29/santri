<?php
session_start();
include 'koneksi.php';
if($_SESSION['role'] != 'pengurus') header("location:index.php");

$kobong_id = $_SESSION['kobong_id'];
$info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_kobong FROM kobong WHERE id='$kobong_id'"));

// 1. TAMBAH JAJAN
if(isset($_POST['jajan'])){
    $sid = $_POST['santri_id']; $nom = $_POST['nominal']; $ket = $_POST['keterangan']; $tgl = date('Y-m-d');
    $saldo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT (SELECT SUM(nominal) FROM transaksi WHERE santri_id='$sid' AND jenis='masuk') - (SELECT SUM(nominal) FROM transaksi WHERE santri_id='$sid' AND jenis='keluar') as s"))['s'];
    if($saldo < $nom) echo "<script>alert('Saldo Tidak Cukup!');</script>";
    else mysqli_query($conn, "INSERT INTO transaksi (tanggal,jenis,nominal,keterangan,santri_id,created_by) VALUES ('$tgl','keluar','$nom','$ket','$sid','$_SESSION[id]')");
    echo "<script>window.location='pengurus.php';</script>";
}

// 2. INPUT IZIN (LOGIKA BARU)
if(isset($_POST['input_izin'])){
    $sid = $_POST['santri_id']; $jns = $_POST['jenis']; $ket = $_POST['keterangan'];
    // Simpan ke tabel perizinan
    mysqli_query($conn, "INSERT INTO perizinan (santri_id, jenis, keterangan, diinput_oleh) VALUES ('$sid', '$jns', '$ket', 'Pengurus')");
    echo "<script>alert('Izin Berhasil Dicatat'); window.location='pengurus.php';</script>";
}

// 3. EDIT / HAPUS TRANSAKSI
if(isset($_POST['update_jajan'])){ mysqli_query($conn, "UPDATE transaksi SET nominal='$_POST[nominal]', keterangan='$_POST[keterangan]' WHERE id='$_POST[trans_id]'"); echo "<script>window.location='pengurus.php';</script>"; }
if(isset($_GET['hapus'])){ mysqli_query($conn, "DELETE FROM transaksi WHERE id='$_GET[hapus]'"); echo "<script>window.location='pengurus.php';</script>"; }

$bulan = $_GET['bulan'] ?? date('m'); 
$tahun = $_GET['tahun'] ?? date('Y');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pengurus - <?= $info['nama_kobong'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; }
        body { background-color: #f8f9fa; padding-top: 70px; }
        .navbar-custom { background: linear-gradient(to right, var(--main), #146c43); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .card-santri { transition: 0.3s; border-left: 4px solid var(--gold); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
    <div class="container">
        <span class="navbar-brand fw-bold"><i class="bi bi-house-door-fill text-warning"></i> <?= $info['nama_kobong'] ?></span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-2">
                <li class="nav-item"><a href="portal.php" class="btn btn-outline-warning btn-sm w-100 fw-bold">Ganti Jabatan</a></li>
                <li class="nav-item"><a href="profil.php" class="btn btn-outline-light btn-sm w-100">Profil</a></li>
                <li class="nav-item"><a href="logout.php" class="btn btn-danger btn-sm w-100">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-3 pb-5">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="text-success fw-bold m-0"><i class="bi bi-people"></i> Daftar Santri</h5>
        <form method="get" class="d-flex gap-1">
            <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()"><?php for($i=1;$i<=12;$i++){ $b=str_pad($i,2,'0',STR_PAD_LEFT); echo "<option value='$b' ".($bulan==$b?'selected':'').">$b</option>"; } ?></select>
            <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()"><?php for($i=2024;$i<=2030;$i++){ echo "<option value='$i' ".($tahun==$i?'selected':'').">$i</option>"; } ?></select>
        </form>
    </div>

    <div class="row g-3">
        <?php
        $q = mysqli_query($conn, "SELECT * FROM santri WHERE kobong_id='$kobong_id' ORDER BY nama_santri ASC");
        
        // LOOPING DIMULAI DI SINI
        while($s = mysqli_fetch_array($q)){
            $sid = $s['id'];
            
            // Hitung Saldo & Cek Status Izin
            $saldo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT (SELECT SUM(nominal) FROM transaksi WHERE santri_id='$sid' AND jenis='masuk') - (SELECT SUM(nominal) FROM transaksi WHERE santri_id='$sid' AND jenis='keluar') as s"))['s'];
            $out_bln = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE santri_id='$sid' AND jenis='keluar' AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun'"))['t'];
            
            // Cek apakah sedang izin?
            $cek_izin = mysqli_query($conn, "SELECT jenis FROM perizinan WHERE santri_id='$sid' AND status='keluar'");
            $is_izin = mysqli_num_rows($cek_izin) > 0;
            $data_izin = mysqli_fetch_assoc($cek_izin);
        ?>
        
        <div class="col-12 col-md-6 mb-2">
            <div class="card card-santri h-100 shadow-sm <?= $is_izin ? 'bg-secondary bg-opacity-10' : '' ?>">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between">
                        <h6 class="fw-bold mb-1 text-capitalize">
                            <?= $s['nama_santri'] ?> 
                            <?php if($is_izin): ?> <span class="badge bg-danger ms-1"><?= strtoupper($data_izin['jenis']) ?></span> <?php endif; ?>
                        </h6>
                        <span class="badge bg-success">Saldo: <?= rupiah($saldo??0) ?></span>
                    </div>
                    
                    <?php if(!$is_izin): ?>
                        <small class="text-muted d-block mb-2">Jajan Bulan Ini: <span class="text-danger fw-bold"><?= rupiah($out_bln??0) ?></span></small>
                        <form method="post" class="d-flex gap-1 mb-2">
                            <input type="hidden" name="santri_id" value="<?= $sid ?>">
                            <input type="number" name="nominal" class="form-control form-control-sm" placeholder="Rp.." required>
                            <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Ket.." required>
                            <button type="submit" name="jajan" class="btn btn-warning btn-sm text-white"><i class="bi bi-send"></i></button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning py-1 px-2 mt-2 small mb-2"><i class="bi bi-exclamation-circle"></i> Sedang di luar pondok.</div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-sm w-50 border text-secondary" data-bs-toggle="modal" data-bs-target="#modalRiwayat<?= $sid ?>">Riwayat Uang</button>
                        <?php if(!$is_izin): ?>
                            <button class="btn btn-outline-danger btn-sm w-50" data-bs-toggle="modal" data-bs-target="#modalIzin<?= $sid ?>">Input Izin</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalRiwayat<?= $sid ?>" tabindex="-1"><div class="modal-dialog modal-fullscreen-sm-down"><div class="modal-content"><div class="modal-header bg-light"><h6 class="modal-title"><?= $s['nama_santri'] ?></h6><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body p-0"><div class="table-responsive"><table class="table table-sm table-striped mb-0"><?php $qr=mysqli_query($conn,"SELECT * FROM transaksi WHERE santri_id='$sid' AND jenis='keluar' AND MONTH(tanggal)='$bulan' ORDER BY tanggal DESC"); while($r=mysqli_fetch_array($qr)){ ?><tr><form method="post"><input type="hidden" name="trans_id" value="<?= $r['id'] ?>"><td width="35%"><input type="text" name="keterangan" class="form-control form-control-sm" value="<?= $r['keterangan'] ?>"></td><td width="35%"><input type="number" name="nominal" class="form-control form-control-sm" value="<?= $r['nominal'] ?>"></td><td class="text-end"><button type="submit" name="update_jajan" class="btn btn-primary btn-sm py-0"><i class="bi bi-check"></i></button> <a href="pengurus.php?hapus=<?= $r['id'] ?>" class="btn btn-danger btn-sm py-0" onclick="return confirm('Hapus?')"><i class="bi bi-trash"></i></a></td></form></tr><?php } ?></table></div></div></div></div></div>

        <div class="modal fade" id="modalIzin<?= $sid ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white"><h6 class="modal-title">Input Izin: <?= $s['nama_santri'] ?></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <form method="post">
                            <input type="hidden" name="santri_id" value="<?= $sid ?>">
                            <div class="mb-2">
                                <label>Jenis Izin</label>
                                <select name="jenis" class="form-select">
                                    <option value="pulang">Pulang (Lama)</option>
                                    <option value="izin">Izin (Sebentar)</option>
                                    <option value="sakit">Sakit</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2" required placeholder="Alasan..."></textarea>
                            </div>
                            <button type="submit" name="input_izin" class="btn btn-danger w-100">Simpan Data</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php } // AKHIR LOOPING ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>