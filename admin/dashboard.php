<?php
session_start();

// GUNAKAN ROLE YANG SUDAH KITA SET DI LOGIN
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - H-Deeja Psychology Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style-admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <i class="fas fa-bars menu-toggle" id="menuToggle"></i>
        <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?> </h2>
        <i class="fas fa-bell" style="font-size:22px;"></i>
    </div>

    <!-- isi dashboard kamu tetap sama -->
    <div style="background:var(--primary);color:white;padding:25px;border-radius:16px;text-align:center;margin-bottom:25px;">
        <h1>Pengolahan Antrian</h1>
        <div class="filter-month" style="justify-content:center;margin:15px 0;">
            <span class="active">Terbaru</span>
            <span class="active">Riwayat</span>
        </div>
        <img src="https://via.placeholder.com/120x80/2c3e50/ffffff?text=Graph" style="border-radius:8px;">
    </div>

    <div style="display:flex;gap:15px;margin:25px 0;flex-wrap:wrap;">
        <div style="background:white;flex:1;min-width:120px;padding:20px;border-radius:14px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,.08);">
            <i class="fas fa-star" style="font-size:30px;color:#f39c12;"></i>
            <h2>95%</h2><p>Pasien puas</p>
        </div>
        <div style="background:white;flex:1;min-width:120px;padding:20px;border-radius:14px;text-align:center;box-shadow:0 4px 15px rgba(0,0,0,.08);">
            <i class="fas fa-user-clock" style="font-size:30px;color:#3498db;"></i>
            <h2>10</h2><p>Pasien rawat jalan</p>
        </div>
    </div>

    <a href="kelola_jadwal.php" class="btn-add">+ Tambah Jadwal Baru</a>

    <div style="background:#e3f2fd;height:120px;border-radius:14px;margin-bottom:15px;overflow:hidden;">
        <img src="https://via.placeholder.com/600x180/e3f2fd/666?text=Jadwal+Hari+Ini" style="width:100%;height:100%;object-fit:cover;">
    </div>
    <div style="background:#e3f2fd;height:120px;border-radius:14px;margin-bottom:15px;overflow:hidden;">
        <img src="https://via.placeholder.com/600x180/e3f2fd/666?text=Jadwal+Besok" style="width:100%;height:100%;object-fit:cover;">
    </div>

    <div style="display:flex;gap:15px;margin-top:30px;">
        <a href="kelola_data.php" style="flex:1;padding:16px;background:#f39c12;color:white;border:none;border-radius:30px;text-align:center;text-decoration:none;">Lihat Penilaian</a>
        <a href="#" style="flex:1;padding:16px;background:#e74c3c;color:white;border:none;border-radius:30px;text-align:center;text-decoration:none;">Rekam Medis</a>
    </div>
</div>

<script>
    document.getElementById('menuToggle').onclick = () => {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('overlay').classList.toggle('active');
    };
    document.getElementById('overlay').onclick = () => {
        document.getElementById('sidebar').classList.remove('active');
        document.getElementById('overlay').classList.remove('active');
    };
</script>
</body>
</html>