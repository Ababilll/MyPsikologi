<?php
session_start();
include "../config/db.php";

// Cek login admin
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Ambil ID Konseling dari parameter URL
$id_konseling = $_GET['id'] ?? null;

if (!$id_konseling) {
    echo "<script>alert('ID Konseling tidak ditemukan!'); window.location='kelola_data.php';</script>";
    exit();
}

// Query mengambil detail pasien untuk ditampilkan di form
$sql = "SELECT k.id_konseling, pem.nama_lengkap, j.tanggal, a.id_antrian
        FROM konseling k
        JOIN pemesanan pem ON k.id_jadwal = pem.id_jadwal AND k.id_pengguna = pem.id_pengguna
        JOIN jadwal j ON k.id_jadwal = j.id_jadwal
        LEFT JOIN antrian a ON a.id_konseling = k.id_konseling
        WHERE k.id_konseling = '$id_konseling'";

$result = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Hasil Konseling - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="style-admin.css"> <style>
        .form-container { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 700px; margin: 20px auto; }
        .info-pasien { background: #e0f2fe; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #1b2a49; }
        textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; resize: vertical; }
        .btn-submit { background: #4a90e2; color: white; border: none; padding: 12px 25px; border-radius: 25px; cursor: pointer; font-weight: 600; width: 100%; transition: 0.3s; }
        .btn-submit:hover { background: #357abd; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <h2>Input Hasil Konseling</h2>
        </div>

        <div class="form-container">
            <div class="info-pasien">
                <p><strong>Nama Pasien:</strong> <?= htmlspecialchars($data['nama_lengkap']) ?></p>
                <p><strong>Tanggal Sesi:</strong> <?= date('d F Y', strtotime($data['tanggal'])) ?></p>
                <p><strong>Nomor Antrian:</strong> A-<?= str_pad($data['id_antrian'], 3, '0', STR_PAD_LEFT) ?></p>
            </div>

            <form action="proses_hasil.php" method="POST">
                <input type="hidden" name="id_konseling" value="<?= $id_konseling ?>">
                
                <div class="form-group">
                    <label for="catatan">Catatan Psikolog:</label>
                    <textarea name="catatan" id="catatan" rows="6" placeholder="Masukkan ringkasan sesi konseling..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="rekomendasi">Rekomendasi / Saran:</label>
                    <textarea name="rekomendasi" id="rekomendasi" rows="4" placeholder="Masukkan langkah selanjutnya untuk pasien..." required></textarea>
                </div>

                <button type="submit" name="simpan_hasil" class="btn-submit">
                    <i class="fas fa-save"></i> Simpan Hasil Konseling
                </button>
            </form>
        </div>
    </div>
</body>
</html>