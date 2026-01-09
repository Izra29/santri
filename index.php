<?php
session_start();
include 'koneksi.php';

// Redirect jika sudah ada session aktif
if(isset($_SESSION['login_active'])){
    header("location:portal.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 1. Cek Identitas User
    $q_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($q_user) > 0) {
        $user = mysqli_fetch_assoc($q_user);
        
        // 2. Ambil Semua Role yang dimiliki user ini
        $uid = $user['id'];
        $q_roles = mysqli_query($conn, "SELECT * FROM user_roles WHERE user_id='$uid'");
        $roles_found = [];
        
        while($r = mysqli_fetch_assoc($q_roles)){
            $roles_found[] = $r;
        }

        if(count($roles_found) > 0){
            // Login Sukses
            $_SESSION['id'] = $user['id'];
            $_SESSION['nama'] = $user['nama_lengkap'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['available_roles'] = $roles_found; // Simpan semua jabatan di session
            $_SESSION['login_active'] = true;

            // 3. Logika Pengarahan
            if(count($roles_found) == 1){
                // Jika cuma punya 1 jabatan, langsung set active role & redirect
                $role_data = $roles_found[0];
                $_SESSION['role'] = $role_data['role'];
                $_SESSION['kobong_id'] = $role_data['kobong_id'];
                $_SESSION['santri_id'] = $role_data['santri_id'];
                
                // Redirect function
                if($role_data['role']=='superadmin') header("location:superadmin.php");
                elseif($role_data['role']=='admin') header("location:admin.php");
                elseif($role_data['role']=='orangtua') header("location:orangtua.php");
                elseif($role_data['role']=='pengurus') header("location:pengurus.php");
                else header("location:sekbid.php");
            } else {
                // Jika punya BANYAK jabatan, arahkan ke PORTAL
                header("location:portal.php");
            }
        } else {
            echo "<script>alert('Akun ditemukan tapi belum memiliki Jabatan/Role!');</script>";
        }
    } else {
        echo "<script>alert('Username atau Password Salah!');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Multi-Role</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; }
        body { background: linear-gradient(135deg, var(--main), #198754); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-login { width: 90%; max-width: 400px; border-radius: 15px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .btn-gold { background-color: var(--gold); color: white; font-weight: bold; }
    </style>
</head>
<body>
<div class="card card-login p-4">
    <div class="text-center mb-4">
        <i class="bi bi-moon-stars-fill text-success" style="font-size: 3rem;"></i>
        <h4 class="fw-bold text-success mt-2">Sistem Pesantren</h4>
        <p class="text-muted small">Satu Akun Untuk Semua</p>
    </div>
    <form method="post">
        <div class="mb-3"><div class="input-group"><span class="input-group-text"><i class="bi bi-person"></i></span><input type="text" name="username" class="form-control" placeholder="Username" required></div></div>
        <div class="mb-4"><div class="input-group"><span class="input-group-text"><i class="bi bi-key"></i></span><input type="password" name="password" class="form-control" placeholder="Password" required></div></div>
        <button type="submit" name="login" class="btn btn-gold w-100 py-2">MASUK</button>
    </form>
</div>
</body>
</html>