<?php
session_start();
include 'koneksi.php';
if(!isset($_SESSION['id'])) header("location:index.php");
$id = $_SESSION['id'];
if(isset($_POST['update'])){
    $u=$_POST['username']; $p=$_POST['password'];
    if(mysqli_num_rows(mysqli_query($conn,"SELECT * FROM users WHERE username='$u' AND id!='$id'"))>0) echo "<script>alert('Username terpakai!');</script>";
    else { mysqli_query($conn,"UPDATE users SET username='$u', password='$p' WHERE id='$id'"); $_SESSION['username']=$u; echo "<script>alert('Sukses!');</script>"; }
}
$d = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$id'"));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> body { background: #f8f9fa; } </style>
</head>
<body>
<div class="container mt-5">
    <div class="card shadow border-0 col-md-6 mx-auto">
        <div class="card-header bg-white text-center py-3"><h4>Ubah Login</h4></div>
        <div class="card-body p-4">
            <form method="post">
                <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" value="<?= $d['username'] ?>" required></div>
                <div class="mb-4"><label>Password</label><input type="text" name="password" class="form-control" value="<?= $d['password'] ?>" required></div>
                <button type="submit" name="update" class="btn btn-success w-100">Simpan</button>
                <a href="javascript:history.back()" class="btn btn-outline-secondary w-100 mt-2">Kembali</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>