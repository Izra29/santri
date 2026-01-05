<?php
session_start();
include 'koneksi.php';

if(isset($_SESSION['role'])){
    if($_SESSION['role'] == 'superadmin') header("location:superadmin.php");
    else if($_SESSION['role'] == 'admin') header("location:admin.php");
    else if($_SESSION['role'] == 'pengurus') header("location:pengurus.php");
    else if($_SESSION['role'] == 'orangtua') header("location:orangtua.php");
    else header("location:sekbid.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = $_POST['username']; $password = $_POST['password'];
    $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($q) > 0) {
        $d = mysqli_fetch_assoc($q);
        $_SESSION['id'] = $d['id']; $_SESSION['username'] = $username; $_SESSION['role'] = $d['role']; 
        $_SESSION['kobong_id'] = $d['kobong_id']; $_SESSION['santri_id'] = $d['santri_id']; 

        if ($d['role'] == "superadmin") header("location:superadmin.php");
        else if ($d['role'] == "admin") header("location:admin.php");
        else if ($d['role'] == "pengurus") header("location:pengurus.php");
        else if ($d['role'] == "orangtua") header("location:orangtua.php");
        else header("location:sekbid.php");
    } else { echo "<script>alert('Username/Password Salah!');</script>"; }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Sistem</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; }
        body { background: linear-gradient(135deg, var(--main), #198754); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-login { width: 90%; max-width: 400px; border-radius: 15px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2); } /* Lebar 90% agar aman di HP */
        .btn-gold { background-color: var(--gold); color: white; font-weight: bold; }
    </style>
</head>
<body>
<div class="card card-login p-4">
    <div class="text-center mb-4">
        <i class="bi bi-moon-stars-fill text-success" style="font-size: 3rem;"></i>
        <h4 class="fw-bold text-success mt-2">Sistem Keuangan</h4>
        <p class="text-muted small">Silakan login untuk melanjutkan</p>
    </div>
    <form method="post">
        <div class="mb-3"><label class="form-label">Username</label><div class="input-group"><span class="input-group-text"><i class="bi bi-person"></i></span><input type="text" name="username" class="form-control" required></div></div>
        <div class="mb-4"><label class="form-label">Password</label><div class="input-group"><span class="input-group-text"><i class="bi bi-key"></i></span><input type="password" name="password" class="form-control" required></div></div>
        <button type="submit" name="login" class="btn btn-gold w-100 py-2">MASUK</button>
    </form>
</div>
</body>
</html>