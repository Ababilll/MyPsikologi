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

    <header class="navbar">
        <div class="navbar-left">
            <span class="material-icons menu-icon">menu</span>
            <div class="logo">H-Deeja Psychology Center</div>
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
                <img src="https://via.placeholder.com/800x450/3498db/ffffff?text=Kegiatan+Konseling" alt="Kegiatan Konseling">
                <p class="card-caption">Kegiatan Konseling</p>
            </div>

            <div class="card-quote">
                <blockquote class="quote-box">
                    <p>"Tidak apa-apa untuk merasa lelah. Beristirahat bukan tanda menyerah."</p>
                    <div class="quote-image">
                        <img src="https://via.placeholder.com/300x200/2ecc71/ffffff?text=Terapis+dan+Klien" alt="Terapis dan Klien">
                    </div>
                </blockquote>
            </div>

            <div class="card-image card-secondary">
                <img src="https://via.placeholder.com/800x450/e74c3c/ffffff?text=Konseling+Personal" alt="Konseling Personal">
            </div>

        </section>

    </main>

</body>
</html>