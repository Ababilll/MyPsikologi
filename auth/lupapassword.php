<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lupa Password - H-Deeja Psychology Center</title>

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

    /* CONTENT WRAPPER */
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

    .bottom a {
        color: #1c8fe6;
        font-weight: 600;
        text-decoration: none;
    }

    .msg-success {
        color: green;
        text-align: center;
        margin-bottom: 15px;
    }

    /* RESPONSIVE */
    @media (min-width: 768px) {
        .header { height: 330px; }
        .container { max-width: 480px; }
        input { font-size: 16px; }
    }

    @media (min-width: 1100px) {
        .header {
            height: 380px;
            border-bottom-left-radius: 60px;
            border-bottom-right-radius: 60px;
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
    <h2>Lupa Kata Sandi?</h2>

    <?php 
    if (isset($_GET['msg'])) {
        echo "<p class='msg-success'>".$_GET['msg']."</p>";
    }
    ?>

    <form action="lupapassword_proses.php" method="POST">

        <label>Email</label>
        <input type="email" name="email" required>

        <button type="submit">Kirim</button>

        <div class="bottom">
            Ingat kata sandi? <a href="login.php">Masuk</a>
        </div>
    </form>
</div>

</body>
</html>
