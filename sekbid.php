<?php
session_start();
include 'koneksi.php';
if(!in_array($_SESSION['role'], ['rois','sekretaris','kurikulum','ubudiah','kebersihan','peralatan','keamanan'])) header("location:index.php");
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Sekbid</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style> :root { --main: #0f5132; } .bg-islamic { background: linear-gradient(135deg, var(--main), #198754); color: white; } </style>
</head>
<body>
<div class="bg-islamic p-5 text-center mb-4">
    <h2 class="fw-bold mt-2">BAGIAN <?= strtoupper($role) ?></h2>
    <a href="profil.php" class="btn btn-warning btn-sm">Profil</a> <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
</div>
<div class="container text-center">
    <div class="alert alert-info">Fitur sedang dalam pengembangan.</div>
</div>
</body>
</html>