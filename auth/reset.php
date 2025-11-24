<?php
require "db.php";

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
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">
    <h2>Reset Password</h2>
    <form action="reset_process.php" method="POST">
        <input type="hidden" name="token" value="<?php echo $token ?>">

        <label>Password Baru</label>
        <input type="password" name="password" required>

        <label>Konfirmasi Password</label>
        <input type="password" name="password2" required>

        <button type="submit">Ganti Password</button>
    </form>
</div>
</body>
</html>
