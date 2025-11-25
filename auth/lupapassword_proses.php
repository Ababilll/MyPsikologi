<?php
require "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: lupa_password.php");
    exit;
}

$email = $_POST['email'];

// cek email
$q = mysqli_query($conn, "SELECT * FROM pengguna WHERE email='$email'");
$user = mysqli_fetch_assoc($q);

if (!$user) {
    header("Location: lupa_password.php?msg=Email tidak terdaftar!");
    exit;
}

// generate token reset
$token = bin2hex(random_bytes(32));
$expired = date("Y-m-d H:i:s", time() + 3600);

mysqli_query($conn, "
    UPDATE pengguna 
    SET reset_token='$token', reset_expired='$expired'
    WHERE email='$email'
");

$link = "http://localhost/PsikologiWeb/auth/reset.php?token=$token";

// sementara tampilkan link (kalau belum pakai email)
echo "Link reset password:<br>";
echo "<a href='$link'>$link</a><br><br>";
echo "Berlaku 60 menit.";

exit;
?>
