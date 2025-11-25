<?php
require "../config/db.php";

$token = $_POST['token'];
$pass  = $_POST['password'];
$pass2 = $_POST['password2'];

if ($pass !== $pass2) {
    echo "<script>
        alert('Password tidak sama!');
        history.back();
    </script>";
    exit;
}

$q = mysqli_query($conn, "SELECT * FROM pengguna WHERE reset_token='$token'");
$data = mysqli_fetch_assoc($q);

if (!$data) {
    echo "<script>
        alert('Token tidak valid!');
        window.location='../auth/lupapassword.php';
    </script>";
    exit;
}

// cek expired
if (strtotime($data['reset_expired']) < time()) {
    echo "<script>
        alert('Token sudah kadaluarsa! Silakan request lupa password baru.');
        window.location='password.php';
    </script>";
    exit;
}

// enkripsi password baru
$newPass = password_hash($pass, PASSWORD_DEFAULT);

// update ke database
mysqli_query($conn, "
    UPDATE pengguna 
    SET password='$newPass', reset_token=NULL, reset_expired=NULL
    WHERE reset_token='$token'
");

echo "<script>
    alert('Password berhasil direset! Silakan login menggunakan password baru.');
    window.location='login.php';
</script>";