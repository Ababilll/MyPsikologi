<?php
session_start();
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}
include "../config/db.php";

if (!isset($_GET['id'])) {
    header("Location: kelola_data.php"); exit();
}

$id = (int)$_GET['id'];
$hapus = mysqli_query($conn, "DELETE FROM antrian WHERE id_antrian = $id");

if ($hapus) {
    echo "<script>
            alert('Data antrian berhasil dihapus!');
            location.href='kelola_data.php';
          </script>";
} else {
    echo "<script>
            alert('Gagal menghapus data!');
            location.href='kelola_data.php';
          </script>";
}
?>