<?php
// Ganti dengan detail koneksi database Anda
$servername = "localhost";
$db_username = "root"; // Ganti
$db_password = "your_password"; // Ganti
$dbname = "mypsikolog"; // Ganti

// Buat Koneksi
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// ASUMSI: ID Pengguna yang sedang login adalah 1
$user_id = 1; 

// Query untuk mengambil username
$sql = "SELECT username FROM pengguna WHERE id = $user_id";
$result = $conn->query($sql);

$username = "Pengguna"; // Default jika gagal
if ($result->num_rows > 0) {
    // Ambil data baris
    $row = $result->fetch_assoc();
    $username = $row["username"];
}

$conn->close();
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