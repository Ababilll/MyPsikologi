<?php
require_once "db.php";

$email     = $_POST['email'];
$username  = $_POST['username'];
$password  = password_hash($_POST['password'], PASSWORD_BCRYPT);

$sql = "INSERT INTO pengguna (email, username, password) VALUES ('$email', '$username', '$password')";

if (mysqli_query($conn, $sql)) {
    header("Location: login.html");
} else {
    echo "Gagal daftar: " . mysqli_error($conn);
}
?>
