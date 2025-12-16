<?php
require "../config/db.php";

if (!isset($_GET['token'])) {
    die("Token tidak ada");
}

$token = $_GET['token'];

$q = mysqli_query($conn, "SELECT * FROM pengguna WHERE reset_token='$token'");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    die("Token tidak valid.");
}

// cek waktu expired
if (strtotime($data['reset_expired']) < time()) {
    die("Token sudah kadaluarsa. Silakan request baru.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password - H-Deeja Psychology Center</title>

<style>
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background: #d9eef7;
    }

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
        font-weight: bold;
        margin-top: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    .bottom {
        text-align: center;
        margin-top: 10px;
        font-size: 14px;
    }

    /* RESPONSIVE */
    @media (min-width: 768px) {
        .header { height: 300px; }
        .container { max-width: 480px; }
        input { font-size: 16px; }
    }

    @media (min-width: 1100px) {
        .header {
            height: 350px;
            border-bottom-left-radius: 60px;
            border-bottom-right-radius: 60px;
        }
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
    <h2>Buat Kata Sandi Baru</h2>

    <form action="reset_proses.php" method="POST">
        <input type="hidden" name="token" value="<?php echo $token ?>">

        <label>Kata Sandi Baru</label>
        <input type="password" name="password" required>

        <label>Konfirmasi Sandi Baru</label>
        <input type="password" name="password2" required>

        <button type="submit">Simpan</button>

        <div class="bottom">
            <a href="login.php">Kembali ke Login</a>
        </div>
    </form>
</div>

</body>
</html>
