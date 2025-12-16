<?php
session_start();
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}
include "../config/db.php";

$alert = "";

$aksi = $_GET['aksi'] ?? '';
$id_pemesanan_param = $_GET['id'] ?? 0;

// Proses konfirmasi
if ($aksi === 'konfirmasi' && $id_pemesanan_param > 0) {
    $id_pemesanan = (int)$id_pemesanan_param;

    $q = mysqli_query($conn, "SELECT p.*, j.id_psikolog, j.tanggal, j.waktu_mulai, j.waktu_selesai 
                              FROM pemesanan p 
                              JOIN jadwal j ON p.id_jadwal = j.id_jadwal 
                              WHERE p.id_pemesanan = $id_pemesanan AND p.status_pemesanan = 'Terdaftar'");

    $p = mysqli_fetch_assoc($q);

    if ($p) {
        mysqli_begin_transaction($conn);
        try {
            // 1. Insert ke antrian
            mysqli_query($conn, "INSERT INTO antrian (id_pengguna, waktuDaftar, id_konseling) 
                                 VALUES ('{$p['id_pengguna']}', NOW(), NULL)");
            $id_antrian = mysqli_insert_id($conn);

            // 2. Buat record konseling
            mysqli_query($conn, "INSERT INTO konseling (id_pengguna, id_psikolog, id_jadwal, status) 
                                 VALUES ('{$p['id_pengguna']}', '{$p['id_psikolog']}', '{$p['id_jadwal']}', 'Terjadwal')");
            $id_konseling = mysqli_insert_id($conn);

            // 3. Update antrian dengan id_konseling
            mysqli_query($conn, "UPDATE antrian SET id_konseling = $id_konseling WHERE id_antrian = $id_antrian");

            // 4. Update status pemesanan
            mysqli_query($conn, "UPDATE pemesanan SET status_pemesanan = 'Dikonfirmasi' WHERE id_pemesanan = $id_pemesanan");

            // 5. Tambah kuota terisi
            mysqli_query($conn, "UPDATE jadwal SET terisi = terisi + 1 WHERE id_jadwal = {$p['id_jadwal']}");

            mysqli_commit($conn);

            $alert = "<script>
                Swal.fire('Berhasil!', 'Pendaftaran berhasil dikonfirmasi!<br>Pasien sudah masuk antrian.', 'success')
                .then(() => location.href='konfirmasi_pendaftaran.php');
            </script>";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $alert = "<script>Swal.fire('Gagal', 'Terjadi kesalahan sistem. Coba lagi nanti.', 'error');</script>";
        }
    } else {
        $alert = "<script>Swal.fire('Error', 'Data tidak ditemukan atau sudah dikonfirmasi!', 'warning');</script>";
    }
}

// Ambil semua pendaftaran yang masih "Terdaftar"
$pendaftar = mysqli_query($conn, "SELECT p.*, u.nama, u.email, ps.nama AS psikolog, 
                                         j.tanggal, j.waktu_mulai, j.waktu_selesai 
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
    <style>
        .badge-pending {background:#fff3cd;color:#856404;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;}
        .btn-confirm {background:#28a745;color:white;padding:10px 20px;border:none;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;}
        .btn-confirm:hover {background:#218838;}
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <i class="fas fa-bars menu-toggle" id="menuToggle"></i>
        <h2>Konfirmasi Pendaftaran Baru</h2>
    </div>

    <div class="page-title">Pendaftaran Online - Menunggu Konfirmasi</div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Pasien</th>
                    <th width="25%">Psikolog & Jadwal</th>
                    <th width="15%">No. Telp</th>
                    <th width="15%">Tanggal Daftar</th>
                    <th width="10%">Status</th>
                    <th width="10%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; while ($r = mysqli_fetch_assoc($pendaftar)): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <strong><?= htmlspecialchars($r['nama']) ?></strong><br>
                        <small><?= htmlspecialchars($r['email']) ?></small>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($r['psikolog']) ?></strong><br>
                        <?= date('d/m/Y', strtotime($r['tanggal')) ?> | 
                        <?= substr($r['waktu_mulai'], 0, 5) ?> - <?= substr($r['waktu_selesai'], 0, 5) ?>
                    </td>
                    <td><?= htmlspecialchars($r['nomor_telepon']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($r['tanggal_pesan'])) ?></td>
                    <td><span class="badge-pending">Menunggu</span></td>
                    <td>
                        <a href="?aksi=konfirmasi&id=<?= $r['id_pemesanan'] ?>" 
                           class="btn-confirm"
                           onclick="return confirm('Yakin konfirmasi pendaftaran ini?\nPasien akan langsung masuk antrian.')">
                           <i class="fas fa-check"></i> Konfirmasi
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php if (mysqli_num_rows($pendaftar) == 0): ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:60px 20px;color:#777;">
                        <i class="fas fa-check-circle" style="font-size:60px;color:#28a745;margin-bottom:20px;display:block;"></i>
                        <strong>Belum ada pendaftaran baru yang perlu dikonfirmasi</strong>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('menuToggle').onclick = () => {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('overlay')?.classList.toggle('active');
    };
</script>

<?= $alert ?? '' ?>
</body>
</html>