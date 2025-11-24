<?php
require "db.php";

$token = $_POST['token'];
$pass  = $_POST['password'];
$pass2 = $_POST['password2'];

if ($pass !== $pass2) {
    die("Password tidak sama!");
}

$q = mysqli_query($conn, "SELECT * FROM pengguna WHERE reset_token='$token'");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    die("Token tidak valid.");
}

// cek expired
if (strtotime($data['reset_expired']) < time()) {
    die("Token kadaluarsa.");
}

// enkripsi password baru
$newPass = password_hash($pass, PASSWORD_DEFAULT);

// update ke database
mysqli_query($conn, "
    UPDATE users 
    SET password='$newPass', reset_token=NULL, reset_expired=NULL
    WHERE reset_token='$token'
");

echo "Password telah diganti. <a href='login.html'>Masuk</a>";
