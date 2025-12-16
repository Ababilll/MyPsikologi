<?php
session_start();
include "../config/db.php"; // sesuaikan path kalau beda

// Hapus pesan lama (jika ada)
$pesan = "";
if (isset($_SESSION['pesan'])) {
    $pesan = $_SESSION['pesan'];
    unset($_SESSION['pesan']);
}

if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Cari user berdasarkan email
    $query = mysqli_query($conn, "SELECT * FROM pengguna WHERE email = '$email' LIMIT 1");
    
    if (mysqli_num_rows($query) == 0) {
        $_SESSION['pesan'] = "Email tidak terdaftar!";
    } else {
        $user = mysqli_fetch_assoc($query);

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            
            // === LOGIN BERHASIL ===
            $_SESSION['login_status'] = true;
            $_SESSION['user_id']      = $user['id_pengguna'];
            $_SESSION['nama']         = $user['nama'];
            $_SESSION['email']        = $user['email'];
            $_SESSION['role']         = $user['role']; // penting!

            // Redirect sesuai role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
                exit();
            } else {
                // Untuk user biasa / pasien
                header("Location: ../user/home.php"); // atau user/dashboard.php
                exit();
            }

        } else {
            $_SESSION['pesan'] = "Password salah!";
        }
    }
    
    // Jika gagal, kembali ke login dengan pesan error
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - H-Deeja Psychology Center</title>
<style>
    body{margin:0;font-family:'Segoe UI',sans-serif;background:#d9eef7;}
    .header{
        background:url('../img/klinik.jpeg') center/cover no-repeat;
        height:250px;display:flex;flex-direction:column;justify-content:center;align-items:center;
        border-bottom-left-radius:40px;border-bottom-right-radius:40px;text-align:center;color:white;padding:20px;
    }
    .logo{
        background:white;color:#1b2a49;padding:25px 25px;border-radius:50%;font-weight:bold;margin-bottom:10px;font-size:20px;
    }
    .container{max-width:420px;margin:0 auto;padding:30px 20px;text-align:left;}
    h2{text-align:center;margin-bottom:25px;color:#0b1c36;font-size:24px;}
    label{display:block;margin-bottom:6px;color:#0b1c36;font-weight:600;}
    input{
        width:100%;padding:14px;border-radius:12px;border:none;margin-bottom:18px;
        box-shadow:0 2px 6px rgba(0,0,0,0.1);font-size:15px;
    }
    button{
        width:100%;padding:14px;background:#4a90e2;border:none;color:white;border-radius:20px;
        font-size:17px;cursor:pointer;margin-top:10px;font-weight:bold;
        box-shadow:0 4px 10px rgba(0,0,0,0.15);transition:0.3s;
    }
    button:hover{background:#357abd;}
    .bottom{text-align:center;margin-top:15px;font-size:14px;}
    .bottom a{color:#1c8fe6;font-weight:600;text-decoration:none;}
    .error{color:#e74c3c;text-align:center;margin:15px 0;padding:12px;background:#ffeaea;border-radius:10px;font-weight:500;}
    @media (min-width:768px){
        .header{height:330px;}
        .container{max-width:480px;}
    }
    @media (min-width:1100px){
        .header{border-bottom-left-radius:60px;border-bottom-right-radius:60px;height:380px;}
    }
</style>
</head>
<body>

<!-- HEADER DENGAN LOGO GAMBAR DI TENGAH -->
<div class="header">
    <!-- GAMBAR LOGO DI TENGAH -->
    <img src="../img/logo.png" alt="H-Deeja Psychology Center" style="height: 100px; width: auto; margin-bottom: 15px; border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">

    <span style="font-size:24px;font-weight:bold;">H-Deeja Psychology Center</span>
</div>

<div class="container">
    <h2>Masuk ke Akun Anda</h2>

    <?php if ($pesan != ""): ?>
        <div class="error"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <label>Email</label>
        <input type="email" name="email" required placeholder="contoh@gmail.com">

        <label>Kata Sandi</label>
        <input type="password" name="password" required placeholder="••••••••">

        <button type="submit" name="login">Masuk</button>
    </form>

    <div class="bottom">
        <a href="lupapassword.php">Lupa Kata Sandi?</a>
    </div>
    <div class="bottom">
        Belum punya akun? <a href="register.php">Daftar Sekarang</a>
    </div>
</div>

</body>
</html>