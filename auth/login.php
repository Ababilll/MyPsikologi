<?php
include "db.php";

$email = $_POST['email'];
$password = $_POST['password'];

$query = mysqli_query($conn, "SELECT * FROM pengguna WHERE email='$email'");
$data = mysqli_fetch_array($query);

if (!$data) {
    echo "Email tidak terdaftar!";
    exit;
}

if (password_verify($password, $data['password'])) {
    echo "Login berhasil!";
    // redirect ke halaman dashboard
} else {
    echo "Password salah!";
}
?>
