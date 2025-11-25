<?php
session_start();
include "../config/db.php";

// Gunakan variabel sesi untuk pesan, lebih baik daripada variabel lokal
$pesan = ""; 
if (isset($_SESSION['pesan'])) {
    $pesan = $_SESSION['pesan'];
    unset($_SESSION['pesan']); // Hapus pesan setelah ditampilkan sekali
}

if (isset($_POST['login'])) {

    // Sanitasi input untuk keamanan (minimal)
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM pengguna WHERE email='$email'");
    $data = mysqli_fetch_array($query);

    if (!$data) {
        // Gagal: Email tidak terdaftar
        $pesan = "Email tidak terdaftar!";
    } else if (password_verify($password, $data['password'])) {
        
        // --- BLOK LOGIN BERHASIL ---
        
        // 1. BUAT SESI LOGIN
        $_SESSION['login_status'] = true;
        // Simpan data penting pengguna ke sesi (JANGAN simpan password!)
        $_SESSION['user_id'] = $data['id'];
        $_SESSION['username'] = $data['username']; // Asumsi ada kolom 'username'
        
        // 2. LAKUKAN PENGALIHAN (REDIRECT)
        $dashboard_url = "../user/home.php"; 
        
        header("Location:" . $dashboard_url);
        exit(); // PENTING: Hentikan eksekusi script setelah redirect

    } else {
        // Gagal: Password salah
        $pesan = "Password salah!";
    }
    
    // Simpan pesan gagal ke sesi agar bisa ditampilkan di halaman ini (opsional)
    // $_SESSION['pesan'] = $pesan;
    // header("Location: login.php"); // Atau redirect ke halaman ini
    // exit();

}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - H-Deeja Psychology Center</title>

<style>
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background: #d9eef7;
    }

    /* HEADER */
    .header {
        background: url('../img/klinik.jpeg') center/cover no-repeat;
        height: 250px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border-bottom-left-radius: 40px;
        border-bottom-right-radius: 40px;
        text-align: center;
        color: white;
        padding: 20px;
    }

    .logo {
        background: white;
        color: #1b2a49;
        padding: 25px 25px;
        border-radius: 50%;
        font-weight: bold;
        margin-bottom: 10px;
    }

    /* FORM WRAPPER */
    .container {
        max-width: 420px;
        margin: 0 auto;
        padding: 30px 20px;
        text-align: left;
    }

    h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #0b1c36;
    }

    label {
        display: block;
        margin-bottom: 6px;
        color: #0b1c36;
        font-weight: 600;
    }

    input {
        width: 100%;
        padding: 12px;
        border-radius: 12px;
        border: none;
        margin-bottom: 18px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        font-size: 15px;
    }

    button {
        width: 100%;
        padding: 14px;
        background: #4a90e2;
        border: none;
        color: white;
        border-radius: 20px;
        font-size: 17px;
        cursor: pointer;
        margin-top: 10px;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    .bottom {
        text-align: center;
        margin-top: 10px;
        font-size: 14px;
    }

    .bottom a {
        color: #1c8fe6;
        font-weight: 600;
        text-decoration: none;
    }

    /* RESPONSIVE DESKTOP */
    @media (min-width: 768px) {
        .header {
            height: 330px;
        }
        .container {
            max-width: 480px;
        }
        input {
            font-size: 16px;
        }
    }

    /* RESPONSIVE LARGE SCREEN */
    @media (min-width: 1100px) {
        .header {
            border-bottom-left-radius: 60px;
            border-bottom-right-radius: 60px;
            height: 380px;
        }
    }
</style>
</head>

<body>

<div class="header">
    <div class="logo">Logo</div>
    <span style="font-size: 20px; font-weight: bold;">H-Deeja Psychology Center</span>
</div>

<div class="container">
    <h2>Masuk</h2>

    <form action="" method="POST">

        <?php if ($pesan != "") { ?>
            <p style="color:red; text-align:center; margin-bottom:10px;">
                <?= $pesan ?>
            </p>
        <?php } ?>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Kata Sandi</label>
        <input type="password" name="password" required>

        <button type="submit" name="login">Masuk</button>

        <div class="bottom">
            <a href="lupapassword.php">Lupa Kata Sandi?</a>
        </div>

        <div class="bottom">
            Belum punya akun? <a href="register.php">Daftar</a>
        </div>

    </form>
</div>

</body>
</html>
