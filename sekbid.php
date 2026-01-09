<?php
session_start();
include 'koneksi.php';

// Cek Role yang diizinkan
$allowed = ['rois','sekretaris','kurikulum','ubudiah','kebersihan','peralatan','keamanan'];
if(!in_array($_SESSION['role'], $allowed)) header("location:index.php");

$role = $_SESSION['role'];
$user = $_SESSION['username'];

// --- LOGIC: PERIZINAN (Bisa diakses Sekretaris, Keamanan, Rois) ---
if(isset($_POST['input_izin'])){
    $sid = $_POST['santri_id'];
    $jenis = $_POST['jenis'];
    $ket = $_POST['keterangan'];
    // Simpan data izin
    mysqli_query($conn, "INSERT INTO perizinan (santri_id, jenis, keterangan, diinput_oleh) VALUES ('$sid', '$jenis', '$ket', '$user')");
    echo "<script>alert('Data Izin Berhasil Disimpan!'); window.location='sekbid.php?tab=izin';</script>";
}

if(isset($_GET['konfirmasi_kembali'])){
    $id_izin = $_GET['konfirmasi_kembali'];
    // Update status jadi kembali
    mysqli_query($conn, "UPDATE perizinan SET status='kembali', tgl_kembali=NOW() WHERE id='$id_izin'");
    echo "<script>alert('Santri Telah Kembali.'); window.location='sekbid.php?tab=izin';</script>";
}

// --- LOGIC: DATA SANTRI (KHUSUS SEKRETARIS) ---
if($role == 'sekretaris'){
    if(isset($_POST['add_santri'])){
        $nm=$_POST['nama']; $kl=$_POST['kelas']; $kb=$_POST['kobong_id'];
        if(mysqli_query($conn,"INSERT INTO santri (nama_santri,kelas,kobong_id,status) VALUES ('$nm','$kl','$kb','aktif')")){
            // Buat akun ortu otomatis
            $uid=mysqli_insert_id($conn); $usr=explode(" ",strtolower($nm))[0].rand(100,999);
            mysqli_query($conn,"INSERT INTO users (username,password,role,santri_id) VALUES ('$usr','12345','orangtua','$uid')");
            echo "<script>alert('Santri Ditambah!'); window.location='sekbid.php?tab=santri';</script>";
        }
    }
    // Update Data Santri (Kenaikan Kelas / Pindah Kobong)
    if(isset($_POST['update_santri'])){
        mysqli_query($conn, "UPDATE santri SET nama_santri='$_POST[nama]', kelas='$_POST[kelas]', kobong_id='$_POST[kobong_id]' WHERE id='$_POST[id]'");
        echo "<script>alert('Data Diupdate!'); window.location='sekbid.php?tab=santri';</script>";
    }
}

// --- LOGIC: NOTULEN (Tulis: Sekretaris, Baca: Semua) ---
if(isset($_POST['simpan_notulen'])){
    $judul = $_POST['judul']; $isi = $_POST['isi']; $tgl = date('Y-m-d');
    mysqli_query($conn, "INSERT INTO notulen (judul, isi, tanggal, pembuat) VALUES ('$judul', '$isi', '$tgl', '$user')");
    echo "<script>alert('Notulen Disebar!'); window.location='sekbid.php?tab=notulen';</script>";
}

// --- HITUNG JATAH MAKAN (Realtime) ---
$total_santri = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM santri WHERE status='aktif'"));
$sedang_izin = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM perizinan WHERE status='keluar'"));
$jatah_makan = $total_santri - $sedang_izin;

$qk = mysqli_query($conn, "SELECT * FROM kobong"); 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard <?= strtoupper($role) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; }
        body { background-color: #f8f9fa; padding-top: 80px; }
        .navbar-custom { background: linear-gradient(to right, var(--main), #146c43); }
        .card-stat { border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top shadow-sm">
    <div class="container">
        <span class="navbar-brand fw-bold"><i class="bi bi-person-workspace"></i> BAG. <?= strtoupper($role) ?></span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navB">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navB">
            <ul class="navbar-nav ms-auto gap-2">
                <li class="nav-item"><a href="?tab=dashboard" class="nav-link text-white">Dashboard</a></li>
                <li class="nav-item"><a href="?tab=izin" class="nav-link text-white">Perizinan</a></li>
                <li class="nav-item"><a href="?tab=notulen" class="nav-link text-white">Notulen</a></li>
                <?php if($role=='sekretaris'): ?>
                <li class="nav-item"><a href="?tab=santri" class="nav-link text-warning fw-bold">Data Santri</a></li>
                <?php endif; ?>
                <li class="nav-item"><a href="portal.php" class="btn btn-outline-warning btn-sm">Ganti Jabatan</a></li>
                <li class="nav-item"><a href="logout.php" class="btn btn-danger btn-sm">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">

    <?php if(!isset($_GET['tab']) || $_GET['tab']=='dashboard'): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <div class="alert alert-success">
                <h4><i class="bi bi-basket2-fill"></i> Laporan Jatah Makan Hari Ini</h4>
                <p class="mb-0">Data otomatis terupdate berdasarkan santri yang izin/pulang.</p>
            </div>
        </div>
        <div class="col-4">
            <div class="card card-stat bg-primary text-white p-3 text-center">
                <h1><?= $total_santri ?></h1>
                <small>Total Santri</small>
            </div>
        </div>
        <div class="col-4">
            <div class="card card-stat bg-danger text-white p-3 text-center">
                <h1><?= $sedang_izin ?></h1>
                <small>Sedang Izin/Pulang</small>
            </div>
        </div>
        <div class="col-4">
            <div class="card card-stat bg-success text-white p-3 text-center">
                <h1 class="fw-bold"><?= $jatah_makan ?></h1>
                <small>Jatah Makan (Porsi)</small>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white fw-bold">Rincian Jatah Makan Per Kobong</div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered mb-0 table-sm">
                <thead class="table-light"><tr><th>Kobong</th><th>Total</th><th>Izin</th><th>Jatah Makan</th></tr></thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($qk, 0);
                    while($k = mysqli_fetch_array($qk)){
                        $kid = $k['id'];
                        $tot_k = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM santri WHERE kobong_id='$kid' AND status='aktif'"));
                        $izn_k = mysqli_num_rows(mysqli_query($conn, "SELECT p.id FROM perizinan p JOIN santri s ON p.santri_id=s.id WHERE s.kobong_id='$kid' AND p.status='keluar'"));
                        $makan_k = $tot_k - $izn_k;
                        echo "<tr><td>$k[nama_kobong]</td><td>$tot_k</td><td class='text-danger'>$izn_k</td><td class='fw-bold text-success'>$makan_k</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php elseif($_GET['tab']=='santri' && $role=='sekretaris'): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-warning text-dark fw-bold">Management Data Santri</div>
        <div class="card-body">
            <form method="post" class="row g-2 mb-4">
                <div class="col-md-4"><input type="text" name="nama" class="form-control" placeholder="Nama Santri" required></div>
                <div class="col-md-3"><input type="text" name="kelas" class="form-control" placeholder="Kelas" required></div>
                <div class="col-md-3"><select name="kobong_id" class="form-select"><?php mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){echo "<option value='$k[id]'>$k[nama_kobong]</option>";} ?></select></div>
                <div class="col-md-2"><button type="submit" name="add_santri" class="btn btn-success w-100">Tambah</button></div>
            </form>
            
            <input type="text" id="cari" class="form-control mb-2" placeholder="Cari Santri..." onkeyup="cariSantri()">
            <div class="table-responsive" style="height: 400px;">
                <table class="table table-sm table-striped" id="tblS">
                    <thead class="table-dark"><tr><th>Nama</th><th>Kelas</th><th>Kobong</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php $qs=mysqli_query($conn,"SELECT s.*, k.nama_kobong FROM santri s LEFT JOIN kobong k ON s.kobong_id=k.id WHERE status='aktif' ORDER BY s.nama_santri ASC");
                        while($s=mysqli_fetch_array($qs)){ ?>
                        <tr>
                            <form method="post">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <td><input type="text" name="nama" class="form-control form-control-sm" value="<?= $s['nama_santri'] ?>"></td>
                                <td><input type="text" name="kelas" class="form-control form-control-sm" value="<?= $s['kelas'] ?>" style="width:80px"></td>
                                <td><select name="kobong_id" class="form-select form-select-sm"><?php mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){ $sl=($s['kobong_id']==$k['id'])?'selected':''; echo "<option value='$k[id]' $sl>$k[nama_kobong]</option>"; } ?></select></td>
                                <td><button type="submit" name="update_santri" class="btn btn-primary btn-sm">Simpan</button></td>
                            </form>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php elseif($_GET['tab']=='izin'): ?>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-danger text-white">Input Izin / Pulang</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-2">
                            <label>Pilih Santri</label>
                            <select name="santri_id" class="form-select select2" required>
                                <option value="">-- Cari Nama --</option>
                                <?php $qs=mysqli_query($conn,"SELECT * FROM santri WHERE status='aktif' ORDER BY nama_santri"); while($s=mysqli_fetch_array($qs)){echo "<option value='$s[id]'>$s[nama_santri]</option>";} ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label>Jenis</label>
                            <select name="jenis" class="form-select">
                                <option value="pulang">Pulang (Lama)</option>
                                <option value="izin">Izin (Sebentar)</option>
                                <option value="sakit">Sakit (Di RS/Rumah)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Alasan..."></textarea>
                        </div>
                        <button type="submit" name="input_izin" class="btn btn-danger w-100">Simpan Izin</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Data Santri Sedang Diluar</div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light"><tr><th>Santri</th><th>Jenis</th><th>Sejak</th><th>Oleh</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php 
                            $qi = mysqli_query($conn, "SELECT p.*, s.nama_santri, k.nama_kobong FROM perizinan p JOIN santri s ON p.santri_id=s.id LEFT JOIN kobong k ON s.kobong_id=k.id WHERE p.status='keluar' ORDER BY p.tgl_keluar DESC");
                            if(mysqli_num_rows($qi) == 0) echo "<tr><td colspan='5' class='text-center py-3'>Semua Santri Ada di Pondok</td></tr>";
                            while($i = mysqli_fetch_array($qi)){
                            ?>
                            <tr>
                                <td><b><?= $i['nama_santri'] ?></b><br><small><?= $i['nama_kobong'] ?></small></td>
                                <td><span class="badge bg-danger"><?= strtoupper($i['jenis']) ?></span><br><small><?= $i['keterangan'] ?></small></td>
                                <td><?= date('d/m H:i', strtotime($i['tgl_keluar'])) ?></td>
                                <td><small><?= $i['diinput_oleh'] ?></small></td>
                                <td><a href="?tab=izin&konfirmasi_kembali=<?= $i['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Santri sudah kembali?')">Kembali</a></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php elseif($_GET['tab']=='notulen'): ?>
    <div class="row">
        <?php if($role == 'sekretaris'): ?>
        <div class="col-md-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5>Buat Notulen Baru</h5>
                    <form method="post">
                        <input type="text" name="judul" class="form-control mb-2" placeholder="Judul Rapat" required>
                        <textarea name="isi" class="form-control mb-2" rows="4" placeholder="Hasil Rapat..." required></textarea>
                        <button type="submit" name="simpan_notulen" class="btn btn-primary">Sebarkan Notulen</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="col-md-12">
            <h5 class="mb-3">Arsip Notulen Rapat</h5>
            <div class="list-group">
                <?php $qn = mysqli_query($conn, "SELECT * FROM notulen ORDER BY id DESC LIMIT 10");
                while($n=mysqli_fetch_array($qn)){ ?>
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1 text-primary"><?= $n['judul'] ?></h5>
                        <small><?= $n['tanggal'] ?></small>
                    </div>
                    <p class="mb-1"><?= nl2br($n['isi']) ?></p>
                    <small class="text-muted">Ditulis oleh: <?= $n['pembuat'] ?></small>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
function cariSantri() {
    var i=document.getElementById("cari"), f=i.value.toUpperCase(), t=document.getElementById("tblS"), tr=t.getElementsByTagName("tr");
    for(var j=0;j<tr.length;j++){ var td=tr[j].getElementsByTagName("td")[0]; if(td){ tr[j].style.display=td.getElementsByTagName("input")[0].value.toUpperCase().indexOf(f)>-1?"":"none"; } }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>