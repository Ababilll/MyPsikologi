<?php
session_start();
include "../config/db.php"; 

// --- 1. Cek Status Login (Asumsi Admin/Psikolog) ---
// Dalam sistem nyata, perlu ada verifikasi peran (role) khusus di sini.
$id_user_login = $_SESSION['user_id'] ?? null; 
$is_admin = true; // Asumsi: Pengguna yang mengakses halaman ini adalah Admin/Psikolog
$display_name = "Admin"; // Ganti dengan nama user yang login jika perlu

if (!isset($_SESSION['login_status']) || $_SESSION['login_status'] !== true || !$is_admin) {
    // Arahkan ke halaman login atau halaman terlarang jika bukan Admin
    header("Location: ../auth/login.php");
    exit();
}


// --- 2. Fungsi Ambil Data Rekam Medis ---
function fetch_rekam_medis($conn) {
    $sql = "SELECT
                rm.id_rekam,
                pem.nama_lengkap,
                pem.nomor_telepon,
                ps.nama AS nama_psikolog,
                rm.diagnosa AS masalah_penyakit,
                a.id_antrian,
                j.tanggal AS tanggal_konseling
            FROM rekam_medis rm
            -- JOIN ke konseling menggunakan id_pengguna dan id_psikolog
            JOIN konseling k ON rm.id_pengguna = k.id_pengguna AND rm.id_psikolog = k.id_psikolog
            JOIN jadwal j ON k.id_jadwal = j.id_jadwal
            JOIN psikolog ps ON k.id_psikolog = ps.id_psikolog
            -- Ambil data identitas dari tabel pemesanan
            LEFT JOIN pemesanan pem ON pem.id_jadwal = k.id_jadwal AND pem.id_pengguna = k.id_pengguna
            LEFT JOIN antrian a ON a.id_konseling = k.id_konseling
            -- Mengelompokkan agar tidak ada data ganda jika pasien punya banyak sesi
            GROUP BY rm.id_rekam 
            ORDER BY j.tanggal DESC"; 
            
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}


$data_rekam_medis = fetch_rekam_medis($conn);

// Helper function
function format_nomor_antrian($id_number) {
    return 'A-' . str_pad($id_number, 3, '0', STR_PAD_LEFT);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekam Medis Pasien | Admin H-Deeja</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        /* Variabel Warna (Konsisten) */
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

        /* CSS umum (Konsisten) */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-page); 
            min-height: 100vh;
        }
        
        /* Navbar Styling (Konsisten) */
        .navbar { background-color: white; padding: 15px var(--spacing); display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); position: sticky; top: 0; z-index: 1000; }
        .navbar-left { display: flex; align-items: center; }
        .menu-icon { font-size: 28px; cursor: pointer; margin-right: 15px; color: var(--text-dark); }
        .logo { text-decoration: none; color: var(--text-dark); font-weight: bold; font-size: 18px; }
        .profile-icon { font-size: 28px; color: var(--text-dark); cursor: pointer; }

        /* --- SIDEBAR MENU STYLING (Konsisten) --- */
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

        /* --- TATA LETAK UTAMA REKAM MEDIS --- */
        .rekam-medis-container { 
            padding: 0 var(--spacing) 40px var(--spacing); 
            max-width: 900px; /* Lebar lebih besar untuk tabel */
            margin: 0 auto; 
        }
        h1.page-title { 
            font-size: 26px; 
            font-weight: 700; 
            color: var(--text-dark); 
            margin: 20px 0 20px 0; 
        }

        /* Navigasi Bulan */
        .month-nav {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 5px;
        }
        .month-nav a {
            text-decoration: none;
            color: var(--text-medium);
            font-weight: 600;
            padding: 5px 0;
            transition: color 0.2s;
        }
        .month-nav a:hover {
            color: var(--primary-blue);
        }
        .month-nav a.active {
            color: var(--primary-blue);
            border-bottom: 3px solid var(--primary-blue);
        }
        
        /* Search Box */
        .search-container {
            position: relative;
            margin-bottom: 30px;
        }
        .search-input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            background-color: var(--card-bg);
        }
        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-medium);
            cursor: pointer;
        }

        /* Tabel Rekam Medis */
        .rekam-medis-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden; /* Penting untuk border-radius */
        }
        .rekam-medis-table thead tr {
            background-color: var(--primary-blue);
            color: white;
        }
        .rekam-medis-table th, .rekam-medis-table td {
            padding: 12px 10px;
            text-align: left;
            font-size: 14px;
        }
        .rekam-medis-table th {
            font-weight: 600;
            background-color: var(--primary-blue);
        }
        .rekam-medis-table tbody tr:nth-child(even) {
            background-color: var(--background-page);
        }
        .rekam-medis-table tbody tr:nth-child(odd) {
            background-color: var(--card-bg);
        }
        .rekam-medis-table tbody tr:hover {
            background-color: #d9eef7;
        }
        .no-data {
            text-align: center;
            padding: 30px;
            background-color: var(--card-bg);
            border-radius: 10px;
            color: var(--text-medium);
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .rekam-medis-container {
                padding-left: 10px;
                padding-right: 10px;
                overflow-x: auto; /* Memungkinkan tabel di-scroll horizontal */
            }
            .rekam-medis-table {
                min-width: 700px; /* Pastikan tabel tidak terlalu sempit */
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

    <main class="rekam-medis-container">
        
        <h1 class="page-title">Rekam Medis Pasien</h1>
        
        <div class="month-nav">
            <a href="#">Oktober</a>
            <a href="#">November</a>
            <a href="#" class="active">Desember</a>
            </div>

        <div class="search-container">
            <input type="text" class="search-input" placeholder="Cari..." onkeyup="filterTable()" id="searchInput">
            <span class="material-icons search-icon">search</span>
        </div>

        <?php if (!empty($data_rekam_medis)): ?>
        <div style="overflow-x: auto;">
            <table class="rekam-medis-table" id="rekamMedisTable">
                <thead>
                    <tr>
                        <th>Nomor Antrian</th>
                        <th>Nama Lengkap</th>
                        <th>Tanggal Konseling</th>
                        <th>Nama Psikolog</th>
                        <th>No. Telp</th>
                        <th>Masalah / Diagnosa</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data_rekam_medis as $data): 
    $nomor_antrian_display = !empty($data['id_antrian']) ? format_nomor_antrian($data['id_antrian']) : 'N/A';
    $tanggal_konseling_display = !empty($data['tanggal_konseling']) ? date('d F Y', strtotime($data['tanggal_konseling'])) : 'Belum Ditentukan';
    $masalah = empty($data['masalah_penyakit']) ? 'Belum Didiagnosa' : htmlspecialchars($data['masalah_penyakit']);
    
    // SEKARANG MENGGUNAKAN DATA DARI TABEL PEMESANAN
    $nama_lengkap = htmlspecialchars($data['nama_lengkap'] ?? 'Tanpa Nama');
    $no_telp = htmlspecialchars($data['nomor_telepon'] ?? '-');
?>
<tr>
    <td><?= $nomor_antrian_display ?></td>
    <td><?= $nama_lengkap ?></td>
    <td><?= $tanggal_konseling_display ?></td>
    <td><?= htmlspecialchars($data['nama_psikolog']) ?></td>
    <td><?= $no_telp ?></td>
    <td><?= $masalah ?></td>
</tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="no-data">
                Tidak ada data rekam medis yang tersedia saat ini.
            </div>
        <?php endif; ?>

    </main>

    <script>
        // JS untuk Sidebar Menu (Konsisten)
        function toggleMenu() {
            const sidebar = document.getElementById('sidebarMenu');
            const overlay = document.querySelector('.overlay');

            sidebar.classList.toggle('open');
            overlay.classList.toggle('visible');
            
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : 'auto';
        }

        // JS untuk fungsi Cari (Filter Table)
        function filterTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toUpperCase();
            const table = document.getElementById("rekamMedisTable");
            const tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) { // Mulai dari 1 untuk lewati header
                tr[i].style.display = "none"; // Sembunyikan semua baris secara default
                const td = tr[i].getElementsByTagName("td");
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        if (td[j].textContent.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break; // Tampilkan baris jika salah satu kolom cocok
                        }
                    }
                }
            }
        }
    </script>

</body>
</html>