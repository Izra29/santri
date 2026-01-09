<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['login_active']) || !isset($_SESSION['available_roles'])){
    header("location:index.php");
    exit;
}

// Proses Pemilihan Role
if(isset($_GET['pilih_role_index'])){
    $index = $_GET['pilih_role_index'];
    $selected_role = $_SESSION['available_roles'][$index];
    
    // Set Session Aktif sesuai pilihan
    $_SESSION['role'] = $selected_role['role'];
    $_SESSION['kobong_id'] = $selected_role['kobong_id'];
    $_SESSION['santri_id'] = $selected_role['santri_id'];
    
    // Redirect
    if($selected_role['role']=='superadmin') header("location:superadmin.php");
    elseif($selected_role['role']=='admin') header("location:admin.php");
    elseif($selected_role['role']=='orangtua') header("location:orangtua.php");
    elseif($selected_role['role']=='pengurus') header("location:pengurus.php");
    else header("location:sekbid.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pilih Jabatan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --main: #0f5132; --gold: #d4ac0d; }
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-portal { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        .role-btn { transition: 0.3s; border: 1px solid #eee; }
        .role-btn:hover { background-color: #f8f9fa; transform: translateY(-3px); border-color: var(--main); }
    </style>
</head>
<body>

<div class="container p-3">
    <div class="card card-portal mx-auto">
        <div class="card-header bg-white text-center py-4 border-0">
            <h4 class="fw-bold text-success mb-1">Assalamu'alaikum,</h4>
            <h3 class="fw-bold text-dark"><?= $_SESSION['nama'] ?></h3>
            <p class="text-muted small">Silakan pilih akses jabatan untuk masuk:</p>
        </div>
        <div class="card-body p-4 pt-0">
            <div class="d-grid gap-3">
                <?php 
                foreach($_SESSION['available_roles'] as $index => $r): 
                    // Tentukan Label & Ikon
                    $label = strtoupper($r['role']);
                    $sub = "";
                    $icon = "bi-person-badge";
                    
                    if($r['role']=='pengurus'){
                        // Ambil nama kobong
                        $nm_k = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_kobong FROM kobong WHERE id='$r[kobong_id]'"));
                        $label = "PENGURUS KOBONG";
                        $sub = $nm_k['nama_kobong'] ?? 'Data Kobong Hilang';
                        $icon = "bi-house-door";
                    } elseif($r['role']=='admin'){
                        $label = "ADMIN KEUANGAN";
                        $icon = "bi-wallet2";
                    } elseif($r['role']=='superadmin'){
                        $label = "PIMPINAN";
                        $icon = "bi-buildings";
                    } elseif($r['role']=='orangtua'){
                        $label = "WALI SANTRI";
                        $icon = "bi-person-heart";
                    }
                ?>
                <a href="?pilih_role_index=<?= $index ?>" class="role-btn p-3 rounded text-decoration-none text-dark d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                        <i class="<?= $icon ?> fs-3"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-success"><?= $label ?></h6>
                        <?php if($sub): ?><small class="text-muted"><?= $sub ?></small><?php endif; ?>
                    </div>
                    <div class="ms-auto text-muted"><i class="bi bi-chevron-right"></i></div>
                </a>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-4">Logout</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>