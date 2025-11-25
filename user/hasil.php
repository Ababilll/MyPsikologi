<?php
session_start();
// Pastikan path ke file koneksi database sudah benar
include "../config/db.php"; 

// --- 1. Cek Status Login dan Ambil ID Pengguna ---
$id_pengguna_login = $_SESSION['user_id'] ?? null; 
$username = $_SESSION['username'] ?? 'Pengguna'; 

if (!isset($_SESSION['login_status']) || $_SESSION['login_status'] !== true || !is_numeric($id_pengguna_login) || $id_pengguna_login <= 0) {
    header("Location: ../auth/login.php");
    exit();
}

// Ambil ID Konseling dari URL. Jika tidak ada, ambil hasil terbaru yang dimiliki pengguna.
$id_konseling_target = $_GET['id'] ?? null; 
$where_clause = "WHERE hk.id_pengguna = ?";

if (is_numeric($id_konseling_target) && $id_konseling_target > 0) {
    // Jika ID Konseling spesifik diminta
    $where_clause .= " AND hk.id_konseling = ?";
}

// --- 2. Query untuk Mengambil Data Hasil Konseling ---
function fetch_hasil_konseling($conn, $id_pengguna, $id_konseling_target) {
    
    // Query untuk mengambil Hasil Konseling, digabungkan dengan detail Konseling, Jadwal, Psikolog, dan Antrian.
    $sql = "SELECT 
                hk.catatan, 
                hk.rekomendasi,
                k.id_konseling,
                k.status AS status_konseling,
                p.nama AS nama_pasien, 
                ps.nama AS nama_psikolog, 
                j.tanggal, 
                j.waktu_mulai, 
                j.waktu_selesai,
                a.id_antrian
            FROM hasil_konseling hk
            JOIN konseling k ON hk.id_konseling = k.id_konseling
            JOIN pengguna p ON hk.id_pengguna = p.id_pengguna
            JOIN psikolog ps ON k.id_psikolog = ps.id_psikolog
            JOIN jadwal j ON k.id_jadwal = j.id_jadwal
            LEFT JOIN antrian a ON a.id_konseling = k.id_konseling
            WHERE hk.id_pengguna = ?";
            
    // Jika ID Konseling spesifik diminta
    if ($id_konseling_target) {
        $sql .= " AND hk.id_konseling = ?";
    }
    
    // Urutkan berdasarkan yang terbaru jika tidak ada ID spesifik
    $sql .= " ORDER BY j.tanggal DESC, j.waktu_mulai DESC LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);
    
    if ($id_konseling_target) {
        mysqli_stmt_bind_param($stmt, "ii", $id_pengguna, $id_konseling_target);
    } else {
        mysqli_stmt_bind_param($stmt, "i", $id_pengguna);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $hasil = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $hasil;
}

$hasil_konseling = fetch_hasil_konseling($conn, $id_pengguna_login, $id_konseling_target);

// Untuk Display "Halo! Rilla"
$display_name = ucwords(strtolower($_SESSION['username'] ?? 'Pengguna')); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Konseling | H-Deeja</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        /* Variabel Warna (Sama dengan riwayat.php/jadwal.php) */
        :root {
            --primary-blue: #4a90e2; 
            --secondary-blue: #e0f2fe; 
            --background-page: #f0f7fb; 
            --text-dark: #1b2a49; 
            --text-medium: #555;
            --card-bg: white;
            --card-bg-light: #e0f2fe; /* Warna kartu hasil yang lebih terang */
            --spacing: 20px;
        }

        /* CSS umum (Sama dengan jadwal.php) */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-page); 
            min-height: 100vh;
        }
        
        /* Navbar Styling (Sama dengan jadwal.php) */
        .navbar { background-color: white; padding: 15px var(--spacing); display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); position: sticky; top: 0; z-index: 1000; }
        .navbar-left { display: flex; align-items: center; }
        .menu-icon { font-size: 28px; cursor: pointer; margin-right: 15px; color: var(--text-dark); }
        .logo { text-decoration: none; color: var(--text-dark); font-weight: bold; font-size: 18px; }
        .profile-icon { font-size: 28px; color: var(--text-dark); cursor: pointer; }

        /* --- SIDEBAR MENU STYLING (Sama dengan jadwal.php) --- */
        .sidebar { position: fixed; top: 0; left: 0; width: 280px; height: 100%; background-color: #f4f7f6; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2); z-index: 2000; transform: translateX(-100%); transition: transform 0.3s ease-in-out; display: flex; flex-direction: column; }
        .sidebar.open { transform: translateX(0); }
        .sidebar-header { display: flex; justify-content: flex-end; padding: 20px; border-bottom: 1px solid #ddd; }
        .close-icon { font-size: 28px; cursor: pointer; color: var(--primary-blue); }
        .sidebar-menu-list { list-style: none; padding: 0; flex-grow: 1; }
        .sidebar-menu-list li a { display: flex; align-items: center; padding: 15px 20px; text-decoration: none; color: #333; font-weight: 500; transition: background-color 0.2s; }
        .sidebar-menu-list li a:hover, .sidebar-menu-list li a.active { background-color: #d9eef7; color: var(--primary-blue); border-left: 5px solid var(--primary-blue); padding-left: 15px; } 
        .sidebar-menu-list li a .material-icons { margin-right: 15px; font-size: 20px; }
        .sidebar-logout { display: flex; align-items: center; padding: 20px; text-decoration: none; color: #e74c3c; font-weight: 600; border-top: 1px solid #ddd; }
        .sidebar-logout .material-icons { margin-right: 15px; font-size: 20px; }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1999; display: none; }
        .overlay.visible { display: block; }
        /* --- END SIDEBAR STYLING --- */

        /* --- TATA LETAK UTAMA (Sama dengan jadwal.php) --- */
        .schedule-container { padding: 0 var(--spacing) 40px var(--spacing); max-width: 500px; margin: 0 auto; }
        .greeting-card { background-color: var(--secondary-blue); padding: 15px; border-radius: 15px; margin-top: 20px; margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); }
        .greeting-title { background-color: white; color: var(--text-dark); font-size: 16px; font-weight: 700; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 10px; }
        .greeting-text { font-size: 14px; color: #333; line-height: 1.5; margin: 0; padding: 0 5px; }
        h1.page-title { font-size: 26px; font-weight: 700; color: var(--text-dark); margin: 0 0 5px 0; }
        .page-subtitle { font-size: 14px; color: var(--text-medium); margin-bottom: 25px; }
        
        @media (min-width: 768px) {
            .schedule-container { padding: 40px var(--spacing); }
        }

        /* --- HASIL KONSELING CARD STYLES --- */
        .hasil-card {
            background-color: var(--card-bg-light); /* Warna biru muda */
            padding: 30px 25px;
            border-radius: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .hasil-detail {
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .hasil-detail label {
            font-weight: 400;
            color: var(--text-medium);
            display: inline-block;
            width: 120px; /* Lebar tetap untuk label */
        }

        .hasil-detail strong {
            font-weight: 600;
            color: var(--text-dark);
            margin-left: 5px;
        }

        /* Styling Khusus untuk Tanggal & Waktu */
        .hasil-detail .icon-row {
            display: flex;
            align-items: center;
            margin-top: 5px;
        }

        .hasil-detail .icon-row span.material-icons {
            font-size: 20px;
            margin-right: 8px;
            color: var(--primary-blue);
        }

        .status-badge {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: #28a745; /* Hijau untuk Terdaftar */
            margin-bottom: 20px;
        }

        .status-badge span.material-icons {
            font-size: 20px;
            margin-right: 8px;
        }

        /* Styling untuk Nomor Antrian */
        .nomor-antrian-display {
            font-size: 48px;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1;
            text-align: center;
            margin-top: 10px;
            margin-bottom: 30px;
        }

        .nomor-antrian-label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-medium);
            text-align: center;
            margin-top: 20px;
        }

        /* Styling untuk Catatan */
        .catatan-container {
            border-top: 1px solid #cce5ff; /* Garis pemisah */
            padding-top: 20px;
        }
        .catatan-container label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 10px;
            display: block;
        }
        .catatan-text {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            min-height: 150px;
            white-space: pre-wrap; /* Mempertahankan format baris */
            font-size: 14px;
            color: #333;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .no-data {
            text-align: center;
            padding: 40px 20px;
            background-color: var(--card-bg);
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            color: var(--text-medium);
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
            <li><a href="jadwal.php"><span class="material-icons">calendar_month</span> Jadwal</a></li>
            <li><a href="riwayat.php"><span class="material-icons">history</span> Riwayat Daftar</a></li> 
            <li><a href="hasil.php" class="active"><span class="material-icons">description</span> Hasil Konseling</a></li> 
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
        <div class="navbar-right">
             <a href="setting.php"><span class="material-icons profile-icon">person</span></a>
        </div>
    </header>

    <main class="schedule-container">
        
        <div class="greeting-card">
            <div class="greeting-title">Halo! <?= htmlspecialchars($display_name) ?></div>
            <p class="greeting-text">Semoga harimu menyenangkan hari ini. Ingat, istirahat sejenak juga penting untuk kesehatan pikiran <span style="color:var(--primary-blue)">♥</span></p>
        </div>
        
        <h1 class="page-title">Hasil Konseling</h1>
        <p class="page-subtitle">Rangkuman dan catatan sesi konseling Anda.</p>

        <?php if ($hasil_konseling): 
            // Ambil data
            $tgl_display = date('d F Y', strtotime($hasil_konseling['tanggal']));
            $waktu_display = substr($hasil_konseling['waktu_mulai'], 0, 5) . ' - ' . substr($hasil_konseling['waktu_selesai'], 0, 5);
            $nomor_antrian = $hasil_konseling['id_antrian'] ? 'A-' . str_pad($hasil_konseling['id_antrian'], 3, '0', STR_PAD_LEFT) : 'N/A';
            $catatan = empty($hasil_konseling['catatan']) ? "Tidak ada catatan yang dicantumkan." : htmlspecialchars($hasil_konseling['catatan']);
            $rekomendasi = empty($hasil_konseling['rekomendasi']) ? "Tidak ada rekomendasi." : htmlspecialchars($hasil_konseling['rekomendasi']);
        ?>
        
        <div class="hasil-card">
            
            <div class="hasil-detail">
                <label>Nama Pasien</label>
                <strong><?= htmlspecialchars($hasil_konseling['nama_pasien']) ?></strong>
            </div>
            
            <div class="hasil-detail">
                <label>Nama Psikolog</label>
                <strong><?= htmlspecialchars($hasil_konseling['nama_psikolog']) ?></strong>
            </div>

            <div class="hasil-detail">
                <label>Tanggal</label>
                <strong><?= $tgl_display ?></strong>
            </div>
            
            <div class="hasil-detail">
                <label>Waktu</label>
                <strong><?= $waktu_display ?></strong>
            </div>

            <div class="status-badge">
                <span class="material-icons">check_circle</span>
                Sesi Selesai (<?= ucwords(strtolower($hasil_konseling['status_konseling'] ?? 'Lunas')) ?>)
            </div>

            <div class="nomor-antrian-label">Nomor Antrian</div>
            <div class="nomor-antrian-display">
                <?= $nomor_antrian ?>
            </div>

            <div class="catatan-container">
                <label>Catatan Psikolog:</label>
                <div class="catatan-text">
                    <?= $catatan ?>
                </div>
            </div>

             <div class="catatan-container" style="margin-top: 20px;">
                <label>Rekomendasi:</label>
                <div class="catatan-text">
                    <?= $rekomendasi ?>
                </div>
            </div>

        </div>

        <?php else: ?>
            <div class="no-data">
                Belum ada hasil konseling yang tersedia untuk Anda.
                <div style="margin-top: 15px;">
                    <a href="riwayat.php" style="color: var(--primary-blue); text-decoration: none; font-weight: 600;">&larr; Cek Status Riwayat</a>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <script>
        // JS untuk Sidebar Menu (Sama dengan jadwal.php)
        function toggleMenu() {
            const sidebar = document.getElementById('sidebarMenu');
            const overlay = document.querySelector('.overlay');

            sidebar.classList.toggle('open');
            overlay.classList.toggle('visible');
            
            // Mencegah scrolling body saat menu terbuka
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : 'auto';
        }
    </script>

</body>
</html>