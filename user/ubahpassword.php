<?php
session_start();
include "../config/db.php"; 

// --- Cek Status Login ---
if (!isset($_SESSION['login_status']) || $_SESSION['login_status'] !== true) {
    header("Location: ../auth/login.php");
    exit();
}

$message = ''; // Variabel untuk pesan sukses/error
$user_id = $_SESSION['user_id']; 

// --- LOGIKA UBAH SANDI (JIKA FORM DIKIRIM) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ubah_sandi'])) {
    
    // Ambil input dari form
    $sandi_sekarang = $_POST['sandi_sekarang'];
    $sandi_baru = $_POST['sandi_baru'];
    $konfirmasi_sandi_baru = $_POST['konfirmasi_sandi_baru'];

    // 1. Validasi Input
    if (empty($sandi_sekarang) || empty($sandi_baru) || empty($konfirmasi_sandi_baru)) {
        $message = "Semua kolom harus diisi.";
    } elseif ($sandi_baru !== $konfirmasi_sandi_baru) {
        $message = "Kata sandi baru dan konfirmasi tidak cocok.";
    } elseif (strlen($sandi_baru) < 6) { // Contoh: minimal 6 karakter
        $message = "Kata sandi baru minimal 6 karakter.";
    } else {
        // 2. Cek Koneksi Database
        if ($conn->connect_error) {
            $message = "Gagal terhubung ke database.";
        } else {
            // 3. Ambil Hashed Password Lama dari Database
            $sql_check = "SELECT password FROM pengguna WHERE id_pengguna = ?";
            if ($stmt_check = $conn->prepare($sql_check)) {
                $stmt_check->bind_param("i", $user_id);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();

                if ($result_check->num_rows == 1) {
                    $row = $result_check->fetch_assoc();
                    $hashed_password_lama = $row['password'];

                    // 4. Verifikasi Kata Sandi Lama
                    if (password_verify($sandi_sekarang, $hashed_password_lama)) {
                        
                        // 5. Hash Kata Sandi Baru
                        $hashed_sandi_baru = password_hash($sandi_baru, PASSWORD_DEFAULT);

                        // 6. Update Password Baru ke Database
                        $sql_update = "UPDATE pengguna SET password = ? WHERE id_pengguna = ?";
                        if ($stmt_update = $conn->prepare($sql_update)) {
                            $stmt_update->bind_param("si", $hashed_sandi_baru, $user_id);
                            
                            if ($stmt_update->execute()) {
                                $message = "Kata sandi berhasil diubah! Silakan login kembali.";
                                // Optional: Hapus semua sesi dan arahkan ke login
                                // session_destroy();
                                // header("Location: ../auth/login.php");
                                // exit();
                            } else {
                                $message = "Gagal memperbarui kata sandi. Silakan coba lagi. Error: " . $stmt_update->error;
                            }
                            $stmt_update->close();
                        } else {
                            $message = "Gagal menyiapkan query update: " . $conn->error;
                        }
                    } else {
                        $message = "Kata sandi sekarang salah.";
                    }
                } else {
                    $message = "Pengguna tidak ditemukan.";
                }
                $stmt_check->close();
            } else {
                $message = "Gagal menyiapkan query cek sandi: " . $conn->error;
            }
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Kata Sandi | H-Deeja</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        /* CSS umum */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #d9eef7; 
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
            text-decoration: none; 
            color: #1b2a49;
            font-weight: bold;
            font-size: 18px;
        }

        .profile-icon {
            font-size: 28px;
            color: #1b2a49;
            cursor: pointer;
        }

        /* --- TATA LETAK UTAMA UBAH SANDI --- */
        .change-password-container {
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
            color: #4a90e2;
        }

        /* Card Ubah Sandi */
        .card {
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .password-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .password-card-header .key-icon {
            font-size: 28px;
            margin-right: 10px;
            color: #e74c3c; /* Warna ikon kunci */
        }

        .password-card-header h2 {
            font-size: 18px;
            color: #0b1c36;
            margin: 0;
            font-weight: 600;
        }

        /* Input Fields */
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #0b1c36;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input[type="password"] {
            width: 95%;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            margin-bottom: 25px;
            background-color: white; 
            color: #333;
            font-size: 15px;
        }

        /* Tombol Ubah Sandi */
        .btn-ubah-sandi {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background-color: #4a90e2; /* Warna tombol biru */
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.4);
            margin-top: 10px;
        }

        .btn-ubah-sandi:hover {
            background-color: #3a7bd5;
        }
        
        /* Pesan Status */
        .status-message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        .status-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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

    <main class="change-password-container">
        
        <div class="page-title">
            <span class="material-icons profile-icon-large">person</span>
            <h1>Pengaturan Akun</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="status-message <?= (strpos($message, 'berhasil') !== false) ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <section class="card password-card">
                <div class="password-card-header">
                    <span class="material-icons key-icon">key</span>
                    <h2>Ubah Kata Sandi</h2>
                </div>
                
                <div class="form-group">
                    <label for="sandi_sekarang">Kata Sandi Sekarang</label>
                    <input type="password" id="sandi_sekarang" name="sandi_sekarang" required>
                </div>

                <div class="form-group">
                    <label for="sandi_baru">Kata Sandi Baru</label>
                    <input type="password" id="sandi_baru" name="sandi_baru" required>
                </div>

                <div class="form-group">
                    <label for="konfirmasi_sandi_baru">Konfirmasi Sandi Baru</label>
                    <input type="password" id="konfirmasi_sandi_baru" name="konfirmasi_sandi_baru" required>
                </div>

                <button type="submit" name="ubah_sandi" class="btn-ubah-sandi">Ubah Sandi</button>
            </section>
        </form>

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