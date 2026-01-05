<?php
session_start();
include 'koneksi.php';
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'superadmin') header("location:index.php");

$qk = mysqli_query($conn, "SELECT * FROM kobong"); 

// --- LOGIC CRUD (Sama seperti sebelumnya) ---
if(isset($_POST['add_santri'])){ $nm=$_POST['nama']; $kl=$_POST['kelas']; $kb=$_POST['kobong_id']; if(mysqli_query($conn,"INSERT INTO santri (nama_santri,kelas,kobong_id,status) VALUES ('$nm','$kl','$kb','aktif')")){ $uid=mysqli_insert_id($conn); $usr=explode(" ",strtolower($nm))[0].rand(100,999); mysqli_query($conn,"INSERT INTO users (username,password,role,santri_id) VALUES ('$usr','12345','orangtua','$uid')"); echo "<script>alert('Santri & Akun Walisantri Dibuat'); window.location='?tab=santri';</script>"; }}
if(isset($_POST['edit_santri'])){ mysqli_query($conn,"UPDATE santri SET nama_santri='$_POST[nama]', kelas='$_POST[kelas]', kobong_id='$_POST[kobong_id]' WHERE id='$_POST[id]'"); echo "<script>window.location='?tab=santri';</script>"; }
if(isset($_GET['hapus_santri'])){ mysqli_query($conn,"DELETE FROM users WHERE santri_id='$_GET[hapus_santri]'"); mysqli_query($conn,"DELETE FROM santri WHERE id='$_GET[hapus_santri]'"); echo "<script>window.location='?tab=santri';</script>"; }
if(isset($_GET['reset_pass'])){ mysqli_query($conn,"UPDATE users SET password='12345' WHERE id='$_GET[reset_pass]'"); echo "<script>alert('Pass Reset: 12345'); window.location='?tab=akun';</script>"; }
if(isset($_POST['simpan_staff'])){ $u=$_POST['username']; $p=$_POST['password']; $r=$_POST['role']; $k=($_POST['role']=='pengurus')?$_POST['kobong_id']:'NULL'; $id=$_POST['id_edit']; if($id) $q="UPDATE users SET username='$u', role='$r', kobong_id=$k".($p?", password='$p'":"")." WHERE id='$id'"; else $q="INSERT INTO users (username,password,role,kobong_id) VALUES ('$u','$p','$r',$k)"; mysqli_query($conn,$q); echo "<script>window.location='?tab=staff';</script>"; }
if(isset($_GET['hapus_staff'])){ mysqli_query($conn,"DELETE FROM users WHERE id='$_GET[hapus_staff]'"); echo "<script>window.location='?tab=staff';</script>"; }
if(isset($_POST['add_kobong'])){ mysqli_query($conn,"INSERT INTO kobong (nama_kobong) VALUES ('$_POST[nama_kobong]')"); echo "<script>window.location='?tab=kobong';</script>"; }
if(isset($_POST['edit_kobong'])){ mysqli_query($conn,"UPDATE kobong SET nama_kobong='$_POST[nama_kobong]' WHERE id='$_POST[id]'"); echo "<script>window.location='?tab=kobong';</script>"; }
if(isset($_GET['hapus_kobong'])){ mysqli_query($conn,"DELETE FROM kobong WHERE id='$_GET[hapus_kobong]'"); echo "<script>window.location='?tab=kobong';</script>"; }
if(isset($_POST['proses_mutasi'])){ $ids=$_POST['ids']; $t=$_POST['target']; $tp=$_POST['tipe']; $il=implode(",",$ids); mysqli_query($conn,"UPDATE santri SET ".($tp=='kobong'?"kobong_id='$t'":"kelas='$t'")." WHERE id IN ($il)"); echo "<script>window.location='?tab=mutasi';</script>"; }
if(isset($_POST['proses_lulus'])){ foreach($_POST['ids'] as $id){ if($_POST['aksi'][$id]=='lanjut') mysqli_query($conn,"UPDATE santri SET kelas='10 MA' WHERE id='$id'"); else { mysqli_query($conn,"UPDATE santri SET status='lulus',kobong_id=NULL WHERE id='$id'"); mysqli_query($conn,"DELETE FROM users WHERE santri_id='$id'"); }} echo "<script>window.location='?tab=lulus';</script>"; }

$es = isset($_GET['edit_s'])?mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM santri WHERE id='$_GET[edit_s]'")):null;
$ep = isset($_GET['edit_p'])?mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$_GET[edit_p]'")):null;
$ek = isset($_GET['edit_k'])?mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM kobong WHERE id='$_GET[edit_k]'")):null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Data Master</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; }
        body { background-color: #f8f9fa; font-family: sans-serif; }
        .bg-islamic { background: linear-gradient(135deg, var(--main), #198754); color: white; }
        .nav-pills .nav-link.active { background-color: var(--main); color: var(--gold); font-weight: bold; }
        .nav-pills .nav-link { color: var(--main); background: white; margin-bottom: 5px; border: 1px solid #ddd; }
        /* Agar tabel bisa di-scroll di HP */
        .table-responsive { overflow-x: auto; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-islamic p-3 mb-4 shadow">
    <div class="container-fluid">
        <span class="navbar-brand"><i class="bi bi-database-gear"></i> Data Master</span>
        <a href="admin.php" class="btn btn-gold btn-sm">Kembali</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="nav flex-column nav-pills">
                <a href="?tab=santri" class="nav-link <?= (!isset($_GET['tab'])||$_GET['tab']=='santri')?'active':'' ?>">Data Santri</a>
                <a href="?tab=akun" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='akun')?'active':'' ?>">Akun Walisantri</a>
                <a href="?tab=staff" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='staff')?'active':'' ?>">Data Staff / Sekbid</a>
                <a href="?tab=kobong" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='kobong')?'active':'' ?>">Data Kobong</a>
                <a href="?tab=mutasi" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='mutasi')?'active':'' ?>">Mutasi</a>
                <a href="?tab=lulus" class="nav-link <?= (isset($_GET['tab'])&&$_GET['tab']=='lulus')?'active':'' ?>">Kelulusan</a>
            </div>
        </div>

        <div class="col-md-9">
            <?php if(!isset($_GET['tab'])||$_GET['tab']=='santri'): ?>
                <div class="card shadow-sm"><div class="card-body">
                <form method="post" class="row g-2"><?php if($es):?><input type="hidden" name="id" value="<?=$es['id']?>"><?php endif;?>
                <div class="col-12 col-md-4"><input type="text" name="nama" class="form-control" placeholder="Nama" value="<?=$es['nama_santri']??''?>" required></div>
                <div class="col-6 col-md-3"><input type="text" name="kelas" class="form-control" placeholder="Kelas" value="<?=$es['kelas']??''?>" required></div>
                <div class="col-6 col-md-3"><select name="kobong_id" class="form-select"><?php mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){ $s=($es&&$es['kobong_id']==$k['id'])?'selected':''; echo "<option value='$k[id]' $s>$k[nama_kobong]</option>"; } ?></select></div>
                <div class="col-12 col-md-2"><button type="submit" name="<?=$es?'edit_santri':'add_santri'?>" class="btn btn-success w-100">Simpan</button></div>
                </form><hr>
                <div class="table-responsive" style="height:400px;"><table class="table table-sm table-striped" style="min-width: 500px;">
                <?php $qs=mysqli_query($conn,"SELECT s.*,k.nama_kobong FROM santri s JOIN kobong k ON s.kobong_id=k.id WHERE status='aktif' ORDER BY s.id DESC"); while($s=mysqli_fetch_array($qs)){ echo "<tr><td>$s[nama_santri]</td><td>$s[kelas]</td><td>$s[nama_kobong]</td><td><a href='?tab=santri&edit_s=$s[id]'>Edit</a> | <a href='?tab=santri&hapus_santri=$s[id]' onclick=\"return confirm('Hapus?')\">Hapus</a></td></tr>"; } ?>
                </table></div></div></div>

            <?php elseif($_GET['tab']=='akun'): ?>
                <div class="card shadow-sm"><div class="card-header bg-primary text-white">Akun Walisantri</div><div class="card-body">
                <input type="text" id="cariAkun" class="form-control mb-3" placeholder="Cari Nama..." onkeyup="filterAkun()">
                <div class="table-responsive" style="height:400px;">
                <table class="table table-bordered table-sm" id="tabelAkun" style="min-width: 500px;"><thead class="table-light"><tr><th>Nama</th><th>Username</th><th>Pass</th><th>Aksi</th></tr></thead><tbody>
                <?php $qu=mysqli_query($conn,"SELECT u.*,s.nama_santri FROM users u JOIN santri s ON u.santri_id=s.id WHERE u.role='orangtua' AND s.status='aktif'"); while($u=mysqli_fetch_array($qu)){ echo "<tr><td>$u[nama_santri]</td><td class='text-primary'>$u[username]</td><td>$u[password]</td><td><a href='?tab=akun&reset_pass=$u[id]' class='btn btn-dark btn-sm py-0' onclick=\"return confirm('Reset?')\">Reset</a></td></tr>"; } ?>
                </tbody></table></div></div></div>

            <?php elseif($_GET['tab']=='staff'): ?>
                <div class="card shadow-sm"><div class="card-header bg-info text-white">Kelola Staff</div><div class="card-body">
                <form method="post" class="row g-2 mb-3"><input type="hidden" name="id_edit" value="<?= $ep['id']??'' ?>">
                    <div class="col-12 col-md-3"><select name="role" class="form-select" id="roleSelect" onchange="cekRole()" required><option value="">- Jabatan -</option><?php foreach(['pengurus'=>'Pengurus Kobong','rois'=>'Rois','sekretaris'=>'Sekretaris','kurikulum'=>'Kurikulum','ubudiah'=>'Ubudiah','kebersihan'=>'Kebersihan','peralatan'=>'Peralatan','keamanan'=>'Keamanan'] as $k=>$v){ $s=($ep&&$ep['role']==$k)?'selected':''; echo "<option value='$k' $s>$v</option>"; } ?></select></div>
                    <div class="col-6 col-md-3"><input type="text" name="username" class="form-control" value="<?= $ep['username']??'' ?>" placeholder="User" required></div>
                    <div class="col-6 col-md-3"><input type="text" name="password" class="form-control" placeholder="Pass"></div>
                    <div class="col-12 col-md-3" id="boxKobong" style="display:none;"><select name="kobong_id" class="form-select"><?php mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){ $s=($ep&&$ep['kobong_id']==$k['id'])?'selected':''; echo "<option value='$k[id]' $s>$k[nama_kobong]</option>"; } ?></select></div>
                    <div class="col-12"><button type="submit" name="simpan_staff" class="btn btn-primary btn-sm w-100"><?= $ep?'Update':'Simpan' ?></button></div>
                </form>
                <div class="table-responsive"><table class="table table-sm table-bordered" style="min-width: 500px;"><thead class="table-light"><tr><th>User</th><th>Jabatan</th><th>Kobong</th><th>Aksi</th></tr></thead><tbody>
                <?php $qu=mysqli_query($conn,"SELECT u.*,k.nama_kobong FROM users u LEFT JOIN kobong k ON u.kobong_id=k.id WHERE role NOT IN ('admin','superadmin','orangtua')"); while($u=mysqli_fetch_array($qu)){ echo "<tr><td>$u[username]</td><td>$u[role]</td><td>".($u['nama_kobong']??'-')."</td><td><a href='?tab=staff&edit_p=$u[id]'>Edit</a> | <a href='?tab=staff&hapus_staff=$u[id]' onclick=\"return confirm('Hapus?')\">Hapus</a></td></tr>"; } ?>
                </tbody></table></div></div></div>

            <?php elseif($_GET['tab']=='kobong'): ?>
                <div class="card"><div class="card-body"><form method="post" class="d-flex gap-2 mb-2"><?php if($ek):?><input type="hidden" name="id" value="<?=$ek['id']?>"><?php endif;?><input type="text" name="nama_kobong" class="form-control" value="<?=$ek['nama_kobong']??''?>" placeholder="Nama Kobong" required><button type="submit" name="<?=$ek?'edit_kobong':'add_kobong'?>" class="btn btn-success">Simpan</button></form><ul class="list-group"><?php mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){ echo "<li class='list-group-item d-flex justify-content-between'>$k[nama_kobong] <div><a href='?tab=kobong&edit_k=$k[id]'>Edit</a> | <a href='?tab=kobong&hapus_kobong=$k[id]'>Hapus</a></div></li>"; } ?></ul></div></div>
            
            <?php elseif($_GET['tab']=='mutasi'): ?>
                <div class="card shadow-sm"><div class="card-header bg-warning">Mutasi</div><div class="card-body">
                <form method="get" class="row g-2 mb-3 bg-light p-2"><input type="hidden" name="tab" value="mutasi">
                    <div class="col-6"><select name="mode" class="form-select" onchange="this.form.submit()"><option value="kobong" <?= ($_GET['mode']??'')=='kobong'?'selected':'' ?>>Pindah Kobong</option><option value="kelas" <?= ($_GET['mode']??'')=='kelas'?'selected':'' ?>>Naik Kelas</option></select></div>
                    <div class="col-6"><select name="sumber" class="form-select" onchange="this.form.submit()"><option value="">- Pilih Asal -</option><?php if(($_GET['mode']??'kobong')=='kobong'){ mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){ $s=($_GET['sumber']??'')==$k['id']?'selected':''; echo "<option value='$k[id]' $s>$k[nama_kobong]</option>"; } } else { $qkl=mysqli_query($conn,"SELECT DISTINCT kelas FROM santri WHERE status='aktif' ORDER BY kelas"); while($kl=mysqli_fetch_array($qkl)){ $s=($_GET['sumber']??'')==$kl['kelas']?'selected':''; echo "<option value='$kl[kelas]' $s>$kl[kelas]</option>"; } } ?></select></div>
                </form>
                <?php if(isset($_GET['sumber']) && $_GET['sumber']!=''): ?>
                <form method="post"><input type="hidden" name="tipe_mutasi" value="<?= $_GET['mode']??'kobong' ?>">
                <div class="table-responsive" style="max-height:300px;"><table class="table table-sm"><thead><tr><th width="30"><input type="checkbox" onclick="toggle(this)"></th><th>Nama</th></tr></thead><tbody>
                <?php $fil=$_GET['sumber']; $col=($_GET['mode']??'kobong')=='kobong'?'kobong_id':'kelas'; $qs=mysqli_query($conn,"SELECT * FROM santri WHERE $col='$fil' AND status='aktif'"); while($s=mysqli_fetch_array($qs)){ echo "<tr><td><input type='checkbox' name='ids[]' value='$s[id]'></td><td>$s[nama_santri]</td></tr>"; } ?>
                </tbody></table></div>
                <div class="input-group mt-2"><?php if(($_GET['mode']??'kobong')=='kobong'){ echo "<select name='target' class='form-select' required><option value=''>- Tujuan -</option>"; mysqli_data_seek($qk,0); while($k=mysqli_fetch_array($qk)){ echo "<option value='$k[id]'>$k[nama_kobong]</option>"; } echo "</select>"; } else { echo "<input type='text' name='target' class='form-control' placeholder='Kelas Baru' required>"; } ?><button type="submit" name="proses_mutasi" class="btn btn-primary">Simpan</button></div></form><?php endif; ?></div></div>

            <?php elseif($_GET['tab']=='lulus'): ?>
                <div class="card shadow-sm"><div class="card-header bg-danger text-white">Kelulusan</div><div class="card-body">
                <form method="get" class="d-flex gap-2 mb-3"><input type="hidden" name="tab" value="lulus"><select name="kelas_akhir" class="form-select"><?php $qkl=mysqli_query($conn,"SELECT DISTINCT kelas FROM santri WHERE status='aktif'"); while($kl=mysqli_fetch_array($qkl)){ echo "<option value='$kl[kelas]'>$kl[kelas]</option>"; } ?></select><button class="btn btn-dark">Cek</button></form>
                <?php if(isset($_GET['kelas_akhir'])): ?><form method="post"><div class="table-responsive"><table class="table table-bordered" style="min-width:400px;"><thead><tr><th>Nama</th><th>Aksi</th></tr></thead><tbody>
                <?php $qa=mysqli_query($conn,"SELECT * FROM santri WHERE kelas='$_GET[kelas_akhir]' AND status='aktif'"); while($sa=mysqli_fetch_array($qa)){ ?>
                <tr><td><input type="hidden" name="ids[]" value="<?= $sa['id'] ?>"><?= $sa['nama_santri'] ?></td><td><label class="me-2"><input type="radio" name="aksi[<?= $sa['id'] ?>]" value="lanjut" checked> Lanjut</label><label class="text-danger"><input type="radio" name="aksi[<?= $sa['id'] ?>]" value="lulus"> Lulus</label></td></tr><?php } ?></tbody></table></div><button type="submit" name="proses_lulus" class="btn btn-primary w-100">Proses</button></form><?php endif; ?></div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
function filterAkun() { var i=document.getElementById("cariAkun"), f=i.value.toUpperCase(), t=document.getElementById("tabelAkun"), tr=t.getElementsByTagName("tr"); for(var j=0;j<tr.length;j++){ var td=tr[j].getElementsByTagName("td")[0]; if(td){ tr[j].style.display=td.innerText.toUpperCase().indexOf(f)>-1?"":"none"; } } }
function cekRole() { var r=document.getElementById("roleSelect").value; document.getElementById("boxKobong").style.display=(r==='pengurus')?'block':'none'; }
function toggle(s) { var c=document.getElementsByName('ids[]'); for(var i=0;i<c.length;i++){ c[i].checked=s.checked; } }
cekRole(); 
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>