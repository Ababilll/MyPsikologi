<?php
session_start();
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include "../config/db.php";

// Ambil semua psikolog untuk dropdown
$psikolog_query = mysqli_query($conn, "SELECT id_psikolog, nama FROM psikolog ORDER BY nama ASC");

// Proses simpan
$alert = "";
if (isset($_POST['simpan'])) {
    $id_psikolog    = mysqli_real_escape_string($conn, $_POST['id_psikolog']);
    $tanggal        = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $waktu_mulai    = mysqli_real_escape_string($conn, $_POST['waktu_mulai']);
    $waktu_selesai  = mysqli_real_escape_string($conn, $_POST['waktu_selesai']);
    $kuota          = (int)$_POST['kuota'];

    // Validasi sederhana
    if (empty($id_psikolog) || empty($tanggal) || empty($waktu_mulai) || empty($waktu_selesai) || $kuota < 1) {
        $alert = "<script>
                    Swal.fire('Gagal!', 'Semua field harus diisi dengan benar!', 'error');
                  </script>";
    } elseif ($waktu_mulai >= $waktu_selesai) {
        $alert = "<script>
                    Swal.fire('Gagal!', 'Waktu selesai harus lebih besar dari waktu mulai!', 'warning');
                  </script>";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO jadwal 
                    (id_psikolog, tanggal, waktu_mulai, waktu_selesai, kuota) 
                    VALUES ('$id_psikolog', '$tanggal', '$waktu_mulai', '$waktu_selesai', '$kuota')");

        if ($insert) {
            $alert = "<script>
                        Swal.fire('Berhasil!', 'Jadwal baru telah ditambahkan', 'success')
                        .then(() => location.href='kelola_jadwal.php');
                      </script>";
        } else {
            $alert = "<script>
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menyimpan data.', 'error');
                      </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jadwal Baru - H-Deeja</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style-admin.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <i class="fas fa-bars menu-toggle" id="menuToggle"></i>
        <h2>Tambah Jadwal Baru</h2>
        <i class="fas fa-bell" style="font-size:22px;"></i>
    </div>

    <div class="page-title">Tambah Jadwal Praktik Psikolog</div>

    <div style="max-width:700px;margin:30px auto;background:white;padding:30px;border-radius:16px;box-shadow:0 8px 25px rgba(0,0,0,0.1);">
        <form method="POST" action="">
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#2c3e50;">Psikolog</label>
                <select name="id_psikolog" required style="width:100%;padding:14px;border-radius:12px;border:1px solid #ddd;font-size:15px;">
                    <option value="">-- Pilih Psikolog --</option>
                    <?php while($p = mysqli_fetch_assoc($psikolog_query)): ?>
                        <option value="<?= $p['id_psikolog'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#2c3e50;">Tanggal Praktik</label>
                <input type="date" name="tanggal" required style="width:100%;padding:14px;border-radius:12px;border:1px solid #ddd;font-size:15px;">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:20px;">
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#2c3e50;">Waktu Mulai</label>
                    <input type="time" name="waktu_mulai" required style="width:100%;padding:14px;border-radius:12px;border:1px solid #ddd;font-size:15px;">
                </div>
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#2c3e50;">Waktu Selesai</label>
                    <input type="time" name="waktu_selesai" required style="width:100%;padding:14px;border-radius:12px;border:1px solid #ddd;font-size:15px;">
                </div>
            </div>

            <div style="margin-bottom:30px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#2c3e50;">Kuota Pasien</label>
                <input type="number" name="kuota" min="1" max="50" value="10" required style="width:100%;padding:14px;border-radius:12px;border:1px solid #ddd;font-size:15px;">
                <small style="color:#7f8c8d;">Maksimal 50 pasien per sesi</small>
            </div>

            <div style="display:flex;gap:15px;">
                <button type="submit" name="simpan" style="flex:1;padding:16px;background:#3498db;color:white;border:none;border-radius:12px;font-size:16px;font-weight:600;cursor:pointer;">
                    Simpan Jadwal
                </button>
                <a href="kelola_jadwal.php" style="flex:1;padding:16px;background:#95a5a6;color:white;text-align:center;border-radius:12px;text-decoration:none;font-weight:600;">
                    Batal
                </a>
            </div>
        </form>
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

<?= $alert ?? '' ?>
</body>
</html>