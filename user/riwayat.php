<?php
session_start();
// Pastikan path ke file koneksi database sudah benar
include "../config/db.php"; 

// --- 1. Cek Status Login dan Ambil ID Pengguna ---
// Kita menggunakan 'user_id' sesuai dengan kunci sesi di login.php
$id_pengguna_login = $_SESSION['user_id'] ?? null; 
$username = $_SESSION['username'] ?? 'Pengguna'; 

if (!isset($_SESSION['login_status']) || $_SESSION['login_status'] !== true || !is_numeric($id_pengguna_login) || $id_pengguna_login <= 0) {
    header("Location: ../auth/login.php");
    exit();
}

// --- 2. Ambil Semua Riwayat Pemesanan untuk Pengguna ini ---
function fetch_riwayat_pemesanan($conn, $id_pengguna) {
    // Query untuk mengambil semua pemesanan pengguna, digabungkan dengan detail jadwal dan psikolog
    $sql = "SELECT 
                p.id_pemesanan,
                p.tanggal_pesan, 
                p.status_pemesanan,
                j.tanggal AS tgl_konseling,
                j.waktu_mulai,
                j.waktu_selesai,
                ps.nama AS nama_psikolog
            FROM pemesanan p
            JOIN jadwal j ON p.id_jadwal = j.id_jadwal
            JOIN psikolog ps ON j.id_psikolog = ps.id_psikolog
            WHERE p.id_pengguna = ?
            ORDER BY p.tanggal_pesan DESC"; // Urutkan dari yang terbaru
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_pengguna);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $riwayat = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    
    return $riwayat;
}

$riwayat_pemesanan = fetch_riwayat_pemesanan($conn, $id_pengguna_login);

// --- 3. LOGIKA UNTUK NOMOR ANTRIAN ---
function format_nomor_antrian($id_pemesanan) {
    return 'A-' . str_pad($id_pemesanan, 3, '0', STR_PAD_LEFT);
}

// Untuk Display "Halo! Rilla"
$display_name = ucwords(strtolower($_SESSION['username'] ?? 'Pengguna')); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pendaftaran | H-Deeja</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        /* Variabel Warna (Sama dengan jadwal.php) */
        :root {
            --primary-blue: #4a90e2; 
            --secondary-blue: #e0f2fe; 
            --background-page: #f0f7fb; 
            --text-dark: #1b2a49; 
            --text-medium: #555;
            --input-bg: #f8f8f8;
            --card-bg: white;
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
        /* Tautan aktif untuk Riwayat */
        .sidebar-menu-list li a:hover, .sidebar-menu-list li a.active-riwayat { background-color: #d9eef7; color: var(--primary-blue); border-left: 5px solid var(--primary-blue); padding-left: 15px; } 
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

        /* --- RIWAYAT CARD SPECIFIC STYLES (Disesuaikan) --- */
        .riwayat-card {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
        }

        .detail-row {
            margin-bottom: 15px;
            font-size: 15px;
        }
        .detail-row label {
            display: block;
            font-weight: 400;
            color: var(--text-medium);
            margin-bottom: 3px;
        }
        .detail-row strong {
            display: block;
            font-weight: 600;
            color: var(--text-dark);
        }

        .status-badge {
            display: flex;
            align-items: center;
            font-weight: 600;
            margin-top: 10px;
        }
        .status-badge span.material-icons {
            font-size: 20px;
            margin-right: 5px;
        }
        .status-selesai { color: #007bff; } /* Warna biru netral untuk lunas/selesai */ 
        .status-terdaftar { color: #28a745; } /* Hijau */
        .status-menunggu { color: #ffc107; } /* Kuning */

        .antrian-box {
            background-color: var(--background-page);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-top: 25px;
        }
        .antrian-box label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-medium);
            display: block;
            margin-bottom: 10px;
        }
        .antrian-box .nomor {
            font-size: 48px;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1;
        }
        
        .no-data {
            text-align: center;
            padding: 30px 20px;
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
            <li><a href="riwayat.php" class="active-riwayat"><span class="material-icons">history</span> Riwayat Daftar</a></li> 
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
        
        <h1 class="page-title">Riwayat Pendaftaran</h1>
        <p class="page-subtitle">Lihat riwayat dan status pendaftaran konseling Anda.</p>

        <?php if (!empty($riwayat_pemesanan)): ?>
            
            <?php foreach ($riwayat_pemesanan as $riwayat): 
                // Format Waktu dan Tanggal
                $tgl_konseling_display = date('d F Y', strtotime($riwayat['tgl_konseling']));
                $waktu_display = substr($riwayat['waktu_mulai'], 0, 5) . ' - ' . substr($riwayat['waktu_selesai'], 0, 5);
                $status_clean = ucwords(strtolower($riwayat['status_pemesanan']));
                
                // Tentukan warna status
                // Tentukan warna status
                $status_class = 'status-menunggu';
                $status_icon = 'schedule';

                if (strpos($status_clean, 'Terdaftar') !== false || strpos($status_clean, 'Konfirmasi') !== false) {
                    $status_class = 'status-terdaftar';
                    $status_icon = 'check_circle';
                    $nomor_label = 'Nomor Antrian Resmi'; // Jika sudah terdaftar/terkonfirmasi
                    
                } elseif (strpos($status_clean, 'Lunas') !== false || strpos($status_clean, 'Selesai') !== false) {
                    // Status setelah pembayaran dan sesi selesai
                    $status_class = 'status-selesai';
                    $status_icon = 'done_all';
                    $status_clean = 'Sesi Selesai (Lunas)';
                    $nomor_label = 'Nomor Antrian Sesi';

                } elseif (strpos($status_clean, 'Belum Dibayar') !== false) {
                    // Status setelah sesi selesai namun pembayaran belum dilakukan
                    $status_class = 'status-batal'; // Menggunakan merah untuk menandakan kewajiban
                    $status_icon = 'credit_card';
                    $status_clean = 'Sesi Selesai (Belum Dibayar)';
                    $nomor_label = 'Nomor Antrian Sesi';

                } elseif (strpos($status_clean, 'Batal') !== false) {
                    $status_class = 'status-batal';
                    $status_icon = 'cancel';
                    $nomor_label = 'Nomor Referensi Pendaftaran';
                } else {
                    $nomor_label = 'Nomor Referensi Pendaftaran';
                }

            ?>
            <div class="riwayat-card">
            <div class="riwayat-card">
                
                <div class="detail-row">
                    <label>Nama Pasien</label>
                    <strong><?= htmlspecialchars($display_name) ?></strong> 
                </div>

                <div class="detail-row">
                    <label>Nama Psikolog</label>
                    <strong><?= htmlspecialchars($riwayat['nama_psikolog']) ?></strong>
                </div>

                <div class="detail-row">
                    <label>Tanggal Konseling</label>
                    <strong><?= $tgl_konseling_display ?></strong>
                </div>
                
                <div class="detail-row">
                    <label>Waktu Konseling</label>
                    <strong>
                        <span class="material-icons" style="font-size: 16px; vertical-align: middle;">schedule</span>
                        <?= $waktu_display ?>
                    </strong>
                </div>
                <div class="status-badge <?= $status_class ?>">
                    <span class="material-icons"><?= $status_icon ?></span>
                    <?= $status_clean ?>
                </div>

                <?php 
                    // Tampilkan nomor antrian jika status Dikonfirmasi, Belum Dibayar, atau Lunas
                    if ($status_class == 'status-terdaftar' || $status_class == 'status-selesai' || strpos($status_clean, 'Belum Dibayar') !== false): 
                        // Gunakan id_antrian jika ada, jika tidak, gunakan id_pemesanan sebagai fallback
                        $nomor_tampil = !empty($riwayat['id_antrian']) ? $riwayat['id_antrian'] : $riwayat['id_pemesanan'];
                ?>
                <div class="antrian-box" style="<?= (strpos($status_clean, 'Belum Dibayar') !== false) ? 'background-color: #f8d7da; border: 1px solid #f5c6cb;' : '' ?>">
                    <label style="<?= (strpos($status_clean, 'Belum Dibayar') !== false) ? 'color: #721c24;' : '' ?>"><?= $nomor_label ?></label>
                    <div class="nomor" style="<?= (strpos($status_clean, 'Belum Dibayar') !== false) ? 'color: #721c24; font-size: 36px;' : '' ?>">
                        <?= format_nomor_antrian($nomor_tampil) ?> 
                    </div>
                    <?php if (strpos($status_clean, 'Belum Dibayar') !== false): ?>
                        <p style="font-size: 12px; margin-top: 10px; color: #721c24; font-weight: 500;">*Harap selesaikan pembayaran di klinik.</p>
                    <?php endif; ?>
                </div>
                <?php 
                    // Jika status selain yang di atas (misal: Batal), dan belum ada id_antrian, tampilkan referensi pendaftaran
                    else:
                ?>
                <div class="antrian-box" style="background-color: #f0f7fb; border: 1px dashed #e0e0e0;">
                    <label style="color: var(--text-medium);">Nomor Referensi Pendaftaran</label>
                    <div class="nomor" style="color: var(--text-medium); font-size: 36px;">
                        <?= format_nomor_antrian($riwayat['id_pemesanan']) ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <?php endforeach; ?>

        <?php else: ?>
            <div class="no-data">
                Anda belum memiliki riwayat pendaftaran konseling.
                <div style="margin-top: 15px;">
                    <a href="jadwal.php" style="color: var(--primary-blue); text-decoration: none; font-weight: 600;">&larr; Cek Jadwal Sekarang</a>
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