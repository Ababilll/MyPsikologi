<?php
session_start();
// Pastikan path ke file koneksi database sudah benar
include "../config/db.php"; 

// --- Cek Status Login (Opsional, tergantung alur Anda) ---
if (!isset($_SESSION['login_status']) || $_SESSION['login_status'] !== true) {
    // header("Location: ../auth/login.php");
    // exit();
}
// Ambil username dari sesi
$username = $_SESSION['username'] ?? 'Pengguna'; 

// --- PERBAIKAN DI SINI: Variabel ini harus didefinisikan di sini ---
// Ambil ID user yang login dari sesi
// PASTIKAN KUNCI SESI ANDA SAAT LOGIN ADALAH 'id_pengguna'
$id_pengguna_login = $_SESSION['user_id'] ?? null;

// --- 1. Ambil ID Jadwal dari URL ---
$id_jadwal = $_GET['id'] ?? null;
$jadwal_detail = null;

if ($id_jadwal) {
    // --- 2. FUNGSI UNTUK MENGAMBIL DETAIL JADWAL YANG DIPILIH ---
    function fetch_schedule_detail($conn, $id) {
        // Gabungkan tabel jadwal dan psikolog untuk mendapatkan detail lengkap
        $sql = "SELECT 
                    j.id_jadwal,
                    j.tanggal, 
                    j.waktu_mulai, 
                    j.waktu_selesai, 
                    j.kuota,
                    j.terisi,
                    p.nama AS nama_psikolog,
                    p.spesialisasi
                FROM jadwal j
                JOIN psikolog p ON j.id_psikolog = p.id_psikolog
                WHERE j.id_jadwal = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $detail = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // Cek Kuota
        if ($detail && ($detail['kuota'] - $detail['terisi']) > 0) {
            return $detail;
        }
        return null; // Jadwal tidak ditemukan atau sudah penuh
    }

    $jadwal_detail = fetch_schedule_detail($conn, $id_jadwal);
}

// Jika jadwal tidak valid atau tidak ada, arahkan kembali
if (!$jadwal_detail) {
    // header("Location: jadwal.php?status=invalid_jadwal");
    // exit();
    // Untuk pengembangan, kita tampilkan pesan error:
    $error_message = "Jadwal tidak valid atau slot sudah penuh. Silakan kembali ke halaman jadwal.";
}


// --- 3. PROSES SUBMIT FORM PENDAFTARAN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_daftar']) && $jadwal_detail) {
    
    // Ambil data form
    $nama_lengkap = $_POST['nama_lengkap'];
    $nomor_telepon = $_POST['nomor_telepon'];
    
    // Sanitasi data
    $nama_lengkap = mysqli_real_escape_string($conn, $nama_lengkap);
    $nomor_telepon = mysqli_real_escape_string($conn, $nomor_telepon);
    
    // Validasi 1: Kelengkapan data form
    if (empty($nama_lengkap) || empty($nomor_telepon)) {
        $submission_message = "Harap lengkapi semua data.";
        $is_success = false;
        
    // --- PERBAIKAN VALIDASI ID PENGGUNA ---
    } elseif (!is_numeric($id_pengguna_login) || $id_pengguna_login <= 0) {
        $submission_message = "Pendaftaran gagal. Anda harus login dengan akun yang valid.";
        $is_success = false;

    } else {
        
        // ID pengguna sudah valid, lanjutkan transaksi.
        $id_jadwal = $jadwal_detail['id_jadwal'];
        
        // --- Transaksi Database ---
        
        // 1. Masukkan ke tabel pemesanan
        // PASTIKAN kolom id_pengguna dan status_pemesanan digunakan
        $insert_query = "INSERT INTO pemesanan (id_jadwal, id_pengguna, nama_lengkap, nomor_telepon, status_pemesanan) 
                         VALUES (?, ?, ?, ?, 'Terdaftar')";
                         
        $stmt_insert = mysqli_prepare($conn, $insert_query);
        
        if ($stmt_insert === false) {
             // Tampilkan error SQL jika query gagal disiapkan
             die("SQL Prepare Gagal: " . mysqli_error($conn));
        }

        // --- PERBAIKAN BIND PARAMETER ---
        // Binding: id_jadwal, id_pengguna_login, nama_lengkap, nomor_telepon
        // Tipe: i, i, s, s
        mysqli_stmt_bind_param($stmt_insert, "iiss", $id_jadwal, $id_pengguna_login, $nama_lengkap, $nomor_telepon);
        $insert_ok = mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);
        
        if ($insert_ok) {
            // 2. Update kolom 'terisi' di tabel jadwal
            $update_query = "UPDATE jadwal SET terisi = terisi + 1 WHERE id_jadwal = ? AND (kuota - terisi) > 0";
            $stmt_update = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt_update, "i", $id_jadwal);
            $update_ok = mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
            
            if ($update_ok) {
                $submission_message = "Pendaftaran berhasil! Silakan tunggu konfirmasi selanjutnya.";
                $is_success = true;
                // Redirect ke halaman sukses/riwayat
                // header("Location: success.php?pesan=berhasil");
                // exit();
            } else {
                $submission_message = "Gagal memperbarui kuota jadwal. Mungkin slot sudah penuh saat Anda mendaftar.";
                $is_success = false;
            }
        } else {
             // Pesan error database yang lebih informatif
             $db_error = mysqli_error($conn);
             $submission_message = "Pendaftaran gagal dilakukan karena masalah database. Detail Error: " . htmlspecialchars($db_error);
             $is_success = false;
        }
    }
}
// ...

// --- Persiapan Data untuk Tampilan ---
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
        /* Variabel Warna */
        :root {
            --primary-blue: #4a90e2; 
            --dark-blue: #1b2a49; 
            --background-page: #f0f7fb; 
            --card-bg: white;
            --header-bg: #2c3e50; /* Warna biru gelap untuk header */
            --spacing: 20px;
        }

        /* CSS umum */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-page); 
            min-height: 100vh;
        }

        /* --- HEADER IMAGE & TEXT --- */
        .header-section {
            background-image: url('../img/klinik.jpeg'); /* Ganti dengan gambar latar belakang yang sesuai */
            background-color: var(--header-bg);
            background-size: cover;
            background-position: center;
            height: 250px; 
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            padding-top: 50px; /* Ruang untuk navbar */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            text-align: center;
            position: relative;
            z-index: 10;
        }

        .header-content h1 {
            font-size: 24px;
            margin: 10px 0 5px 0;
            font-weight: 700;
        }

        .header-content .logo-placeholder {
            width: 70px;
            height: 70px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            color: var(--dark-blue);
            font-weight: bold;
            font-size: 14px;
        }
        
        /* --- NAV BAR MINI --- */
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
        .navbar-mini span { font-size: 28px; cursor: pointer; }
        .navbar-mini .logo-text { font-weight: 500; font-size: 14px; }

        /* --- FORM CONTAINER --- */
        .form-container {
            max-width: 500px;
            margin: -80px auto 40px; /* Margin negatif untuk efek overlay */
            position: relative;
            z-index: 20;
            padding: 0 var(--spacing);
        }

        .form-card {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .form-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark-blue);
            margin-bottom: 5px;
        }
        .form-subtitle {
            font-size: 14px;
            color: #777;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-card label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-blue);
            font-size: 15px;
        }

        .styled-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: var(--background-page);
            box-sizing: border-box;
            font-size: 15px;
            color: var(--dark-blue);
        }
        .styled-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }
        /* Style untuk input yang readonly */
        .styled-input:disabled, .styled-input[readonly] {
            background-color: #e9ecef;
            color: #555;
            cursor: default;
        }

        .btn-daftar {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background-color: var(--primary-blue);
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.4);
            margin-top: 10px;
        }
        .btn-daftar:hover { background-color: #3b74b6; }

        /* Pesan notifikasi */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    
    <header class="header-section">
        <div class="navbar-mini">
            <a href="jadwal.php" style="color: white; text-decoration: none;"><span class="material-icons">arrow_back</span></a>
            <span class="logo-text">H-Deeja Psychology Center</span>
            <span class="material-icons">menu</span> 
        </div>

        <div class="header-content">
            <div class="logo-placeholder">Logo</div>
            <h1>H-Deeja Psychology Center</h1>
        </div>
    </header>

    <main class="form-container">
        
        <section class="form-card">
            <h2 class="form-title">Form Pendaftaran</h2>
            <p class="form-subtitle">Isi data berikut untuk mendaftar konseling</p>

            <?php 
            // Tampilkan pesan error jika jadwal tidak valid
            if (isset($error_message)): ?>
                <div class="alert alert-danger" style="text-align: center;">
                    <?= htmlspecialchars($error_message) ?>
                    <div style="margin-top: 10px;">
                        <a href="jadwal.php" style="color: #721c24; font-weight: 700;">&larr; Kembali ke Jadwal</a>
                    </div>
                </div>
            <?php 
            // Tampilkan pesan submit form
            elseif (isset($submission_message)): ?>
                <div class="alert <?= $is_success ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($submission_message) ?>
                    <?php if ($is_success): ?>
                        <div style="margin-top: 5px;">
                            <a href="riwayat.php">Lihat riwayat pendaftaran Anda.</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($jadwal_detail && !isset($submission_message) || (isset($submission_message) && !$is_success)): ?>
            
            <form action="daftar.php?id=<?= $id_jadwal ?>" method="POST">
                
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="nama_lengkap" 
                        class="styled-input" 
                        name="nama_lengkap" 
                        placeholder="Masukkan nama lengkap Anda"
                        required
                        value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="nomor_telepon">Nomor Telepon</label>
                    <input 
                        type="tel" 
                        id="nomor_telepon" 
                        class="styled-input" 
                        name="nomor_telepon" 
                        placeholder="Contoh: 0812xxxxxxxx"
                        required
                        value="<?= htmlspecialchars($_POST['nomor_telepon'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="psikolog_tim">Pilih Tim Psikolog</label>
                    <input 
                        type="text" 
                        id="psikolog_tim" 
                        class="styled-input" 
                        value="<?= htmlspecialchars($psikolog_display) ?>"
                        readonly
                    >
                    <input type="hidden" name="id_jadwal" value="<?= $id_jadwal ?>">
                </div>
                
                <div class="form-group">
                    <label for="tanggal_waktu">Tanggal & Waktu Konseling</label>
                    <input 
                        type="text" 
                        id="tanggal_waktu" 
                        class="styled-input" 
                        value="<?= htmlspecialchars($tanggal_waktu_display) ?>"
                        readonly
                    >
                </div>

                <button type="submit" name="submit_daftar" class="btn-daftar">Daftar</button>
            </form>
            
            <?php endif; // End if $jadwal_detail ?>

        </section>

    </main>

</body>
</html>