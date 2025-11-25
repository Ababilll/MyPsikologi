<?php
session_start();
include "../config/db.php"; 

// --- Cek Status Login ---
if (!isset($_SESSION['login_status']) || $_SESSION['login_status'] !== true) {
    header("Location: ../auth/login.php");
    exit();
}

// Ambil username dari sesi
// Asumsi username adalah "Rilla" berdasarkan gambar
$username = $_SESSION['username'] ?? 'Pengguna'; 

// --- LOGIKA PEMBATASAN TANGGAL 1 MINGGU KE DEPAN ---

// Tanggal Hari Ini (Sebagai batas minimum)
$today = new DateTime('now', new DateTimeZone('Asia/Jakarta')); // Asumsi zona waktu Indonesia
$min_date = $today->format('Y-m-d'); 

// Tanggal 6 hari ke depan (Sebagai batas maksimum)
$max_date_obj = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->modify('+6 days');
$max_date = $max_date_obj->format('Y-m-d');

// --- 1. FUNGSI UNTUK MENGAMBIL DAFTAR TIM PSIKOLOG ---
function fetch_psikologs($conn) {
    // Menggunakan kolom 'nama' dan 'spesialisasi'
    $query = "SELECT id_psikolog, nama, spesialisasi FROM psikolog ORDER BY nama ASC";
    $result = mysqli_query($conn, $query);
    $psikologs = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $psikologs[] = $row;
        }
    }
    return $psikologs;
}

// --- 2. FUNGSI UNTUK MENGAMBIL JADWAL BERDASARKAN FILTER ---
function fetch_schedules($conn, $id_psikolog = null, $tanggal = null) {
    // Menggunakan kolom 'waktu_mulai', 'waktu_selesai', 'kuota', dan 'terisi'
    $sql = "SELECT 
                id_jadwal, 
                waktu_mulai, 
                waktu_selesai, 
                kuota, 
                terisi    
            FROM jadwal 
            WHERE 1=1";
            
    $params = [];
    $types = '';
    
    if ($id_psikolog) {
        $sql .= " AND id_psikolog = ?";
        $types .= "i";
        $params[] = $id_psikolog;
    }
    
    if ($tanggal) {
        $sql .= " AND tanggal = ?";
        $types .= "s";
        $params[] = $tanggal;
    }
    
    // Urutkan berdasarkan waktu mulai
    $sql .= " ORDER BY waktu_mulai ASC";

    $stmt = mysqli_prepare($conn, $sql);
    
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $schedules = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $schedules[] = $row;
        }
    }
    
    // Perlu menutup statement setelah selesai (Best practice)
    mysqli_stmt_close($stmt);
    
    return $schedules;
}

// --- LOGIKA UTAMA: Ambil data dari Database ---

$list_psikologs = fetch_psikologs($conn);

// Mengambil nilai filter dari URL (GET)
$selected_psikolog_id = $_GET['psikolog_tim'] ?? null;
$selected_tanggal = $_GET['tanggal_konseling'] ?? null;
$list_schedules = [];
$show_results = false;

// Proses form saat tombol "Tampilkan Jadwal" diklik (method GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tampilkan_jadwal'])) {
    
    // Validasi: Hanya ambil data jika kedua filter terisi
    if ($selected_psikolog_id && $selected_tanggal) {
        $show_results = true;
        $list_schedules = fetch_schedules($conn, $selected_psikolog_id, $selected_tanggal);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Konseling | H-Deeja</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        /* Variabel Warna */
        :root {
            --primary-blue: #4a90e2; /* Biru Utama */
            --secondary-blue: #e0f2fe; /* Biru Muda untuk Greeting */
            --background-page: #f0f7fb; /* Background page yang sangat pucat */
            --text-dark: #1b2a49; /* Teks gelap/judul */
            --text-medium: #555;
            --input-bg: #f8f8f8;
            --card-bg: white;
            --spacing: 20px;
        }

        /* CSS umum */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-page); /* Background page yang lebih pucat */
            min-height: 100vh;
        }
        
        /* Navbar Styling */
        .navbar {
            background-color: white;
            padding: 15px var(--spacing);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }
        .menu-icon { font-size: 28px; cursor: pointer; margin-right: 15px; color: var(--text-dark); }
        .logo { text-decoration: none; color: var(--text-dark); font-weight: bold; font-size: 18px; }
        .profile-icon { font-size: 28px; color: var(--text-dark); cursor: pointer; }

        /* --- SIDEBAR MENU STYLING (tidak diubah, untuk konsistensi) --- */
        .sidebar { /* ... (CSS Sidebar yang sama) ... */
            position: fixed; top: 0; left: 0; width: 280px; height: 100%; background-color: #f4f7f6; 
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2); z-index: 2000; transform: translateX(-100%);
            transition: transform 0.3s ease-in-out; display: flex; flex-direction: column;
        }
        .sidebar.open { transform: translateX(0); }
        .sidebar-header { display: flex; justify-content: flex-end; padding: 20px; border-bottom: 1px solid #ddd; }
        .close-icon { font-size: 28px; cursor: pointer; color: var(--primary-blue); }
        .sidebar-menu-list { list-style: none; padding: 0; flex-grow: 1; }
        .sidebar-menu-list li a { display: flex; align-items: center; padding: 15px 20px; text-decoration: none; color: #333; font-weight: 500; transition: background-color 0.2s; }
        .sidebar-menu-list li a:hover,
        .sidebar-menu-list li a.active { background-color: #d9eef7; color: var(--primary-blue); border-left: 5px solid var(--primary-blue); padding-left: 15px; }
        .sidebar-menu-list li a .material-icons { margin-right: 15px; font-size: 20px; }
        .sidebar-logout { display: flex; align-items: center; padding: 20px; text-decoration: none; color: #e74c3c; font-weight: 600; border-top: 1px solid #ddd; }
        .sidebar-logout .material-icons { margin-right: 15px; font-size: 20px; }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1999; display: none; }
        .overlay.visible { display: block; }
        /* --- END SIDEBAR STYLING --- */

        /* --- TATA LETAK UTAMA JADWAL --- */
        .schedule-container {
            padding: 0 var(--spacing) 40px var(--spacing);
            max-width: 500px; /* Batasi seperti di desain */
            margin: 0 auto;
        }

        /* Kotak Sapaan Mirip Balon Chat */
        .greeting-card {
            background-color: var(--secondary-blue);
            padding: 15px;
            border-radius: 15px;
            margin-top: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .greeting-title {
            background-color: white;
            color: var(--text-dark);
            font-size: 16px;
            font-weight: 700;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .greeting-text {
            font-size: 14px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0 5px; /* Sedikit padding agar tidak mepet */
        }

        /* Judul Halaman */
        h1.page-title {
            font-size: 26px;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0 0 5px 0;
        }
        .page-subtitle {
            font-size: 14px;
            color: var(--text-medium);
            margin-bottom: 25px;
        }

        /* Card Form Jadwal */
        .schedule-card {
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .schedule-card label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 15px;
        }

        /* Input/Dropdown Styling - Mengganti Div Dummy dengan Select/Input Asli */
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .styled-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: var(--input-bg);
            box-sizing: border-box;
            font-size: 15px;
            color: var(--text-dark);
            appearance: none; /* Hapus default style Select/Date */
            -webkit-appearance: none;
            -moz-appearance: none;
            cursor: pointer;
        }
        
        .styled-input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }

        /* Placeholder dan teks default */
        .styled-input option:first-of-type,
        .styled-input:not(:valid) {
            color: #999;
        }

        .input-icon {
            position: absolute;
            right: 15px;
            font-size: 24px;
            color: var(--primary-blue);
            pointer-events: none; /* Penting agar klik tetap tembus ke input/select */
        }

        /* Tombol Jadwal */
        .btn-schedule {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background-color: #5b9edc; /* Warna tombol */
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            box-shadow: 0 4px 8px rgba(91, 158, 220, 0.4);
            margin-top: 5px; /* Memberi jarak ke atas */
            margin-bottom: 30px;
        }
        .btn-schedule:hover {
            background-color: #4a7db7;
        }

        /* Tabel/List Jadwal Hasil */
        .schedule-results {
            display: grid;
            grid-template-columns: 1fr 1fr auto; /* Waktu | Status | Tombol */
            gap: 15px 10px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .schedule-results .header {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 15px;
        }

        .schedule-item {
            font-size: 14px;
            color: #333;
        }

        .status-badge {
            font-size: 14px; /* Dibuat sedikit lebih besar agar terlihat jelas */
            font-weight: 600;
            color: var(--text-dark);
            text-align: left; /* Sesuaikan agar tidak seperti badge*/
        }
        
        /* Tombol Daftar */
        .btn-daftar {
            padding: 8px 15px;
            border-radius: 8px;
            background-color: var(--primary-blue);
            color: white;
            font-weight: bold;
            text-decoration: none;
            font-size: 12px;
            text-align: center;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(74, 144, 226, 0.4);
        }
        .btn-daftar.disabled {
            background-color: #e5e7eb;
            color: #9ca3af;
            box-shadow: none;
            cursor: not-allowed;
        }
        .schedule-results > div:nth-child(3n+3) {
            text-align: right; /* Rata kanan untuk kolom tombol */
        }
        .schedule-results > div:nth-child(3n+2) {
            text-align: left; /* Rata kiri untuk kolom status */
        }

        @media (min-width: 768px) {
            .schedule-container {
                padding: 40px var(--spacing);
            }
        }
    </style>
</head>

<body>

    <nav class="sidebar" id="sidebarMenu">
        <div class="sidebar-header">
            <a href="home.php" class="logo" style="margin-right: auto; color: var(--primary-blue);">H-Deeja</a>
            <span class="material-icons close-icon" onclick="toggleMenu()">close</span>
        </div>
        <ul class="sidebar-menu-list">
            <li><a href="home.php"><span class="material-icons">dashboard</span> Dashboard</a></li>
            <li><a href="jadwal.php" class="active"><span class="material-icons">calendar_month</span> Jadwal</a></li>
            <li><a href="riwayat.php"><span class="material-icons">history</span> Riwayat Daftar</a></li>
            <li><a href="setting.php"><span class="material-icons">settings</span> Pengaturan</a></li>
        </ul>
        <a href="../auth/logout.php" class="sidebar-logout">
            <span class="material-icons">logout</span> Keluar
        </a>
    </nav>
    <div class="overlay" onclick="toggleMenu()"></div>
    
    <header class="navbar">
        <div class="navbar-left">
            <span class="material-icons menu-icon" onclick="toggleMenu()">menu</span> 
            <a href="home.php" class="logo">H-Deeja Psychology Center</a>
        </div>
    </header>

    <main class="schedule-container">
        
        <div class="greeting-card">
            <div class="greeting-title">Halo! <?= $username ?></div>
            <p class="greeting-text">Semoga harimu menyenangkan hari ini. Ingat, istirahat sejenak juga penting untuk kesehatan pikiran <span style="color:var(--primary-blue)">♥</span></p>
        </div>
        
        <h1 class="page-title">Jadwal Konseling</h1>
        <p class="page-subtitle">Pilih jadwal konseling Anda</p>

        <section class="schedule-card">
            
        <form action="" method="GET">
                <div class="form-group">
                    <label for="psikolog_tim">Pilih Psikolog</label>
                    <div class="input-wrapper">
                        <select id="psikolog_tim" class="styled-input" name="psikolog_tim" required>
                            <option value="" disabled <?php echo (!$selected_psikolog_id) ? 'selected' : ''; ?>>Pilih Psikolog...</option>
                            <?php foreach ($list_psikologs as $psikolog): ?>
                                <option 
                                    value="<?= htmlspecialchars($psikolog['id_psikolog']) ?>"
                                    <?= ($selected_psikolog_id == $psikolog['id_psikolog']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($psikolog['nama'] . ' - ' . $psikolog['spesialisasi']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="material-icons input-icon">chevron_right</span>
                    </div>
                </div>
                
                <div class="form-group">
    <label for="tanggal_konseling">Tanggal</label>
    <div class="input-wrapper">
         <input 
            type="date" 
            id="tanggal_konseling" 
            class="styled-input" 
            name="tanggal_konseling" 
            value="<?= htmlspecialchars($selected_tanggal ?? '') ?>"
            min="<?= $min_date ?>"
            max="<?= $max_date ?>"
            required
        >
        <span class="material-icons input-icon">calendar_today</span>
    </div>
</div>

                <button type="submit" name="tampilkan_jadwal" value="1" class="btn-schedule">Tampilkan Jadwal</button>
            </form>

            <?php if ($show_results): ?>
                <div class="schedule-results">
                    <div class="header">Waktu</div>
                    <div class="header" style="text-align: left;">Status</div>
                    <div class="header"></div> <?php if (empty($list_schedules)): ?>
                        <div style="grid-column: 1 / span 3; text-align: center; color: #777; padding: 15px;">
                            Tidak ada jadwal tersedia untuk kriteria ini.
                        </div>
                    <?php endif; ?>

                    
                    <?php foreach ($list_schedules as $schedule): 
                        // LOGIKA STATUS: Hitung sisa kuota
                        $sisa_kuota = $schedule['kuota'] - $schedule['terisi'];
                        $status_class = ($sisa_kuota > 0) ? 'tersedia' : 'penuh';
                        $status_text = ($sisa_kuota > 0) ? 'Tersedia (' . $sisa_kuota . ' slots)' : 'Penuh';
                        $button_class = ($sisa_kuota > 0) ? '' : 'disabled';
                        $button_link = ($sisa_kuota > 0) ? "daftar.php?id=" . $schedule['id_jadwal'] : '#';
                        $waktu_display = $schedule['waktu_mulai'] . ' - ' . $schedule['waktu_selesai'];
                        
                        try {
                            $start = new DateTime($schedule['waktu_mulai']);
                            $end = new DateTime($schedule['waktu_selesai']);
                            $waktu_display = $start->format('H:i') . ' - ' . $end->format('H:i');
                        } catch (Exception $e) {
                            // Fallback jika format waktu di DB tidak valid
                            $waktu_display = "Waktu Tidak Valid";
                        }
                    ?>
                        <div class="schedule-item"><?= htmlspecialchars($waktu_display) ?></div>
                        <div class="schedule-item"><span class="status-badge <?= $status_class ?>"><?= $status_text ?></span></div>
                        <div>
                            <a href="<?= $button_link ?>" class="btn-daftar <?= $button_class ?>">Daftar</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['tampilkan_jadwal'])): ?>
                 <div style="text-align: center; color: #e74c3c; padding: 15px; border-top: 1px solid #eee;">
                    Harap lengkapi pemilihan Psikolog dan Tanggal.
                </div>
            <?php endif; ?>
            
        </section>

    </main>

    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebarMenu');
            const overlay = document.querySelector('.overlay');

            sidebar.classList.toggle('open');
            overlay.classList.toggle('visible');
            
            // Mencegah scrolling body saat menu terbuka
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : 'auto';
        }

        // Membuat Input Tanggal lebih fungsional di perangkat mobile (saat diklik)
        document.addEventListener('DOMContentLoaded', (event) => {
            const dateInput = document.getElementById('tanggal_konseling');
            
            // Untuk memastikan ikon panah pada select Tim Psikolog kembali ke default (chevron_right)
            const selectPsikolog = document.getElementById('psikolog_tim');
            
            // Hapus placeholder jika ada, dan pastikan pengguna bisa memilih
            // Karena menggunakan required dan option disabled selected, ini cukup untuk validasi.
        });
    </script>

</body>
</html>