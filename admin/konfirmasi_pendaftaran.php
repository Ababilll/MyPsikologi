<?php
session_start();
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include "../config/db.php";

$alert = "";

$aksi = $_GET['aksi'] ?? '';
$id_pemesanan = (int)($_GET['id'] ?? 0);

// Proses konfirmasi
if ($aksi === 'konfirmasi' && $id_pemesanan > 0) {
    $q = mysqli_query($conn, "SELECT p.*, j.id_psikolog, j.tanggal, j.waktu_mulai, j.waktu_selesai 
                              FROM pemesanan p 
                              JOIN jadwal j ON p.id_jadwal = j.id_jadwal 
                              WHERE p.id_pemesanan = $id_pemesanan AND p.status_pemesanan = 'Terdaftar'");

    $p = mysqli_fetch_assoc($q);

    if ($p) {
        mysqli_begin_transaction($conn);
        try {
            // Insert ke antrian
            mysqli_query($conn, "INSERT INTO antrian (id_pengguna, waktuDaftar, id_konseling) 
                                 VALUES ('{$p['id_pengguna']}', NOW(), NULL)");
            $id_antrian = mysqli_insert_id($conn);

            // Insert ke konseling
            mysqli_query($conn, "INSERT INTO konseling (id_pengguna, id_psikolog, id_jadwal, status) 
                                 VALUES ('{$p['id_pengguna']}', '{$p['id_psikolog']}', '{$p['id_jadwal']}', 'Terjadwal')");
            $id_konseling = mysqli_insert_id($conn);

            // Update antrian
            mysqli_query($conn, "UPDATE antrian SET id_konseling = $id_konseling WHERE id_antrian = $id_antrian");

            // Update status pemesanan
            mysqli_query($conn, "UPDATE pemesanan SET status_pemesanan = 'Dikonfirmasi' WHERE id_pemesanan = $id_pemesanan");

            mysqli_commit($conn);

            $alert = "<script>
                Swal.fire('Berhasil!', 'Pendaftaran dikonfirmasi! Pasien sudah masuk antrian.', 'success')
                .then(() => location.href='konfirmasi_pendaftaran.php');
            </script>";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $alert = "<script>Swal.fire('Gagal', 'Terjadi kesalahan. Coba lagi.', 'error');</script>";
        }
    }
}

// Daftar pendaftaran yang menunggu konfirmasi
$pendaftar = mysqli_query($conn, "SELECT p.*, u.nama, u.email, ps.nama AS psikolog, j.tanggal, j.waktu_mulai, j.waktu_selesai
                                  FROM pemesanan p
                                  JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                                  JOIN jadwal j ON p.id_jadwal = j.id_jadwal
                                  JOIN psikolog ps ON j.id_psikolog = ps.id_psikolog
                                  WHERE p.status_pemesanan = 'Terdaftar'
                                  ORDER BY p.tanggal_pesan DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pendaftaran - H-Deeja</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style-admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
        <h2>Konfirmasi Pendaftaran</h2>
    </div>

    <div class="page-title">Pendaftaran Baru - Menunggu Konfirmasi</div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pasien</th>
                    <th>Psikolog & Jadwal</th>
                    <th>No. Telp</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($r = mysqli_fetch_assoc($pendaftar)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= htmlspecialchars($r['nama']) ?></strong><br><small><?= htmlspecialchars($r['email']) ?></small></td>
                    <td><strong><?= htmlspecialchars($r['psikolog']) ?></strong><br>
                        <?= date('d/m/Y', strtotime($r['tanggal'])) ?> | <?= substr($r['waktu_mulai'],0,5) ?> - <?= substr($r['waktu_selesai'],0,5) ?>
                    </td>
                    <td><?= htmlspecialchars($r['nomor_telepon']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($r['tanggal_pesan'])) ?></td>
                    <td>
                        <a href="?aksi=konfirmasi&id=<?= $r['id_pemesanan'] ?>" 
                           class="btn-confirm"
                           onclick="return confirm('Konfirmasi pendaftaran ini?')">
                           Konfirmasi
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($pendaftar) == 0): ?>
                <tr><td colspan="6" style="text-align:center;padding:50px;color:#777;">Belum ada pendaftaran baru</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const menuToggle = document.getElementById('menuToggle');
    const closeBtn = document.getElementById('closeSidebar');

    function toggleSidebar() {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    }

    menuToggle.addEventListener('click', toggleSidebar);
    if (closeBtn) closeBtn.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);
</script>

<?= $alert ?? '' ?>
</body>
</html>