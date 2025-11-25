<?php
session_start();
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}
include "../config/db.php";

$pengguna = mysqli_query($conn, "SELECT id_pengguna, nama, email FROM pengguna WHERE role != 'admin' ORDER BY nama ASC");

$alert = "";
if (isset($_POST['simpan'])) {
    $id_pengguna = mysqli_real_escape_string($conn, $_POST['id_pengguna']);
    $waktuDaftar = date('Y-m-d H:i:s');

    // Cek duplikat
    $cek = mysqli_query($conn, "SELECT id_antrian FROM antrian WHERE id_pengguna = '$id_pengguna' AND id_konseling IS NULL");
    if (mysqli_num_rows($cek) > 0) {
        $alert = "<script>Swal.fire('Gagal!', 'Pasien ini sudah ada di antrian!', 'warning');</script>";
    } else {
        // Sekarang boleh NULL → aman!
        $insert = mysqli_query($conn, "INSERT INTO antrian (id_pengguna, waktuDaftar, id_konseling) VALUES ('$id_pengguna', '$waktuDaftar', NULL)");

        if ($insert) {
            $alert = "<script>
                Swal.fire('Berhasil!', 'Pasien berhasil ditambahkan ke antrian', 'success')
                .then(() => location.href='kelola_data.php');
            </script>";
        } else {
            $alert = "<script>Swal.fire('Error!', 'Gagal: ".addslashes(mysqli_error($conn))."', 'error');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Antrian</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min-css">
    <link rel="stylesheet" href="style-admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <i class="fas fa-bars menu-toggle" id="menuToggle"></i>
        <h2>Tambah Antrian Baru</h2>
    </div>

    <div style="max-width:600px;margin:40px auto;background:white;padding:35px;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,0.12);">
        <form method="POST">
            <label style="font-weight:600;display:block;margin-bottom:10px;">Pilih Pasien</label>
            <select name="id_pengguna" required style="width:100%;padding:16px;border-radius:12px;border:1px solid #ddd;margin-bottom:20px;">
                <option value="">-- Pilih Pasien --</option>
                <?php while($u = mysqli_fetch_assoc($pengguna)): ?>
                    <option value="<?= $u['id_pengguna'] ?>"><?= htmlspecialchars($u['nama']) ?> (<?= $u['email'] ?>)</option>
                <?php endwhile; ?>
            </select>

            <div style="display:flex;gap:15px;">
                <button type="submit" name="simpan" style="flex:1;padding:16px;background:#3498db;color:white;border:none;border-radius:12px;font-weight:600;">
                    Tambahkan ke Antrian
                </button>
                <a href="kelola_data.php" style="flex:1;padding:16px;background:#95a5a6;color:white;text-align:center;border-radius:12px;text-decoration:none;font-weight:600;">
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
</script>
<?= $alert ?? '' ?>
</body>
</html>