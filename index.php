<?php
session_start();
include 'koneksi.php';

// Logic Login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($q) > 0) {
        $d = mysqli_fetch_assoc($q);
        $_SESSION['id'] = $d['id']; $_SESSION['username'] = $username; $_SESSION['role'] = $d['role']; $_SESSION['kobong_id'] = $d['kobong_id'];
        header("location:".($d['role']=='admin'?'admin.php':'pengurus.php'));
    } else { echo "<script>alert('Login Gagal');</script>"; }
}

// Logic Tahun Ajaran (Juli Start)
$bln_skg = date('m');
$thn_skg = date('Y');
// Jika bulan 1-6 (Jan-Jun), maka tahun ajarannya adalah (TahunLalu)/(TahunIni)
// Jika bulan 7-12 (Jul-Des), maka tahun ajarannya adalah (TahunIni)/(TahunDepan)
$default_tahun = ($bln_skg < 7) ? $thn_skg : $thn_skg; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Sistem Informasi Pesantren</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; --bg: #f3f4f6; }
        body { background-color: var(--bg); font-family: 'Segoe UI', sans-serif; }
        .hero { background: linear-gradient(135deg, var(--main), #198754); color: white; padding: 60px 0 40px; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(15, 81, 50, 0.2); }
        .card-custom { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .card-custom:hover { transform: translateY(-5px); }
        .btn-gold { background-color: var(--gold); color: white; font-weight: bold; border-radius: 20px; padding: 8px 25px; }
        .icon-box { font-size: 2rem; color: var(--main); margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="hero text-center">
    <div class="container">
        <i class="bi bi-moon-stars-fill" style="font-size: 3rem; color: var(--gold);"></i>
        <h1 class="fw-bold mt-2">Sistem Keuangan Santri</h1>
        <p class="opacity-75">Transparansi & Kemudahan untuk Walisantri</p>
        <button class="btn btn-outline-light btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#modalLogin">
            <i class="bi bi-shield-lock"></i> Login Pengurus
        </button>
    </div>
</div>

<div class="container" style="margin-top: -60px;">
    <div class="card card-custom bg-white p-4 mb-4">
        <h5 class="text-center text-success mb-3"><i class="bi bi-search"></i> Cari Data Santri</h5>
        <form method="GET">
            <div class="row g-2 justify-content-center">
                <div class="col-md-4">
                    <select name="kobong_id" class="form-select border-success" required>
                        <option value="">-- Pilih Kobong --</option>
                        <?php
                        $qk = mysqli_query($conn, "SELECT * FROM kobong");
                        while($k=mysqli_fetch_array($qk)){ echo "<option value='$k[id]' ".(isset($_GET['kobong_id'])&&$_GET['kobong_id']==$k['id']?'selected':'').">$k[nama_kobong]</option>"; }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="nama" class="form-control border-success" placeholder="Nama Santri..." value="<?= $_GET['nama']??'' ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-gold w-100"><i class="bi bi-search"></i> Cari</button>
                </div>
            </div>
        </form>
    </div>

    <?php if(isset($_GET['kobong_id']) && isset($_GET['nama'])): 
        $kid = $_GET['kobong_id']; $nm = $_GET['nama'];
        $santri = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM santri WHERE kobong_id='$kid' AND nama_santri LIKE '%$nm%'"));
    ?>
    
    <?php if($santri): 
        $sid = $santri['id'];
        // Hitung Saldo Total
        $in = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE santri_id='$sid' AND jenis='masuk'"));
        $out = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as t FROM transaksi WHERE santri_id='$sid' AND jenis='keluar'"));
        $saldo = ($in['t']??0) - ($out['t']??0);
        
        // Filter Tanggal
        $bulan = $_GET['bulan'] ?? date('m');
        $tahun = $_GET['tahun'] ?? date('Y');
    ?>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card card-custom bg-success text-white p-4 h-100 text-center">
                    <i class="bi bi-person-circle" style="font-size: 4rem;"></i>
                    <h3 class="fw-bold mt-2"><?= $santri['nama_santri'] ?></h3>
                    <p class="mb-0">Kelas: <?= $santri['kelas'] ?></p>
                    <hr class="border-white">
                    <small>Sisa Saldo Saat Ini</small>
                    <h1 class="fw-bold text-warning"><?= rupiah($saldo) ?></h1>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-custom p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-success"><i class="bi bi-clock-history"></i> Riwayat Transaksi</h5>
                        <form method="get" class="d-flex gap-1">
                            <input type="hidden" name="kobong_id" value="<?= $kid ?>">
                            <input type="hidden" name="nama" value="<?= $nm ?>">
                            <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                                <?php for($i=1;$i<=12;$i++){ $b=str_pad($i,2,'0',STR_PAD_LEFT); echo "<option value='$b' ".($bulan==$b?'selected':'').">$b</option>"; } ?>
                            </select>
                            <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
                                <?php for($i=2024;$i<=2030;$i++){ echo "<option value='$i' ".($tahun==$i?'selected':'').">$i</option>"; } ?>
                            </select>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light"><tr><th>Tgl</th><th>Ket</th><th>Masuk</th><th>Keluar</th></tr></thead>
                            <tbody>
                                <?php
                                $qt = mysqli_query($conn, "SELECT * FROM transaksi WHERE santri_id='$sid' AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun' ORDER BY tanggal DESC");
                                if(mysqli_num_rows($qt)==0) echo "<tr><td colspan='4' class='text-center text-muted py-4'><i class='bi bi-inbox'></i> Belum ada data bulan ini</td></tr>";
                                while($t=mysqli_fetch_array($qt)){
                                    echo "<tr>
                                        <td>".date('d/m', strtotime($t['tanggal']))."</td>
                                        <td>$t[keterangan]</td>
                                        <td class='text-success fw-bold'>".($t['jenis']=='masuk'?rupiah($t['nominal']):'-')."</td>
                                        <td class='text-danger fw-bold'>".($t['jenis']=='keluar'?rupiah($t['nominal']):'-')."</td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger text-center"><i class="bi bi-exclamation-circle"></i> Data Santri Tidak Ditemukan.</div>
    <?php endif; endif; ?>
</div>

<div class="modal fade" id="modalLogin" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-success text-white"><h5 class="modal-title">Login Staff</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center">
                <i class="bi bi-shield-lock-fill text-success" style="font-size: 3rem;"></i>
                <form method="post" class="mt-3">
                    <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
                    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
                    <button type="submit" name="login" class="btn btn-success w-100">Masuk</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>