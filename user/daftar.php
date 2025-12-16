<?php
session_start();
include "../config/db.php"; 

// Cek login
if (!isset($_SESSION['login_status']) || !isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_pengguna_login = $_SESSION['user_id'];
$id_jadwal = (int)($_GET['id'] ?? 0);

// Ambil detail jadwal
function fetch_schedule_detail($conn, $id) {
    $sql = "SELECT j.id_jadwal, j.tanggal, j.waktu_mulai, j.waktu_selesai, j.kuota, j.terisi,
                   p.nama AS nama_psikolog, p.spesialisasi
            FROM jadwal j
            JOIN psikolog p ON j.id_psikolog = p.id_psikolog
            WHERE j.id_jadwal = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $detail = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($detail && ($detail['kuota'] - $detail['terisi']) > 0) {
        return $detail;
    }
    return null;
}

$jadwal_detail = fetch_schedule_detail($conn, $id_jadwal);

if (!$jadwal_detail) {
    $error_message = "Jadwal tidak valid atau slot sudah penuh.";
}

// Proses pendaftaran
$submission_message = "";
$is_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_daftar']) && $jadwal_detail) {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $nomor_telepon = trim($_POST['nomor_telepon']);

    if (empty($nama_lengkap) || empty($nomor_telepon)) {
        $submission_message = "Harap lengkapi semua data.";
        $is_success = false;
    } else {
        // Reserve kuota dulu
        $reserve = mysqli_query($conn, "UPDATE jadwal SET terisi = terisi + 1 
                                        WHERE id_jadwal = $id_jadwal AND (kuota - terisi) > 0");
        
        if (mysqli_affected_rows($conn) == 0) {
            $submission_message = "Maaf, kuota sudah penuh saat Anda mendaftar.";
            $is_success = false;
        } else {
            // Insert ke pemesanan
            $nama_lengkap = mysqli_real_escape_string($conn, $nama_lengkap);
            $nomor_telepon = mysqli_real_escape_string($conn, $nomor_telepon);
            
            $insert = mysqli_query($conn, "INSERT INTO pemesanan 
                (id_jadwal, id_pengguna, nama_lengkap, nomor_telepon, status_pemesanan, tanggal_pesan) 
                VALUES ($id_jadwal, $id_pengguna_login, '$nama_lengkap', '$nomor_telepon', 'Terdaftar', NOW())");

            if ($insert) {
                $submission_message = "Pendaftaran berhasil! Menunggu konfirmasi dari admin.";
                $is_success = true;
            } else {
                // Rollback kuota jika insert gagal
                mysqli_query($conn, "UPDATE jadwal SET terisi = terisi - 1 WHERE id_jadwal = $id_jadwal");
                $submission_message = "Gagal mendaftar. Silakan coba lagi.";
                $is_success = false;
            }
        }
    }
}

// Tampilan
if ($jadwal_detail) {
    $psikolog_display = $jadwal_detail['nama_psikolog'] . ' - ' . $jadwal_detail['spesialisasi'];
    $tanggal_waktu_display = date('d F Y', strtotime($jadwal_detail['tanggal'])) . ' | ' . 
                             substr($jadwal_detail['waktu_mulai'], 0, 5) . ' - ' . 
                             substr($jadwal_detail['waktu_selesai'], 0, 5);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Konseling | H-Deeja</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #4a90e2; 
            --dark-blue: #1b2a49; 
            --background-page: #f0f7fb; 
            --card-bg: white;
            --spacing: 20px;
        }
        body {margin:0;font-family:'Segoe UI',sans-serif;background:var(--background-page);min-height:100vh;}
        .header-section {
            background-image: url('../img/klinik.jpeg');
            background-size: cover;
            background-position: center;
            height: 250px;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            padding-top: 50px;
        }
        .navbar-mini {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px var(--spacing);
            color: white;
        }
        .form-container {max-width:500px;margin:-80px auto 40px;position:relative;z-index:20;padding:0 var(--spacing);}
        .form-card {background:white;padding:30px;border-radius:20px;box-shadow:0 8px 20px rgba(0,0,0,0.15);}
        .form-title {font-size:22px;font-weight:700;color:var(--dark-blue);text-align:center;margin-bottom:5px;}
        .form-subtitle {font-size:14px;color:#777;text-align:center;margin-bottom:30px;}
        .form-group {margin-bottom:25px;}
        label {display:block;margin-bottom:8px;font-weight:600;color:var(--dark-blue);}
        .styled-input {
            width:100%;padding:12px 15px;border:1px solid #ccc;border-radius:10px;background:var(--background-page);
            font-size:15px;box-sizing:border-box;
        }
        .styled-input:focus {outline:none;border-color:var(--primary-blue);box-shadow:0 0 0 2px rgba(74,144,226,0.2);}
        .styled-input[readonly] {background:#e9ecef;color:#555;}
        .btn-daftar {
            width:100%;padding:14px;background:var(--primary-blue);color:white;border:none;border-radius:10px;
            font-size:18px;font-weight:bold;cursor:pointer;margin-top:10px;box-shadow:0 4px 8px rgba(74,144,226,0.4);
        }
        .btn-daftar:hover {background:#3b74b6;}
        .alert {padding:15px;margin-bottom:20px;border-radius:8px;font-weight:600;text-align:center;}
        .alert-success {background:#d4edda;color:#155724;border:1px solid #c3e6cb;}
        .alert-danger {background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
    </style>
</head>
<body>

<header class="header-section">
    <div class="navbar-mini">
        <a href="jadwal.php" style="color:white;text-decoration:none;"><span class="material-icons">arrow_back</span></a>
        <span>H-Deeja Psychology Center</span>
    </div>
    <div style="text-align:center;">
        <div style="width:70px;height:70px;background:white;border-radius:50%;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;color:var(--dark-blue);font-weight:bold;">Logo</div>
        <h1>H-Deeja Psychology Center</h1>
    </div>
</header>

<main class="form-container">
    <section class="form-card">
        <h2 class="form-title">Form Pendaftaran Konseling</h2>
        <p class="form-subtitle">Lengkapi data untuk mendaftar</p>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error_message) ?>
                <div style="margin-top:10px;"><a href="jadwal.php" style="color:#721c24;font-weight:700;">← Kembali</a></div>
            </div>
        <?php elseif (isset($submission_message)): ?>
            <div class="alert <?= $is_success ? 'alert-success' : 'alert-danger' ?>">
                <?= htmlspecialchars($submission_message) ?>
                <?php if ($is_success): ?>
                    <div style="margin-top:10px;"><a href="riwayat.php">Lihat Riwayat</a></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($jadwal_detail && (!isset($submission_message) || !$is_success)): ?>
        <div style="background:#e3f2fd;padding:15px;border-radius:10px;margin-bottom:25px;text-align:center;">
            <strong><?= htmlspecialchars($psikolog_display) ?></strong><br>
            <?= htmlspecialchars($tanggal_waktu_display) ?><br>
            Kuota tersisa: <strong><?= $jadwal_detail['kuota'] - $jadwal_detail['terisi'] ?></strong>
        </div>

        <form action="" method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="styled-input" required 
                       value="<?= htmlspecialchars($_SESSION['nama'] ?? $_POST['nama_lengkap'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="tel" name="nomor_telepon" class="styled-input" required 
                       placeholder="081234567890" value="<?= htmlspecialchars($_POST['nomor_telepon'] ?? '') ?>">
            </div>

            <button type="submit" name="submit_daftar" class="btn-daftar">Kirim Pendaftaran</button>
        </form>
        <?php endif; ?>
    </section>
</main>

</body>
</html>