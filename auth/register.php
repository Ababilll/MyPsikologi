<?php
require_once "../config/db.php";

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email     = $_POST['email'];
    $username  = $_POST['username'];
    $password  = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $cek = mysqli_query($conn, "SELECT * FROM pengguna WHERE email='$email'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "Email sudah digunakan!";
    } else {
        $sql = "INSERT INTO pengguna (email, username, password) 
                VALUES ('$email', '$username', '$password')";

        if (mysqli_query($conn, $sql)) {
            header("Location: login.php");
            exit;
        } else {
            $pesan = "Gagal daftar: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun - H-Deeja Psychology Center</title>

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

    .title {
        font-size: 20px;
        font-weight: bold;
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
        font-weight: bold;
        margin-top: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    button:hover {
        opacity: .9;
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

    .error {
        color: red;
        text-align: center;
        margin-bottom: 15px;
    }

    /* RESPONSIVE TABLET */
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

    /* RESPONSIVE DESKTOP */
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

<!-- HEADER DENGAN LOGO GAMBAR DI TENGAH -->
<div class="header">
    <!-- GAMBAR LOGO DI TENGAH -->
    <img src="../img/logo.png" alt="H-Deeja Psychology Center" style="height: 100px; width: auto; margin-bottom: 15px; border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">

    <span style="font-size:24px;font-weight:bold;">H-Deeja Psychology Center</span>
</div>

<div class="container">
    <h2>Daftar</h2>

    <?php if ($pesan != "") { ?>
        <p class="error"><?= $pesan ?></p>
    <?php } ?>

    <form action="" method="POST">

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Kata Sandi</label>
        <input type="password" name="password" required>

        <button type="submit">Daftar</button>

        <div class="bottom">
            Sudah punya akun? <a href="login.php">Masuk</a>
        </div>
    </form>
</div>

</body>
</html>
