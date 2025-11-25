<?php
session_start();
include "../config/db.php"; 

// --- Cek Status Login ---
if (!isset($_SESSION['login_status']) || $_SESSION['login_status'] !== true) {
    header("Location: ../auth/login.php");
    exit();
}

// Inisialisasi variabel default
$username = $_SESSION['username'] ?? 'Pengguna'; 
$email = ''; 
$nomor_hp = ''; 

$user_id = $_SESSION['user_id']; 

// 2. Query untuk mengambil data profil lengkap
if ($conn->connect_error) {
    // Jika koneksi gagal, hentikan eksekusi
    die("Koneksi ke Database Gagal: " . $conn->connect_error);
}

// Menggunakan Prepared Statement
$sql = "SELECT username, email, nomor_hp FROM pengguna WHERE id_pengguna = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id); 
    if ($stmt->execute()) {
        $result = $stmt->get_result(); 
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $username = htmlspecialchars($row["username"]);
            $email = htmlspecialchars($row["email"]);
            $nomor_hp = htmlspecialchars($row["nomor_hp"]);
        }
    }
    $stmt->close();
}
$conn->close(); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun | H-Deeja</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        /* CSS umum (dari home.php/style.css) */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #d9eef7; /* Background light blue */
            min-height: 100vh;
        }

        /* Navbar Styling */
        .navbar {
            background-color: white;
            padding: 15px 20px;
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

        .menu-icon {
            font-size: 28px;
            cursor: pointer;
            margin-right: 15px;
            color: #1b2a49;
        }

        .logo {
            text-decoration: none; /* Menghilangkan garis bawah */
            color: #1b2a49;
            font-weight: bold;
            font-size: 18px;
        }

        .profile-icon {
            font-size: 28px;
            color: #1b2a49;
            cursor: pointer;
        }

        /* --- SIDEBAR MENU STYLING (Hanya untuk reference, asumsikan sudah ada di file utama) --- */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100%;
            background-color: #f4f7f6; 
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
            z-index: 2000; 
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out; 
            display: flex;
            flex-direction: column;
        }

        .sidebar.open {
            transform: translateX(0);
        }
        
        /* Tambahkan CSS untuk Sidebar menu list, logout, dsb. dari kode sebelumnya */
        /* ... */

        /* --- TATA LETAK UTAMA PENGATURAN AKUN --- */

        .settings-container {
            padding: 20px;
            max-width: 500px;
            margin: 20px auto;
        }

        /* Judul Halaman */
        .page-title {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            color: #0b1c36;
        }

        .page-title h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .profile-icon-large {
            font-size: 32px;
            margin-right: 10px;
            color: #4a90e2; /* Ikon warna biru */
        }

        /* Card Styling */
        .card {
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .profile-info-card h2 {
            font-size: 18px;
            color: #4a90e2;
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Area Foto Profil */
        .profile-picture-area {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            background-color: #d9eef7;
            border-radius: 15px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .profile-icon-placeholder {
            font-size: 60px;
            color: #1b2a49;
        }

        .edit-icon {
            position: absolute;
            bottom: -5px;
            right: -5px;
            font-size: 20px;
            color: #4a90e2;
            background-color: white;
            border-radius: 50%;
            padding: 3px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            cursor: pointer;
        }

        /* Input Fields */
        .profile-info-card label {
            display: block;
            margin-bottom: 6px;
            color: #0b1c36;
            font-weight: 600;
            font-size: 14px;
        }

        .profile-info-card input[type="text"],
        .profile-info-card input[type="email"] {
            width: 96%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc; 
            margin-bottom: 18px;
            background-color: #f8f8f8; 
            color: #333;
            font-size: 15px;
            /* Match the input styling in the image */
        }

        /* Ubah Kata Sandi Card */
        .change-password-card {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #0b1c36;
            font-weight: 600;
            padding: 15px 20px;
            cursor: pointer;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .change-password-card .key-icon {
            font-size: 24px;
            margin-right: 15px;
            color: #e74c3c; 
        }

        /* RESPONSIVE */
        @media (min-width: 768px) {
            .settings-container {
                padding: 40px;
                max-width: 600px;
            }
        }
        /* --- SIDEBAR MENU STYLING --- */

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px; /* Lebar menu */
    height: 100%;
    background-color: #f4f7f6; /* Warna latar belakang menu */
    color: #333;
    padding: 20px 0;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
    z-index: 2000; /* Pastikan menu di atas konten lain */
    
    /* Aturan untuk menyembunyikan menu di luar layar */
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out; 
    
    /* Menggunakan Flexbox untuk tata letak menu vertikal */
    display: flex;
    flex-direction: column;
}

/* Class yang akan ditambahkan oleh JavaScript untuk menampilkan menu */
.sidebar.open {
    transform: translateX(0);
}

.sidebar-header {
    display: flex;
    justify-content: flex-end; /* Ikon close di kanan atas */
    padding: 0 20px 20px 20px;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
}

.close-icon {
    font-size: 28px;
    cursor: pointer;
    color: #4a90e2;
}

.profile-placeholder {
    /* Gaya untuk area kosong di bawah ikon X (opsional) */
    width: 60px;
    height: 60px;
    background-color: #ddd;
    border-radius: 50%;
}

.sidebar-menu-list {
    list-style: none;
    padding: 0;
    flex-grow: 1; /* Agar menu mengisi sisa ruang */
}

.sidebar-menu-list li a {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: background-color 0.2s;
}

.sidebar-menu-list li a:hover,
.sidebar-menu-list li a.active {
    background-color: #d9eef7; /* Warna latar belakang saat aktif/hover */
    color: #4a90e2; /* Warna teks saat aktif/hover */
    border-left: 5px solid #4a90e2; /* Garis aktif (seperti di desain) */
    padding-left: 15px; /* Sesuaikan padding karena ada garis */
}

.sidebar-menu-list li a .material-icons {
    margin-right: 15px;
    font-size: 20px;
}

/* Tombol Keluar (Logout) */
.sidebar-logout {
    display: flex;
    align-items: center;
    padding: 20px;
    text-decoration: none;
    color: #e74c3c; /* Warna merah untuk logout */
    font-weight: 600;
    border-top: 1px solid #ddd;
}

.sidebar-logout .material-icons {
    margin-right: 15px;
    font-size: 20px;
}

/* Tambahkan overlay di belakang menu (Hanya muncul saat menu terbuka) */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5); /* Warna gelap transparan */
    z-index: 1999;
    display: none; /* Default tersembunyi */
}
.overlay.visible {
    display: block;
}
    </style>
</head>

<body>

    <nav class="sidebar" id="sidebarMenu">
        <div class="sidebar-header">
            <span class="material-icons close-icon" onclick="toggleMenu()">close</span>
            <div class="profile-placeholder"></div>
        </div>
        <ul class="sidebar-menu-list">
            <li><a href="home.php"><span class="material-icons">dashboard</span> Dashboard</a></li>
            <li><a href="jadwal.php"><span class="material-icons">calendar_month</span> Jadwal</a></li>
            <li><a href="riwayat.php"><span class="material-icons">history</span> Riwayat Daftar</a></li>
            <li><a href="setting.php" class="active"><span class="material-icons">settings</span> Pengaturan</a></li>
        </ul>
        <a href="../auth/logout.php" class="sidebar-logout">
            <span class="material-icons">logout</span> Keluar
        </a>
    </nav>
    
    <header class="navbar">
        <div class="navbar-left">
            <span class="material-icons menu-icon" onclick="toggleMenu()">menu</span> 
            <a href="home.php" class="logo">H-Deeja Psychology Center</a>
        </div>
    </header>

    <main class="settings-container">
        
        <div class="page-title">
            <span class="material-icons profile-icon-large">person</span>
            <h1>Pengaturan Akun</h1>
        </div>

        <section class="card profile-info-card">
            <h2>Informasi Profil</h2>
            
            <div class="profile-picture-area">
                <span class="material-icons profile-icon-placeholder">person</span>
                <span class="material-icons edit-icon">edit</span>
            </div>

            <label>Nama Pengguna</label>
            <input type="text" value="<?= $username ?>" readonly>

            <label>Nomor HP</label>
            <input type="text" value="<?= $nomor_hp ?>" readonly>

            <label>Email</label>
            <input type="email" value="<?= $email ?>" readonly>
        </section>

        <a href="ubahpassword.php" class="change-password-card">
            <span class="material-icons key-icon">key</span>
            <span>Ubah Kata Sandi</span>
        </a>
        
    </main>

    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebarMenu');
            const overlay = document.querySelector('.overlay');

            sidebar.classList.toggle('open');

            if (sidebar.classList.contains('open')) {
                overlay.classList.add('visible');
            } else {
                overlay.classList.remove('visible');
            }
        }

        // Kode untuk membuat dan mengatur event listener pada overlay
        document.addEventListener('DOMContentLoaded', (event) => {
            let overlay = document.querySelector('.overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.classList.add('overlay');
                overlay.setAttribute('onclick', 'toggleMenu()');
                document.body.appendChild(overlay);
            }
        });
    </script>

</body>
</html>