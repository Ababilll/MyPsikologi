<?php
session_start();
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include "../config/db.php";

// QUERY YANG BENAR SESUAI DATABASE KAMU
$sql = "SELECT 
            a.id_antrian,
            a.waktuDaftar,
            a.id_konseling,
            p.nama,
            p.email
        FROM antrian a
        LEFT JOIN pengguna p ON a.id_pengguna = p.id_pengguna
        ORDER BY a.waktuDaftar DESC";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Pasien - H-Deeja</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style-admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <i class="fas fa-bars menu-toggle" id="menuToggle"></i>
        <h2>Kelola Data Pasien</h2>
        <i class="fas fa-bell" style="font-size:22px;"></i>
    </div>

    <div class="page-title">Data Antrian & Konseling</div>

    <input type="text" class="search-box" placeholder="Cari nama atau email pasien..." id="searchInput">
    <a href="tambah_antrian.php" class="btn-add">+ Tambah Data Pasien</a>

    <table>
        <thead>
            <tr>
                <th>No. Antrian</th>
                <th>Nama Pasien</th>
                <th>Email</th>
                <th>Tanggal Daftar</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><strong>A<?= str_pad($row['id_antrian'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                <td><?= htmlspecialchars($row['nama'] ?: '<i style="color:#999">Tidak ada nama</i>') ?></td>
                <td><?= htmlspecialchars($row['email'] ?: '-') ?></td>
                <td><?= $row['waktuDaftar'] ? date('d/m/Y H:i', strtotime($row['waktuDaftar'])) : '-' ?></td>
                <td>
    <?php if(is_null($row['id_konseling'])): ?>
        <span style="color:#e67e22;font-weight:600;padding:6px 12px;background:#fff3cd;border-radius:20px;font-size:13px;">
            Menunggu
        </span>
    <?php else: ?>
        <span style="color:#27ae60;font-weight:600;padding:6px 12px;background:#d4edda;border-radius:20px;font-size:13px;">
            Selesai
        </span>
    <?php endif; ?>
</td>
                <td>
                    <button class="btn-edit" onclick="location.href='edit_antrian.php?id=<?= $row['id_antrian'] ?>'">Edit</button>
                    <button class="btn-delete" onclick="if(confirm('Hapus data pasien ini?')) location.href='hapus_antrian.php?id=<?= $row['id_antrian'] ?>'">Hapus</button>
                </td>
            </tr>
            <?php endwhile; ?>

            <?php if(mysqli_num_rows($result) == 0): ?>
            <tr>
                <td colspan="6" style="text-align:center;padding:80px;color:#95a5a6;font-size:16px;">
                    Belum ada data antrian pasien
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
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

    // Live search
    document.getElementById('searchInput').addEventListener('keyup', function(){
        let val = this.value.toLowerCase();
        document.querySelectorAll('#tableBody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
        });
    });
</script>
</body>
</html>