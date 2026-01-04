<?php
session_start();
include 'koneksi.php';
if($_SESSION['role'] != 'admin') header("location:index.php");

// --- GLOBAL VARIABLES (Ditaruh paling atas agar terbaca di semua tab) ---
$qk = mysqli_query($conn, "SELECT * FROM kobong"); 

// --- LOGIC CRUD ---

// 1. SANTRI
if(isset($_POST['add_santri'])){
    mysqli_query($conn, "INSERT INTO santri (nama_santri, kelas, kobong_id, status) VALUES ('$_POST[nama]','$_POST[kelas]','$_POST[kobong_id]', 'aktif')");
    echo "<script>alert('Santri Ditambah'); window.location='admin_data.php?tab=santri';</script>";
}
if(isset($_POST['edit_santri'])){
    mysqli_query($conn, "UPDATE santri SET nama_santri='$_POST[nama]', kelas='$_POST[kelas]', kobong_id='$_POST[kobong_id]' WHERE id='$_POST[id]'");
    echo "<script>alert('Santri Diupdate'); window.location='admin_data.php?tab=santri';</script>";
}
if(isset($_GET['hapus_santri'])){
    mysqli_query($conn, "DELETE FROM santri WHERE id='$_GET[hapus_santri]'");
    echo "<script>alert('Santri Dihapus'); window.location='admin_data.php?tab=santri';</script>";
}

// 2. KOBONG
if(isset($_POST['add_kobong'])){
    mysqli_query($conn, "INSERT INTO kobong (nama_kobong) VALUES ('$_POST[nama_kobong]')");
    echo "<script>alert('Kobong Ditambah'); window.location='admin_data.php?tab=kobong';</script>";
}
if(isset($_POST['edit_kobong'])){
    mysqli_query($conn, "UPDATE kobong SET nama_kobong='$_POST[nama_kobong]' WHERE id='$_POST[id]'");
    echo "<script>alert('Kobong Diupdate'); window.location='admin_data.php?tab=kobong';</script>";
}
if(isset($_GET['hapus_kobong'])){
    mysqli_query($conn, "DELETE FROM kobong WHERE id='$_GET[hapus_kobong]'");
    echo "<script>alert('Kobong Dihapus'); window.location='admin_data.php?tab=kobong';</script>";
}

// 3. PENGURUS
if(isset($_POST['simpan_pengurus'])){
    $user=$_POST['username']; $pass=$_POST['password']; $kid=$_POST['kobong_id']; $id=$_POST['id_edit'];
    if($id) { $q="UPDATE users SET username='$user', kobong_id='$kid'".($pass?", password='$pass'":"")." WHERE id='$id'"; mysqli_query($conn, $q); }
    else { mysqli_query($conn, "INSERT INTO users (username, password, role, kobong_id) VALUES ('$user','$pass','pengurus','$kid')"); }
    echo "<script>alert('Data Pengurus Disimpan'); window.location='admin_data.php?tab=pengurus';</script>";
}
if(isset($_GET['hapus_pengurus'])){
    mysqli_query($conn, "DELETE FROM users WHERE id='$_GET[hapus_pengurus]'");
    echo "<script>alert('Pengurus Dihapus'); window.location='admin_data.php?tab=pengurus';</script>";
}

// 4. MUTASI (PINDAH KOBONG / NAIK KELAS MASSAL)
if(isset($_POST['proses_mutasi'])){
    $ids = $_POST['ids']; // Array ID santri
    $target = $_POST['target_value']; 
    $tipe = $_POST['tipe_mutasi']; 
    
    if(!empty($ids)){
        $id_list = implode(",", $ids);
        if($tipe == 'kobong'){
            mysqli_query($conn, "UPDATE santri SET kobong_id='$target' WHERE id IN ($id_list)");
            $msg = "Santri Berhasil Pindah Kobong!";
        } else {
            mysqli_query($conn, "UPDATE santri SET kelas='$target' WHERE id IN ($id_list)");
            $msg = "Santri Berhasil Naik Kelas!";
        }
        echo "<script>alert('$msg'); window.location='admin_data.php?tab=mutasi';</script>";
    } else {
        echo "<script>alert('Pilih minimal satu santri!'); window.location='admin_data.php?tab=mutasi';</script>";
    }
}

// 5. KELULUSAN / ALUMNI
if(isset($_POST['proses_lulus'])){
    $ids = $_POST['ids']; 
    $aksi = $_POST['aksi']; 
    if(!empty($ids)){
        foreach($ids as $id){
            $pilihan = $aksi[$id]; 
            if($pilihan == 'lanjut'){
                mysqli_query($conn, "UPDATE santri SET kelas='10 MA' WHERE id='$id'"); // Default naik, bisa diedit nanti
            } else {
                mysqli_query($conn, "UPDATE santri SET status='lulus', kobong_id=NULL WHERE id='$id'");
            }
        }
        echo "<script>alert('Data Kelulusan Diproses!'); window.location='admin_data.php?tab=lulus';</script>";
    }
}

// Prepare Edit Data
$edit_p = isset($_GET['edit_p']) ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$_GET[edit_p]'")) : null;
$edit_s = isset($_GET['edit_s']) ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM santri WHERE id='$_GET[edit_s]'")) : null;
$edit_k = isset($_GET['edit_k']) ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kobong WHERE id='$_GET[edit_k]'")) : null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manajemen Data Master</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; }
        body { background-color: #f8f9fa; }
        .bg-islamic { background: linear-gradient(135deg, var(--main), #198754); color: white; }
        .nav-pills .nav-link.active { background-color: var(--main); color: var(--gold); font-weight: bold; }
        .nav-pills .nav-link { color: var(--main); background: white; margin-bottom: 5px; border: 1px solid #ddd; }
        .table-check td { vertical-align: middle; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-islamic p-3 mb-4 shadow">
    <div class="container">
        <span class="navbar-brand"><i class="bi bi-database-gear"></i> Kelola Data Pesantren</span>
        <a href="admin.php" class="btn btn-gold btn-sm"><i class="bi bi-arrow-left"></i> Kembali Dashboard</a>
    </div>
</nav>

<div class="container">
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="nav flex-column nav-pills">
                <a href="?tab=santri" class="nav-link <?= (!isset($_GET['tab'])||$_GET['tab']=='santri')?'active':'' ?>"><i class="bi bi-people"></i> Data Santri</a>
                <a href="?tab=pengurus" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='pengurus')?'active':'' ?>"><i class="bi bi-person-badge"></i> Data Pengurus</a>
                <a href="?tab=kobong" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='kobong')?'active':'' ?>"><i class="bi bi-houses"></i> Data Kobong</a>
                <a href="?tab=mutasi" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='mutasi')?'active':'' ?>"><i class="bi bi-arrow-left-right"></i> Mutasi (Pindah/Naik)</a>
                <a href="?tab=lulus" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='lulus')?'active':'' ?>"><i class="bi bi-mortarboard"></i> Kelulusan / Alumni</a>
            </div>
        </div>

        <div class="col-md-9">
            
            <?php if(!isset($_GET['tab']) || $_GET['tab']=='santri'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white text-success fw-bold"><?= $edit_s ? 'Edit Santri' : 'Tambah Santri' ?></div>
                <div class="card-body">
                    <form method="post" class="row g-2">
                        <?php if($edit_s): ?><input type="hidden" name="id" value="<?= $edit_s['id'] ?>"><?php endif; ?>
                        <div class="col-md-4"><input type="text" name="nama" class="form-control" placeholder="Nama" value="<?= $edit_s['nama_santri']??'' ?>" required></div>
                        <div class="col-md-3"><input type="text" name="kelas" class="form-control" placeholder="Kelas" value="<?= $edit_s['kelas']??'' ?>" required></div>
                        <div class="col-md-3">
                            <select name="kobong_id" class="form-select" required>
                                <option value="">- Kobong -</option>
                                <?php mysqli_data_seek($qk,0); // Reset pointer
                                while($k=mysqli_fetch_array($qk)){ 
                                    $sel = ($edit_s && $edit_s['kobong_id']==$k['id'])?'selected':'';
                                    echo "<option value='$k[id]' $sel>$k[nama_kobong]</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="col-md-2"><button type="submit" name="<?= $edit_s?'edit_santri':'add_santri' ?>" class="btn btn-success w-100"><?= $edit_s?'Update':'Simpan' ?></button></div>
                        <?php if($edit_s): ?><div class="col-12"><a href="?tab=santri" class="btn btn-secondary btn-sm w-100">Batal Edit</a></div><?php endif; ?>
                    </form>

                    <hr>
                    <input type="text" id="cariSantri" class="form-control mb-2" placeholder="Cari nama santri..." onkeyup="filterTable()">
                    <div style="max-height: 400px; overflow-y:auto;">
                        <table class="table table-sm table-striped" id="tabelSantri">
                            <thead><tr><th>Nama</th><th>Kelas</th><th>Kobong</th><th>Aksi</th></tr></thead>
                            <tbody>
                                <?php $qs=mysqli_query($conn,"SELECT s.*,k.nama_kobong FROM santri s JOIN kobong k ON s.kobong_id=k.id WHERE s.status='aktif' ORDER BY s.nama_santri ASC"); 
                                while($s=mysqli_fetch_array($qs)){ ?>
                                <tr>
                                    <td><?= $s['nama_santri'] ?></td>
                                    <td><?= $s['kelas'] ?></td>
                                    <td><?= $s['nama_kobong'] ?></td>
                                    <td>
                                        <a href="?tab=santri&edit_s=<?= $s['id'] ?>" class="btn btn-warning btn-sm py-0"><i class="bi bi-pencil"></i></a>
                                        <a href="?tab=santri&hapus_santri=<?= $s['id'] ?>" class="btn btn-danger btn-sm py-0" onclick="return confirm('Hapus Santri?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <?php elseif($_GET['tab']=='pengurus'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white text-success fw-bold"><?= $edit_p?'Edit Pengurus':'Tambah Pengurus' ?></div>
                <div class="card-body">
                    <form method="post" class="row g-2">
                        <input type="hidden" name="id_edit" value="<?= $edit_p['id']??'' ?>">
                        <div class="col-md-4"><input type="text" name="username" class="form-control" value="<?= $edit_p['username']??'' ?>" placeholder="Username" required></div>
                        <div class="col-md-4"><input type="text" name="password" class="form-control" placeholder="Password (Isi jika ubah)"></div>
                        <div class="col-md-4">
                            <select name="kobong_id" class="form-select">
                                <?php mysqli_data_seek($qk,0); // Reset pointer
                                while($k=mysqli_fetch_array($qk)){ 
                                    $sel=($edit_p && $edit_p['kobong_id']==$k['id'])?'selected':''; echo "<option value='$k[id]' $sel>$k[nama_kobong]</option>";} ?>
                            </select>
                        </div>
                        <div class="col-12 mt-2">
                            <button type="submit" name="simpan_pengurus" class="btn btn-primary btn-sm"><?= $edit_p?'Update':'Simpan' ?></button>
                            <?php if($edit_p): ?><a href="?tab=pengurus" class="btn btn-secondary btn-sm">Batal</a><?php endif; ?>
                        </div>
                    </form>
                    <table class="table table-sm mt-3">
                        <thead><tr><th>Username</th><th>Kobong</th><th>Aksi</th></tr></thead>
                        <tbody>
                        <?php $qu=mysqli_query($conn,"SELECT u.*,k.nama_kobong FROM users u JOIN kobong k ON u.kobong_id=k.id WHERE role='pengurus'");
                        while($u=mysqli_fetch_array($qu)){ echo "<tr><td>$u[username]</td><td>$u[nama_kobong]</td><td>
                            <a href='?tab=pengurus&edit_p=$u[id]' class='btn btn-warning btn-sm py-0'><i class='bi bi-pencil'></i></a>
                            <a href='?tab=pengurus&hapus_pengurus=$u[id]' class='btn btn-danger btn-sm py-0' onclick='return confirm(\"Hapus?\")'><i class='bi bi-trash'></i></a>
                        </td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php elseif($_GET['tab']=='kobong'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white text-success fw-bold"><?= $edit_k?'Edit Kobong':'Tambah Kobong' ?></div>
                <div class="card-body">
                    <form method="post" class="d-flex gap-2 mb-3">
                        <?php if($edit_k): ?><input type="hidden" name="id" value="<?= $edit_k['id'] ?>"><?php endif; ?>
                        <input type="text" name="nama_kobong" class="form-control" value="<?= $edit_k['nama_kobong']??'' ?>" placeholder="Nama Kobong" required>
                        <button type="submit" name="<?= $edit_k?'edit_kobong':'add_kobong' ?>" class="btn btn-success"><i class="bi bi-save"></i></button>
                        <?php if($edit_k): ?><a href="?tab=kobong" class="btn btn-secondary">Batal</a><?php endif; ?>
                    </form>
                    <ul class="list-group">
                        <?php mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){ ?>
                        <li class='list-group-item d-flex justify-content-between'>
                            <span><i class='bi bi-house'></i> <?= $k['nama_kobong'] ?></span>
                            <div>
                                <a href="?tab=kobong&edit_k=<?= $k['id'] ?>" class="text-warning me-2"><i class="bi bi-pencil"></i></a>
                                <a href="?tab=kobong&hapus_kobong=<?= $k['id'] ?>" class="text-danger" onclick="return confirm('Hapus Kobong?')"><i class="bi bi-trash"></i></a>
                            </div>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>

            <?php elseif($_GET['tab']=='mutasi'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark fw-bold">Mutasi (Pindah Kobong / Naik Kelas)</div>
                <div class="card-body">
                    
                    <form method="get" class="row g-2 align-items-end bg-light p-3 rounded mb-3">
                        <input type="hidden" name="tab" value="mutasi">
                        <div class="col-md-5">
                            <label class="small fw-bold">Pilih Kategori Mutasi</label>
                            <select name="mode" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="kobong" <?= (isset($_GET['mode']) && $_GET['mode']=='kobong')?'selected':'' ?>>Pindah Kobong</option>
                                <option value="kelas" <?= (isset($_GET['mode']) && $_GET['mode']=='kelas')?'selected':'' ?>>Naik Kelas</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="small fw-bold">Pilih Asal (Sumber)</label>
                            <?php if(!isset($_GET['mode']) || $_GET['mode']=='kobong'): ?>
                                <select name="sumber" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">-- Pilih Kobong Asal --</option>
                                    <?php mysqli_data_seek($qk,0); // Reset Pointer
                                    while($k=mysqli_fetch_array($qk)){ 
                                        $sel = (isset($_GET['sumber']) && $_GET['sumber']==$k['id'])?'selected':'';
                                        echo "<option value='$k[id]' $sel>$k[nama_kobong]</option>"; 
                                    } ?>
                                </select>
                            <?php else: ?>
                                <select name="sumber" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">-- Pilih Kelas Asal --</option>
                                    <?php 
                                    $qkl = mysqli_query($conn, "SELECT DISTINCT kelas FROM santri WHERE status='aktif' ORDER BY kelas ASC");
                                    while($kl=mysqli_fetch_array($qkl)){ 
                                        $sel = (isset($_GET['sumber']) && $_GET['sumber']==$kl['kelas'])?'selected':'';
                                        echo "<option value='$kl[kelas]' $sel>$kl[kelas]</option>"; 
                                    } ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if(isset($_GET['sumber']) && $_GET['sumber'] != ''): ?>
                    <form method="post">
                        <input type="hidden" name="tipe_mutasi" value="<?= $_GET['mode']??'kobong' ?>">
                        <h6 class="text-success border-bottom pb-2">Daftar Santri</h6>
                        <div class="table-responsive mb-3" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-hover table-check">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="30"><input type="checkbox" onclick="toggle(this)"></th>
                                        <th>Nama Santri</th>
                                        <th><?= ($_GET['mode']??'kobong')=='kobong' ? 'Kelas' : 'Kobong' ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $filter = $_GET['sumber'];
                                    $col = ($_GET['mode']??'kobong')=='kobong' ? 'kobong_id' : 'kelas';
                                    $qs = mysqli_query($conn, "SELECT s.*, k.nama_kobong FROM santri s LEFT JOIN kobong k ON s.kobong_id=k.id WHERE s.$col = '$filter' AND s.status='aktif' ORDER BY s.nama_santri ASC");
                                    if(mysqli_num_rows($qs) == 0) echo "<tr><td colspan='3' class='text-center text-muted'>Tidak ada santri</td></tr>";
                                    while($s = mysqli_fetch_array($qs)){
                                    ?>
                                    <tr>
                                        <td><input type="checkbox" name="ids[]" value="<?= $s['id'] ?>"></td>
                                        <td><?= $s['nama_santri'] ?></td>
                                        <td><?= ($_GET['mode']??'kobong')=='kobong' ? $s['kelas'] : $s['nama_kobong'] ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="card bg-light p-3">
                            <label class="fw-bold mb-2">Pindahkan Terpilih Ke:</label>
                            <div class="input-group">
                                <?php if((isset($_GET['mode']) && $_GET['mode']=='kelas')): ?>
                                    <input type="text" name="target_value" class="form-control" placeholder="Nama Kelas Baru (Contoh: 2 SMA)" required>
                                <?php else: ?>
                                    <select name="target_value" class="form-select" required>
                                        <option value="">-- Pilih Kobong Tujuan --</option>
                                        <?php mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){ echo "<option value='$k[id]'>$k[nama_kobong]</option>"; } ?>
                                    </select>
                                <?php endif; ?>
                                <button type="submit" name="proses_mutasi" class="btn btn-primary">Simpan</button>
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif($_GET['tab']=='lulus'): ?>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-warning fw-bold">Proses Kelulusan (Kelas Akhir)</div>
                <div class="card-body">
                    <form method="get" class="row g-2 mb-3">
                        <input type="hidden" name="tab" value="lulus">
                        <div class="col-md-8">
                            <select name="kelas_akhir" class="form-select">
                                <option value="">-- Pilih Kelas Akhir (Misal: 9 MTs) --</option>
                                <?php $qkl=mysqli_query($conn,"SELECT DISTINCT kelas FROM santri WHERE status='aktif' ORDER BY kelas ASC"); 
                                while($kl=mysqli_fetch_array($qkl)){ echo "<option value='$kl[kelas]'>$kl[kelas]</option>"; } ?>
                            </select>
                        </div>
                        <div class="col-md-4"><button type="submit" class="btn btn-dark w-100">Tampilkan Santri</button></div>
                    </form>

                    <?php if(isset($_GET['kelas_akhir']) && $_GET['kelas_akhir']!=''): ?>
                    <form method="post">
                        <h6 class="text-success border-bottom pb-2">Daftar Santri Kelas <?= $_GET['kelas_akhir'] ?></h6>
                        <table class="table table-bordered table-sm">
                            <thead class="table-light"><tr><th>Nama Santri</th><th>Pilihan Tindakan</th></tr></thead>
                            <tbody>
                                <?php $qa=mysqli_query($conn,"SELECT * FROM santri WHERE kelas='$_GET[kelas_akhir]' AND status='aktif'");
                                if(mysqli_num_rows($qa)==0) echo "<tr><td colspan='2' class='text-center'>Tidak ada santri</td></tr>";
                                while($sa=mysqli_fetch_array($qa)){ ?>
                                <tr>
                                    <td><input type="hidden" name="ids[]" value="<?= $sa['id'] ?>"><?= $sa['nama_santri'] ?></td>
                                    <td>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="aksi[<?= $sa['id'] ?>]" value="lanjut" checked>
                                            <label class="form-check-label text-primary">Lanjut</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="aksi[<?= $sa['id'] ?>]" value="lulus">
                                            <label class="form-check-label text-danger">Lulus / Boyong</label>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php if(mysqli_num_rows($qa)>0): ?>
                        <button type="submit" name="proses_lulus" class="btn btn-primary w-100" onclick="return confirm('Yakin?')">Proses Simpan</button>
                        <?php endif; ?>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white">Data Alumni / Lulusan</div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead><tr><th>Nama</th><th>Terakhir Kelas</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php $qal=mysqli_query($conn,"SELECT * FROM santri WHERE status='lulus' ORDER BY nama_santri ASC");
                            while($al=mysqli_fetch_array($qal)){ echo "<tr><td>$al[nama_santri]</td><td>$al[kelas]</td><td><span class='badge bg-secondary'>Alumni</span></td></tr>"; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function filterTable() {
    var input = document.getElementById("cariSantri");
    var filter = input.value.toUpperCase();
    var table = document.getElementById("tabelSantri");
    var tr = table.getElementsByTagName("tr");
    for (var i = 0; i < tr.length; i++) {
        var td = tr[i].getElementsByTagName("td")[0];
        if (td) {
            var txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) { tr[i].style.display = ""; } else { tr[i].style.display = "none"; }
        }       
    }
}
function toggle(source) {
    checkboxes = document.getElementsByName('ids[]');
    for(var i=0, n=checkboxes.length;i<n;i++) { checkboxes[i].checked = source.checked; }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>