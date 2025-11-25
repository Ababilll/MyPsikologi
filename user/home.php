<?php
session_start();
include "../config/db.php";

// 1. CEK KONEKSI (Sudah Anda lakukan, bagus!)
if ($conn->connect_error) {
    die("Koneksi ke Database Gagal: " . $conn->connect_error);
}

// 2. CEK STATUS LOGIN (Sudah Anda lakukan, bagus!)
if (!isset($_SESSION['login_status']) || $_SESSION['login_status'] !== true) {
    header("Location: ../auth/login.php");
    exit();
}

// 3. AMBIL ID DARI SESI
$user_id = $_SESSION['user_id']; 

$username = "Pengguna"; 

// 4. PERSIAPAN DAN EKSEKUSI STATEMENT DENGAN PENGECEKAN ERROR

$sql = "SELECT username FROM pengguna WHERE id_pengguna = ?";

// Pengecekan 1: Apakah Statement berhasil dipersiapkan?
if ($stmt = $conn->prepare($sql)) {
    
    $stmt->bind_param("i", $user_id);
    
    // Pengecekan 2: Apakah Statement berhasil dieksekusi?
    if ($stmt->execute()) {
        
        $result = $stmt->get_result(); 
        
        // Pengecekan 3: Apakah ada baris yang dikembalikan?
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $username = htmlspecialchars($row["username"]);
        }
        
    } else {
        // Gagal Eksekusi
        // Anda bisa log error ini di sistem Anda: $stmt->error
        error_log("Gagal Eksekusi Query: " . $stmt->error);
        $username = "ErrorExec";
    }
    
    $stmt->close();
    
} else {
    // Gagal Persiapan Query
    // Sering terjadi jika nama tabel/kolom salah, atau error sintaks SQL.
    error_log("Gagal Persiapan Query: " . $conn->error);
    $username = "ErrorPrepare";
}

// Opsional: Tutup koneksi di akhir halaman
// $conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | H-Deeja Psychology Center</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

<nav class="sidebar" id="sidebarMenu">
    <div class="sidebar-header">
        <span class="material-icons close-icon" onclick="toggleMenu()">close</span>
        <div class="profile-placeholder"></div>
    </div>
    
    <ul class="sidebar-menu-list">
        <li><a href="home.php" class="active"><span class="material-icons">dashboard</span> Dashboard</a></li>
        <li><a href="#"><span class="material-icons">calendar_month</span> Jadwal</a></li>
        <li><a href="#"><span class="material-icons">history</span> Riwayat Daftar</a></li>
        <li><a href="#"><span class="material-icons">settings</span> Pengaturan</a></li>
    </ul>

    <a href="../auth/logout.php" class="sidebar-logout">
        <span class="material-icons">logout</span> Keluar
    </a>
</nav>

    <header class="navbar">
        <div class="navbar-left">
            <span class="material-icons menu-icon" onclick="toggleMenu()" id="menuIcon">menu</span>
            <a href="home.php" class="logo">H-Deeja Psychology Center</a>
        </div>
        <div class="navbar-right">
            <span class="material-icons profile-icon">person</span>
        </div>
    </header>

    <main class="dashboard-container">

    <section class="welcome-card">
        <h1 class="welcome-title">Halo! <?php echo htmlspecialchars($username); ?></h1>
        <p class="welcome-message">
            Semoga harimu menyenangkan hari ini.
            Ingat, istirahat sejenak juga penting untuk kesehatan pikiran 
            <span class="material-icons arrow-down-icon">arrow_drop_down</span>
        </p>
    </section>

        <section class="action-buttons">
            <a href="#" class="btn btn-result">Hasil Konseling Anda</a>
            <a href="#" class="btn btn-medical-record">Rekam Medis</a>
        </section>

        <section class="feedback-section">
            <p>Apakah anda puas dengan pelayanan kami?</p>
            <div class="feedback-options">
                <button class="btn btn-feedback btn-yes">Iya</button>
                <button class="btn btn-feedback btn-no">Tidak</button>
            </div>
        </section>

        <section class="content-cards">
            
            <div class="card-image card-main">
                <img src="../img/ai-generated-9556075_1280.png" alt="Kegiatan Konseling">
                <p class="card-caption">Kegiatan Konseling</p>
            </div>

            <div class="card-quote">
                <blockquote class="quote-box">
                    <p>"Tidak apa-apa untuk merasa lelah. Beristirahat bukan tanda menyerah."</p>
                    <div class="quote-image">
                        <img src="../img/psikolog.png" alt="Terapis dan Klien">
                    </div>
                </blockquote>
            </div>

            <div class="card-image card-secondary">
                <img src="../img/terapis.png" alt="Konseling Personal">
            </div>

        </section>

    </main>

    <script>
    function toggleMenu() {
        const sidebar = document.getElementById('sidebarMenu');
        const overlay = document.querySelector('.overlay');

        // Toggle class 'open' pada sidebar
        sidebar.classList.toggle('open');

        // Toggle class 'visible' pada overlay
        if (sidebar.classList.contains('open')) {
            overlay.classList.add('visible');
        } else {
            overlay.classList.remove('visible');
        }
    }

    // Tambahkan event listener untuk menutup menu saat overlay diklik
    document.addEventListener('DOMContentLoaded', (event) => {
        const overlay = document.createElement('div');
        overlay.classList.add('overlay');
        overlay.setAttribute('onclick', 'toggleMenu()');
        document.body.appendChild(overlay);
    });

</script>
</body>
</html>