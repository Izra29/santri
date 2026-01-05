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
}

// 2. EDIT / HAPUS OLEH PENGURUS
if(isset($_POST['update_jajan'])){
    mysqli_query($conn, "UPDATE transaksi SET nominal='$_POST[nominal]', keterangan='$_POST[keterangan]' WHERE id='$_POST[trans_id]'");
    echo "<script>alert('Data Diupdate'); window.location='pengurus.php';</script>";
}
if(isset($_GET['hapus'])){
    mysqli_query($conn, "DELETE FROM transaksi WHERE id='$_GET[hapus]'");
    echo "<script>alert('Dihapus'); window.location='pengurus.php';</script>";
}

$bulan = $_GET['bulan'] ?? date('m'); 
$tahun = $_GET['tahun'] ?? date('Y');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pengurus Kobong</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; }
        body { background-color: #f8f9fa; }
        .bg-islamic { background: linear-gradient(135deg, var(--main), #198754); color: white; border-bottom: 5px solid var(--gold); }
        .card-santri { transition: 0.3s; border-left: 4px solid var(--main); }
        .card-santri:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="bg-islamic p-4 mb-4 text-center">
    <h3 class="fw-bold"><i class="bi bi-shop"></i> Kobong <?= $info['nama_kobong'] ?></h3>
    <div class="mt-2">
        <a href="profil.php" class="btn btn-warning btn-sm fw-bold">Profil</a>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</div>

<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-success fw-bold">Daftar Santri</h5>
        <form method="get" class="d-flex gap-2">
            <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php for($i=1;$i<=12;$i++){ $b=str_pad($i,2,'0',STR_PAD_LEFT); echo "<option value='$b' ".($bulan==$b?'selected':'').">$b</option>"; } ?>
            </select>
            <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php for($i=2024;$i<=2030;$i++){ echo "<option value='$i' ".($tahun==$i?'selected':'').">$i</option>"; } ?>
            </select>
        </form>
    </div>

    <div class="row g-3"> <?php
        $q = mysqli_query($conn, "SELECT * FROM santri WHERE kobong_id='$kobong_id' ORDER BY nama_santri ASC");
        while($s = mysqli_fetch_array($q)){
            $sid = $s['id'];
            $saldo = mysqli_fetch_assoc(mysqli_query($conn, "SELECT (SELECT SUM(nominal) FROM transaksi WHERE santri_id='$sid' AND jenis='masuk') - (SELECT SUM(nominal) FROM transaksi WHERE santri_id='$sid' AND jenis='keluar') as s"))['s'];
            $out_bln = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE santri_id='$sid' AND jenis='keluar' AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun'"))['t'];
        ?>
        <div class="col-12 col-md-6 mb-3"> 
            <div class="card card-santri h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h5 class="fw-bold mb-1 text-capitalize"><?= $s['nama_santri'] ?></h5>
                        <span class="badge bg-success" style="font-size: 0.9rem;">Saldo: <?= rupiah($saldo??0) ?></span>
                    </div>
                    <small class="text-muted d-block mb-2">Jajan Bulan Ini: <span class="text-danger fw-bold"><?= rupiah($out_bln??0) ?></span></small>
                    
                    <form method="post" class="d-flex gap-1 mb-2">
                        <input type="hidden" name="santri_id" value="<?= $sid ?>">
                        <input type="number" name="nominal" class="form-control form-control-sm" placeholder="Rp.." required>
                        <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Ket.." required>
                        <button type="submit" name="jajan" class="btn btn-warning btn-sm text-white"><i class="bi bi-send"></i></button>
                    </form>

                    <button class="btn btn-light btn-sm w-100 border text-secondary" data-bs-toggle="modal" data-bs-target="#modalRiwayat<?= $sid ?>">Lihat / Edit Riwayat</button>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalRiwayat<?= $sid ?>" tabindex="-1">
            <div class="modal-dialog modal-fullscreen-sm-down"> <div class="modal-content">
                    <div class="modal-header bg-light"><h6 class="modal-title text-capitalize"><?= $s['nama_santri'] ?></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <?php 
                                $qr = mysqli_query($conn, "SELECT * FROM transaksi WHERE santri_id='$sid' AND jenis='keluar' AND MONTH(tanggal)='$bulan' ORDER BY tanggal DESC");
                                while($r=mysqli_fetch_array($qr)){ ?>
                                <tr>
                                    <form method="post">
                                        <input type="hidden" name="trans_id" value="<?= $r['id'] ?>">
                                        <td width="35%"><input type="text" name="keterangan" class="form-control form-control-sm" value="<?= $r['keterangan'] ?>"></td>
                                        <td width="35%"><input type="number" name="nominal" class="form-control form-control-sm" value="<?= $r['nominal'] ?>"></td>
                                        <td class="text-end">
                                            <button type="submit" name="update_jajan" class="btn btn-primary btn-sm py-0"><i class="bi bi-check"></i></button>
                                            <a href="pengurus.php?hapus=<?= $r['id'] ?>" class="btn btn-danger btn-sm py-0" onclick="return confirm('Hapus?')"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </form>
                                </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>