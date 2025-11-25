<?php
session_start();
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}
include "../config/db.php";

$id = (int)$_GET['id'];
$data = mysqli_query($conn, "SELECT a.*, p.nama, p.email FROM antrian a LEFT JOIN pengguna p ON a.id_pengguna = p.id_pengguna WHERE a.id_antrian = $id");
if (mysqli_num_rows($data) == 0) { echo "Data tidak ditemukan"; exit; }
$row = mysqli_fetch_assoc($data);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Antrian - H-Deeja</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style-admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <i class="fas fa-bars menu-toggle" id="menuToggle"></i>
        <h2>Status Antrian Pasien</h2>
        <i class="fas fa-bell" style="font-size:22px;"></i>
    </div>

    <div class="page-title">Detail & Status Antrian</div>

    <div style="max-width:700px;margin:40px auto;background:white;padding:35px;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,0.1);">
        <div style="text-align:center;margin-bottom:30px;">
            <i class="fas fa-user-circle" style="font-size:80px;color:#3498db;"></i>
            <h3 style="margin:15px 0 5px;color:#2c3e50;"><?= htmlspecialchars($row['nama'] ?: 'Nama Tidak Tersedia') ?></h3>
            <p style="color:#7f8c8d;"><?= htmlspecialchars($row['email']) ?></p>
        </div>

        <div style="background:#f8f9fa;padding:20px;border-radius:12px;margin-bottom:25px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                <span style="color:#555;font-weight:600;">No. Antrian</span>
                <strong>A<?= str_pad($row['id_antrian'], 4, '0', STR_PAD_LEFT) ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:12px;">
                <span style="color:#555;font-weight:600;">Tanggal Daftar</span>
                <strong><?= date('d/m/Y H:i', strtotime($row['waktuDaftar'])) ?> WIB</strong>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span style="color:#555;font-weight:600;">Status Saat Ini</span>
                <strong style="color:<?= is_null($row['id_konseling']) ? '#e67e22' : '#27ae60' ?>;font-size:18px;">
                    <?= is_null($row['id_konseling']) ? 'Menunggu Konseling' : 'Sudah Konseling' ?>
                </strong>
            </div>
        </div>

        <?php if (is_null($row['id_konseling'])): ?>
        <button onclick="Swal.fire({
            title: 'Belum Ada Konseling',
            text: 'Pasien ini masih menunggu jadwal konseling.',
            icon: 'info',
            confirmButtonText: 'OK'
        })" style="width:100%;padding:16px;background:#e67e22;color:white;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;">
            <i class="fas fa-clock"></i> Menunggu Jadwal Konseling
        </button>
        <?php else: ?>
        <button style="width:100%;padding:16px;background:#27ae60;color:white;border:none;border-radius:12px;font-size:16px;font-weight:600;">
            <i class="fas fa-check-circle"></i> Sudah Melakukan Konseling
        </button>
        <?php endif; ?>

        <a href="kelola_data.php" style="display:block;margin-top:20px;padding:14px;background:#95a5a6;color:white;text-align:center;border-radius:12px;text-decoration:none;font-weight:600;">
            Kembali ke Daftar Antrian
        </a>
    </div>
</div>

<script>
    document.getElementById('menuToggle').onclick = () => {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('overlay').classList.toggle('active');
    };
    document.getElementById('overlay')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.remove('active');
        document.getElementById('overlay').classList.remove('active');
    });
</script>
</body>
</html>