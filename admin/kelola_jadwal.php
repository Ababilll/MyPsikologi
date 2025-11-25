<?php
session_start();
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include "../config/db.php";

// QUERY YANG 100% SESUAI DATABASE KAMU (hanya pakai kolom yang ADA)
$sql = "SELECT 
            j.id_jadwal,
            j.tanggal,
            j.waktu_mulai,
            j.waktu_selesai,
            j.kuota,
            j.id_psikolog,
            p.nama AS nama_psikolog
        FROM jadwal j
        LEFT JOIN psikolog p ON j.id_psikolog = p.id_psikolog
        ORDER BY j.tanggal DESC, j.waktu_mulai ASC";

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
    <title>Kelola Jadwal - H-Deeja</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style-admin.css">
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <i class="fas fa-bars menu-toggle" id="menuToggle"></i>
        <h2>Kelola Jadwal</h2>
        <i class="fas fa-bell" style="font-size:22px;"></i>
    </div>

    <div class="page-title">Kelola Jadwal Praktik Psikolog</div>

    <input type="text" class="search-box" placeholder="Cari nama psikolog atau tanggal..." id="searchInput">
    <a href="tambah_jadwal.php" class="btn-add">+ Tambah Jadwal Baru</a>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Psikolog</th>
                <th>Tanggal</th>
                <th>Jam Praktik</th>
                <th>Kuota</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php while($j = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><strong>JDW-<?= str_pad($j['id_jadwal'], 4, '0', STR_PAD_LEFT) ?></strong></td>
                <td><?= htmlspecialchars($j['nama_psikolog'] ?? 'Belum Ditentukan') ?></td>
                <td>
                    <?= date('d/m/Y', strtotime($j['tanggal'])) ?><br>
                    <small style="color:#95a5a6;">
                        <?= ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][date('w', strtotime($j['tanggal']))] ?>
                    </small>
                </td>
                <td>
                    <?= date('H:i', strtotime($j['waktu_mulai'])) ?> - 
                    <?= date('H:i', strtotime($j['waktu_selesai'])) ?> WIB
                </td>
                <td><strong><?= $j['kuota'] ?></strong> pasien</td>
                <td>
                    <button class="btn-edit" onclick="location.href='edit_jadwal.php?id=<?= $j['id_jadwal'] ?>'">Edit</button>
                    <button class="btn-delete" onclick="if(confirm('Yakin hapus jadwal ini?')) location.href='hapus_jadwal.php?id=<?= $j['id_jadwal'] ?>'">Hapus</button>
                </td>
            </tr>
            <?php endwhile; ?>

            <?php if(mysqli_num_rows($result) == 0): ?>
            <tr>
                <td colspan="6" style="text-align:center;padding:80px;color:#95a5a6;font-size:16px;">
                    Belum ada jadwal praktik psikolog
                </td>
            </tr>
            <?php endif; ?>
        </tbody quantity>
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

    document.getElementById('searchInput').addEventListener('keyup', function(){
        let val = this.value.toLowerCase();
        document.querySelectorAll('#tableBody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
        });
    });
</script>
</body>
</html>