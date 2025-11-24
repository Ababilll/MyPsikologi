<?php
require "db.php";

$email = $_POST['email'];

$q = mysqli_query($conn, "SELECT * FROM pengguna WHERE email='$email'");
$user = mysqli_fetch_assoc($q);

if (!$user) {
    die("Email tidak terdaftar!");
}

// generate random token
$token = bin2hex(random_bytes(32));
$expired = date("Y-m-d H:i:s", time() + 3600);

// simpan ke database
mysqli_query($conn, "
    UPDATE pengguna 
    SET reset_token='$token', reset_expired='$expired'
    WHERE email='$email'
");

$link = "http://localhost/mypsikolog/MyPsikologi/auth/reset.php?token=$token";

echo "Link reset password:<br><a href='$link'>$link</a><br><br>
Klik link di atas (berlaku 60 menit).";
