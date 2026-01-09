<?php
session_start();
include 'koneksi.php';

// Validasi Login (Hanya Admin & Superadmin)
if(!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin')){
    header("location:index.php");
    exit;
}

// --- LOGIC CRUD ---

// 1. SANTRI & AKUN ORANG TUA (AUTO)
if(isset($_POST['add_santri'])){
    $nm=$_POST['nama']; $kl=$_POST['kelas']; $kb=$_POST['kobong_id'];
    if(mysqli_query($conn,"INSERT INTO santri (nama_santri,kelas,kobong_id,status) VALUES ('$nm','$kl','$kb','aktif')")){
        $uid=mysqli_insert_id($conn); 
        // Buat username unik (nama depan + 3 angka acak)
        $usr=explode(" ",strtolower($nm))[0].rand(100,999);
        // Cek username kembar
        while(mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE username='$usr'")) > 0){
            $usr=explode(" ",strtolower($nm))[0].rand(100,999);
        }
        // Masukkan ke tabel users (Role orangtua langsung di tabel users karena single role)
        // Catatan: Sesuai struktur database baru, user_roles dipisah, tapi untuk orangtua biasanya 1 akun 1 anak.
        // Agar konsisten dengan Multi-Role, kita buat User dulu, lalu Insert Role.
        
        // A. Buat User Identity
        mysqli_query($conn, "INSERT INTO users (nama_lengkap, username, password) VALUES ('Wali $nm', '$usr', '12345')");
        $id_user_baru = mysqli_insert_id($conn);
        
        // B. Masukan Role Orangtua
        mysqli_query($conn, "INSERT INTO user_roles (user_id, role, santri_id) VALUES ('$id_user_baru', 'orangtua', '$uid')");
        
        echo "<script>alert('Santri Ditambah & Akun Walisantri Dibuat.\\nUsername: $usr\\nPassword: 12345'); window.location='?tab=santri';</script>";
    }
}
if(isset($_POST['edit_santri'])){ mysqli_query($conn,"UPDATE santri SET nama_santri='$_POST[nama]', kelas='$_POST[kelas]', kobong_id='$_POST[kobong_id]' WHERE id='$_POST[id]'"); echo "<script>window.location='?tab=santri';</script>"; }
if(isset($_GET['hapus_santri'])){ 
    // Hapus User Orangtua terkait (Cari user_id dari user_roles)
    $sid = $_GET['hapus_santri'];
    $q_cari = mysqli_query($conn, "SELECT user_id FROM user_roles WHERE santri_id='$sid' AND role='orangtua'");
    if($d_cari = mysqli_fetch_assoc($q_cari)){
        $uid = $d_cari['user_id'];
        mysqli_query($conn, "DELETE FROM users WHERE id='$uid'"); // Hapus User (Role ikut terhapus krn Cascade)
    }
    mysqli_query($conn,"DELETE FROM santri WHERE id='$sid'"); 
    echo "<script>window.location='?tab=santri';</script>"; 
}

// 2. RESET PASSWORD WALISANTRI
if(isset($_GET['reset_pass'])){ 
    mysqli_query($conn,"UPDATE users SET password='12345' WHERE id='$_GET[reset_pass]'"); 
    echo "<script>alert('Pass Reset: 12345'); window.location='?tab=akun';</script>"; 
}

// 3. STAFF / SEKBID (SISTEM MULTI ROLE)
// A. Buat User Baru (Orangnya)
if(isset($_POST['create_user'])){
    $n=$_POST['nama']; $u=$_POST['username']; $p=$_POST['password'];
    if(mysqli_num_rows(mysqli_query($conn,"SELECT id FROM users WHERE username='$u'"))==0){
        mysqli_query($conn,"INSERT INTO users (nama_lengkap,username,password) VALUES ('$n','$u','$p')");
        echo "<script>alert('User Dibuat. Silakan tambah jabatan di form sebelah.'); window.location='?tab=staff';</script>";
    } else { echo "<script>alert('Username sudah ada!'); window.location='?tab=staff';</script>"; }
}
// B. Tambah Jabatan ke User
if(isset($_POST['add_role'])){
    $u_target=$_POST['username_target']; $role=$_POST['role']; $kid=($role=='pengurus')?$_POST['kobong_id']:'NULL';
    $cek_u = mysqli_query($conn,"SELECT id FROM users WHERE username='$u_target'");
    if(mysqli_num_rows($cek_u)>0){
        $uid = mysqli_fetch_assoc($cek_u)['id'];
        if(mysqli_num_rows(mysqli_query($conn,"SELECT id FROM user_roles WHERE user_id='$uid' AND role='$role'"))==0){
            mysqli_query($conn,"INSERT INTO user_roles (user_id,role,kobong_id) VALUES ('$uid','$role',$kid)");
            echo "<script>alert('Jabatan Berhasil Ditambah!'); window.location='?tab=staff';</script>";
        } else { echo "<script>alert('User sudah punya jabatan ini!'); window.location='?tab=staff';</script>"; }
    } else { echo "<script>alert('Username tidak ditemukan!'); window.location='?tab=staff';</script>"; }
}
// C. Hapus Jabatan
if(isset($_GET['hapus_role'])){ mysqli_query($conn,"DELETE FROM user_roles WHERE id='$_GET[hapus_role]'"); echo "<script>window.location='?tab=staff';</script>"; }

// 4. KOBONG
if(isset($_POST['add_kobong'])){ mysqli_query($conn,"INSERT INTO kobong (nama_kobong) VALUES ('$_POST[nama_kobong]')"); echo "<script>window.location='?tab=kobong';</script>"; }
if(isset($_POST['edit_kobong'])){ mysqli_query($conn,"UPDATE kobong SET nama_kobong='$_POST[nama_kobong]' WHERE id='$_POST[id]'"); echo "<script>window.location='?tab=kobong';</script>"; }
if(isset($_GET['hapus_kobong'])){ mysqli_query($conn,"DELETE FROM kobong WHERE id='$_GET[hapus_kobong]'"); echo "<script>window.location='?tab=kobong';</script>"; }

// 5. MUTASI & KELULUSAN
if(isset($_POST['proses_mutasi'])){ 
    $ids=$_POST['ids']; $t=$_POST['target']; $tp=$_POST['tipe'];
    if(!empty($ids)){ $il=implode(",",$ids); mysqli_query($conn,"UPDATE santri SET ".($tp=='kobong'?"kobong_id='$t'":"kelas='$t'")." WHERE id IN ($il)"); echo "<script>alert('Sukses'); window.location='?tab=mutasi';</script>"; }
}
if(isset($_POST['proses_lulus'])){ 
    if(!empty($_POST['ids'])){
        foreach($_POST['ids'] as $id){
            if($_POST['aksi'][$id]=='lanjut') mysqli_query($conn,"UPDATE santri SET kelas='10 MA' WHERE id='$id'");
            else { 
                mysqli_query($conn,"UPDATE santri SET status='lulus',kobong_id=NULL WHERE id='$id'");
                // Hapus akun ortu
                $q_ur = mysqli_query($conn, "SELECT user_id FROM user_roles WHERE santri_id='$id' AND role='orangtua'");
                if($du = mysqli_fetch_assoc($q_ur)){
                    mysqli_query($conn, "DELETE FROM users WHERE id='$du[user_id]'");
                }
            }
        } echo "<script>alert('Sukses'); window.location='?tab=lulus';</script>";
    }
}

// PREPARE DATA
$qk = mysqli_query($conn, "SELECT * FROM kobong");
$es = isset($_GET['edit_s'])?mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM santri WHERE id='$_GET[edit_s]'")):null;
$ek = isset($_GET['edit_k'])?mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM kobong WHERE id='$_GET[edit_k]'")):null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Master</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; }
        body { background-color: #f8f9fa; }
        .bg-islamic { background: linear-gradient(135deg, var(--main), #198754); color: white; }
        .nav-pills .nav-link.active { background-color: var(--main); color: var(--gold); font-weight: bold; }
        .nav-pills .nav-link { color: var(--main); background: white; margin-bottom: 5px; border: 1px solid #ddd; }
        .table-responsive { overflow-x: auto; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-islamic p-3 mb-4 shadow">
    <div class="container-fluid"><span class="navbar-brand"><i class="bi bi-database-gear"></i> Data Master</span><a href="admin.php" class="btn btn-gold btn-sm">Kembali</a></div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="nav flex-column nav-pills">
                <a href="?tab=santri" class="nav-link <?= (!isset($_GET['tab'])||$_GET['tab']=='santri')?'active':'' ?>"><i class="bi bi-people"></i> Data Santri</a>
                <a href="?tab=akun" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='akun')?'active':'' ?>"><i class="bi bi-key"></i> Akun Walisantri</a>
                <a href="?tab=staff" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='staff')?'active':'' ?>"><i class="bi bi-person-badge"></i> Data Staff / Sekbid</a>
                <a href="?tab=kobong" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='kobong')?'active':'' ?>"><i class="bi bi-houses"></i> Data Kobong</a>
                <a href="?tab=mutasi" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='mutasi')?'active':'' ?>"><i class="bi bi-arrow-left-right"></i> Mutasi</a>
                <a href="?tab=lulus" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='lulus')?'active':'' ?>"><i class="bi bi-mortarboard"></i> Kelulusan</a>
            </div>
        </div>

        <div class="col-md-9">
            
            <?php if(!isset($_GET['tab'])||$_GET['tab']=='santri'): ?>
            <div class="card shadow-sm"><div class="card-body">
                <h5 class="card-title text-success">Kelola Data Santri</h5>
                <form method="post" class="row g-2"><?php if($es):?><input type="hidden" name="id" value="<?=$es['id']?>"><?php endif;?>
                <div class="col-md-4"><input type="text" name="nama" class="form-control" placeholder="Nama" value="<?=$es['nama_santri']??''?>" required></div>
                <div class="col-md-3"><input type="text" name="kelas" class="form-control" placeholder="Kelas" value="<?=$es['kelas']??''?>" required></div>
                <div class="col-md-3"><select name="kobong_id" class="form-select"><?php mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){ $s=($es&&$es['kobong_id']==$k['id'])?'selected':''; echo "<option value='$k[id]' $s>$k[nama_kobong]</option>"; } ?></select></div>
                <div class="col-md-2"><button type="submit" name="<?=$es?'edit_santri':'add_santri'?>" class="btn btn-success w-100">Simpan</button></div>
                </form><hr>
                <div class="table-responsive" style="height:400px;"><table class="table table-sm table-striped" style="min-width:500px">
                <thead class="table-dark"><tr><th>Nama</th><th>Kelas</th><th>Kobong</th><th>Aksi</th></tr></thead>
                <?php $qs=mysqli_query($conn,"SELECT s.*,k.nama_kobong FROM santri s JOIN kobong k ON s.kobong_id=k.id WHERE status='aktif' ORDER BY s.id DESC"); while($s=mysqli_fetch_array($qs)){ echo "<tr><td>$s[nama_santri]</td><td>$s[kelas]</td><td>$s[nama_kobong]</td><td><a href='?tab=santri&edit_s=$s[id]'>Edit</a> | <a href='?tab=santri&hapus_santri=$s[id]' onclick=\"return confirm('Hapus?')\">Hapus</a></td></tr>"; } ?>
                </table></div>
            </div></div>

            <?php elseif($_GET['tab']=='akun'): ?>
            <div class="card shadow-sm"><div class="card-header bg-primary text-white">Akun Walisantri</div><div class="card-body">
                <div class="alert alert-info py-2 small">Password default: <b>12345</b>. Username dibuat otomatis saat santri ditambah.</div>
                <input type="text" id="cariAkun" class="form-control mb-2" placeholder="Cari Nama Santri..." onkeyup="filterAkun()">
                <div class="table-responsive" style="height:400px;">
                <table class="table table-bordered table-sm" id="tabelAkun"><thead class="table-light"><tr><th>Santri</th><th>Username</th><th>Pass</th><th>Aksi</th></tr></thead><tbody>
                <?php 
                $qu = mysqli_query($conn, "SELECT u.*, s.nama_santri FROM users u JOIN user_roles ur ON u.id=ur.user_id JOIN santri s ON ur.santri_id=s.id WHERE ur.role='orangtua' AND s.status='aktif'");
                while($u=mysqli_fetch_array($qu)){ echo "<tr><td><b>$u[nama_santri]</b></td><td class='text-primary'>$u[username]</td><td>$u[password]</td><td><a href='?tab=akun&reset_pass=$u[id]' class='btn btn-dark btn-sm py-0'>Reset</a></td></tr>"; } 
                ?>
                </tbody></table></div>
            </div></div>

            <?php elseif($_GET['tab']=='staff'): ?>
            <div class="row">
                <div class="col-md-5 mb-3"><div class="card shadow-sm"><div class="card-header bg-dark text-white">1. Buat User (Orang)</div><div class="card-body">
                    <form method="post">
                        <div class="mb-2"><label>Nama Lengkap</label><input type="text" name="nama" class="form-control" required></div>
                        <div class="mb-2"><label>Username</label><input type="text" name="username" class="form-control" required></div>
                        <div class="mb-3"><label>Password</label><input type="text" name="password" class="form-control" required></div>
                        <button type="submit" name="create_user" class="btn btn-dark w-100">Buat User</button>
                    </form>
                </div></div></div>
                
                <div class="col-md-7 mb-3"><div class="card shadow-sm"><div class="card-header bg-success text-white">2. Tambah Jabatan</div><div class="card-body">
                    <form method="post" class="row g-2">
                        <div class="col-6"><label>Username Target</label><input type="text" name="username_target" class="form-control" placeholder="Ketik Username..." required></div>
                        <div class="col-6">
                            <label>Pilih Jabatan</label>
                            <select name="role" id="roleS" class="form-select" onchange="cekR()" required>
                                <option value="">- Pilih -</option>
                                <option value="pengurus">Pengurus Kobong</option>
                                <option value="sekretaris">Sekretaris</option>
                                <option value="keamanan">Bag. Keamanan</option>
                                <option value="rois">Rois (Ketua)</option>
                                <option value="kurikulum">Bag. Kurikulum</option>
                                <option value="ubudiah">Bag. Ubudiah</option>
                                <option value="kebersihan">Bag. Kebersihan</option>
                                <option value="peralatan">Bag. Peralatan</option>
                            </select>
                        </div>
                        <div class="col-12" id="boxK" style="display:none">
                            <label>Pegang Kobong Mana?</label>
                            <select name="kobong_id" class="form-select"><?php mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){ echo "<option value='$k[id]'>$k[nama_kobong]</option>"; } ?></select>
                        </div>
                        <div class="col-12 mt-2"><button type="submit" name="add_role" class="btn btn-success w-100">Simpan Jabatan</button></div>
                    </form>
                </div></div></div>

                <div class="col-12"><div class="card shadow-sm"><div class="card-header bg-white">Daftar Staff & Jabatan</div><div class="card-body p-0 table-responsive">
                    <table class="table table-bordered mb-0 table-hover"><thead class="table-light"><tr><th>Nama (Username)</th><th>Jabatan Dimiliki</th></tr></thead><tbody>
                    <?php 
                    $q_staff = mysqli_query($conn, "SELECT DISTINCT u.* FROM users u JOIN user_roles ur ON u.id=ur.user_id WHERE ur.role NOT IN ('orangtua','admin','superadmin')");
                    while($u=mysqli_fetch_array($q_staff)){
                        echo "<tr><td><b>$u[nama_lengkap]</b> <br><small class='text-muted'>@$u[username]</small></td><td>";
                        $uid=$u['id'];
                        $q_roles=mysqli_query($conn,"SELECT ur.*,k.nama_kobong FROM user_roles ur LEFT JOIN kobong k ON ur.kobong_id=k.id WHERE ur.user_id='$uid'");
                        while($r=mysqli_fetch_array($q_roles)){
                            $ket = $r['nama_kobong'] ? "($r[nama_kobong])" : "";
                            echo "<span class='badge bg-info text-dark border me-1 mb-1'>".strtoupper($r['role'])." $ket <a href='?tab=staff&hapus_role=$r[id]' class='text-danger fw-bold ms-1' style='text-decoration:none' onclick=\"return confirm('Cabut?')\">&times;</a></span>";
                        }
                        echo "</td></tr>";
                    } ?>
                    </tbody></table>
                </div></div></div>
            </div>

            <?php elseif($_GET['tab']=='kobong'): ?>
            <div class="card"><div class="card-body"><form method="post" class="d-flex gap-2 mb-2"><?php if($ek):?><input type="hidden" name="id" value="<?=$ek['id']?>"><?php endif;?><input type="text" name="nama_kobong" class="form-control" value="<?=$ek['nama_kobong']??''?>" placeholder="Nama Kobong" required><button type="submit" name="<?=$ek?'edit_kobong':'add_kobong'?>" class="btn btn-success">Simpan</button></form><ul class="list-group"><?php mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){ echo "<li class='list-group-item d-flex justify-content-between'>$k[nama_kobong] <div><a href='?tab=kobong&edit_k=$k[id]'>Edit</a> | <a href='?tab=kobong&hapus_kobong=$k[id]'>Hapus</a></div></li>"; } ?></ul></div></div>

            <?php elseif($_GET['tab']=='mutasi'): ?>
            <div class="card shadow-sm"><div class="card-header bg-warning">Mutasi Santri</div><div class="card-body">
                <form method="get" class="row g-2 mb-3 bg-light p-2"><input type="hidden" name="tab" value="mutasi">
                    <div class="col-6"><select name="mode" class="form-select" onchange="this.form.submit()"><option value="kobong" <?= ($_GET['mode']??'')=='kobong'?'selected':'' ?>>Pindah Kobong</option><option value="kelas" <?= ($_GET['mode']??'')=='kelas'?'selected':'' ?>>Naik Kelas</option></select></div>
                    <div class="col-6"><select name="sumber" class="form-select" onchange="this.form.submit()"><option value="">- Asal -</option><?php if(($_GET['mode']??'kobong')=='kobong'){ mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){ $s=($_GET['sumber']??'')==$k['id']?'selected':''; echo "<option value='$k[id]' $s>$k[nama_kobong]</option>"; } } else { $qkl=mysqli_query($conn,"SELECT DISTINCT kelas FROM santri WHERE status='aktif' ORDER BY kelas"); while($kl=mysqli_fetch_array($qkl)){ $s=($_GET['sumber']??'')==$kl['kelas']?'selected':''; echo "<option value='$kl[kelas]' $s>$kl[kelas]</option>"; } } ?></select></div>
                </form>
                <?php if(isset($_GET['sumber']) && $_GET['sumber']!=''): ?>
                <form method="post"><input type="hidden" name="tipe_mutasi" value="<?= $_GET['mode']??'kobong' ?>">
                <div class="table-responsive" style="max-height:300px;"><table class="table table-sm"><thead><tr><th width="30"><input type="checkbox" onclick="toggle(this)"></th><th>Nama</th></tr></thead><tbody>
                <?php $fil=$_GET['sumber']; $col=($_GET['mode']??'kobong')=='kobong'?'kobong_id':'kelas'; $qs=mysqli_query($conn,"SELECT * FROM santri WHERE $col='$fil' AND status='aktif'"); while($s=mysqli_fetch_array($qs)){ echo "<tr><td><input type='checkbox' name='ids[]' value='$s[id]'></td><td>$s[nama_santri]</td></tr>"; } ?>
                </tbody></table></div>
                <div class="input-group mt-2"><?php if(($_GET['mode']??'kobong')=='kobong'){ echo "<select name='target' class='form-select' required><option value=''>- Tujuan -</option>"; mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){ echo "<option value='$k[id]'>$k[nama_kobong]</option>"; } echo "</select>"; } else { echo "<input type='text' name='target' class='form-control' placeholder='Kelas Baru' required>"; } ?><button type="submit" name="proses_mutasi" class="btn btn-primary">Simpan</button></div></form><?php endif; ?>
            </div></div>

            <?php elseif($_GET['tab']=='lulus'): ?>
            <div class="card shadow-sm"><div class="card-header bg-danger text-white">Kelulusan</div><div class="card-body">
                <form method="get" class="d-flex gap-2 mb-3"><input type="hidden" name="tab" value="lulus"><select name="kelas_akhir" class="form-select"><?php $qkl=mysqli_query($conn,"SELECT DISTINCT kelas FROM santri WHERE status='aktif'"); while($kl=mysqli_fetch_array($qkl)){ echo "<option value='$kl[kelas]'>$kl[kelas]</option>"; } ?></select><button class="btn btn-dark">Cek</button></form>
                <?php if(isset($_GET['kelas_akhir'])): ?><form method="post"><div class="table-responsive"><table class="table table-bordered" style="min-width:400px;"><thead><tr><th>Nama</th><th>Aksi</th></tr></thead><tbody>
                <?php $qa=mysqli_query($conn,"SELECT * FROM santri WHERE kelas='$_GET[kelas_akhir]' AND status='aktif'"); while($sa=mysqli_fetch_array($qa)){ ?>
                <tr><td><input type="hidden" name="ids[]" value="<?= $sa['id'] ?>"><?= $sa['nama_santri'] ?></td><td><label class="me-2"><input type="radio" name="aksi[<?= $sa['id'] ?>]" value="lanjut" checked> Lanjut</label><label class="text-danger"><input type="radio" name="aksi[<?= $sa['id'] ?>]" value="lulus"> Lulus</label></td></tr><?php } ?></tbody></table></div><button type="submit" name="proses_lulus" class="btn btn-primary w-100">Proses</button></form><?php endif; ?>
            </div></div>
            <?php endif; ?>

        </div>
    </div>
</div>
<script>
function filterAkun() { var i=document.getElementById("cariAkun"), f=i.value.toUpperCase(), t=document.getElementById("tabelAkun"), tr=t.getElementsByTagName("tr"); for(var j=0;j<tr.length;j++){ var td=tr[j].getElementsByTagName("td")[0]; if(td){ tr[j].style.display=td.innerText.toUpperCase().indexOf(f)>-1?"":"none"; } } }
function cekR() { var r=document.getElementById("roleS").value; document.getElementById("boxK").style.display=(r==='pengurus')?'block':'none'; }
function toggle(s) { var c=document.getElementsByName('ids[]'); for(var i=0;i<c.length;i++){ c[i].checked=s.checked; } }
cekR(); 
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>