<?php
session_start();
include 'koneksi.php';

// Jika tombol login ditekan (Untuk Admin/Pengurus)
if (isset($_POST['login'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$user' AND password='$pass'");
    if (mysqli_num_rows($q) > 0) {
        $d = mysqli_fetch_assoc($q);
        $_SESSION['id'] = $d['id']; // PENTING: Simpan ID agar tidak error
        $_SESSION['role'] = $d['role'];
        $_SESSION['kobong_id'] = $d['kobong_id'];
        
        if ($d['role'] == 'admin') header("location:admin.php");
        else header("location:pengurus.php");
    } else {
        echo "<script>alert('Login Gagal');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cek Uang Saku Santri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark p-3">
    <div class="container">
        <span class="navbar-brand">Sistem Keuangan Pesantren</span>
        <button class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalLogin">Login Pengurus/Admin</button>
    </div>
</nav>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Cari Data Santri (Khusus Orang Tua)</h4>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-5 mb-2">
                        <select name="kobong_id" class="form-control" required>
                            <option value="">-- Pilih Kobong --</option>
                            <?php
                            $q_kobong = mysqli_query($conn, "SELECT * FROM kobong");
                            while($k = mysqli_fetch_array($q_kobong)){
                                $selected = (isset($_GET['kobong_id']) && $_GET['kobong_id'] == $k['id']) ? 'selected' : '';
                                echo "<option value='$k[id]' $selected>$k[nama_kobong]</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-5 mb-2">
                        <input type="text" name="nama" class="form-control" placeholder="Nama Santri" value="<?= isset($_GET['nama']) ? $_GET['nama'] : '' ?>" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100">Cari</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if(isset($_GET['kobong_id']) && isset($_GET['nama'])): 
        $kid = $_GET['kobong_id'];
        $nm = $_GET['nama'];
        $q_santri = mysqli_query($conn, "SELECT * FROM santri WHERE kobong_id='$kid' AND nama_santri LIKE '%$nm%'");
        $santri = mysqli_fetch_assoc($q_santri);
    ?>
    
    <?php if($santri): 
        // Hitung Saldo
        $sid = $santri['id'];
        $q_masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as tot FROM transaksi WHERE santri_id='$sid' AND jenis='masuk'"));
        $q_keluar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as tot FROM transaksi WHERE santri_id='$sid' AND jenis='keluar'"));
        $saldo = $q_masuk['tot'] - $q_keluar['tot'];
    ?>
        <div class="card mt-4">
            <div class="card-body">
                <h3><?= $santri['nama_santri'] ?> <small class="text-muted">(Kelas: <?= $santri['kelas'] ?>)</small></h3>
                <h2 class="text-primary">Sisa Saldo: <?= rupiah($saldo) ?></h2>
                <hr>
                <h5>Riwayat Transaksi</h5>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Masuk</th>
                            <th>Keluar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q_trans = mysqli_query($conn, "SELECT * FROM transaksi WHERE santri_id='$sid' ORDER BY tanggal DESC");
                        while($t = mysqli_fetch_array($q_trans)){
                            echo "<tr>
                                <td>$t[tanggal]</td>
                                <td>$t[keterangan]</td>
                                <td class='text-success'>".($t['jenis']=='masuk' ? rupiah($t['nominal']) : '-')."</td>
                                <td class='text-danger'>".($t['jenis']=='keluar' ? rupiah($t['nominal']) : '-')."</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger mt-3">Data santri tidak ditemukan! Pastikan Nama dan Kobong benar.</div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<div class="modal fade" id="modalLogin" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Login Petugas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="post">
            <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control"></div>
            <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control"></div>
            <button type="submit" name="login" class="btn btn-dark w-100">Masuk</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>