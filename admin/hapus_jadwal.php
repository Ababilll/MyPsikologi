<?php
session_start();
if (!isset($_SESSION['login_status']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php"); exit();
}
include "../config/db.php";

if (!isset($_GET['id'])) {
    header("Location: kelola_jadwal.php"); exit();
}

$id = (int)$_GET['id'];

// Hapus jadwal
$hapus = mysqli_query($conn, "DELETE FROM jadwal WHERE id_jadwal = $id");

if ($hapus) {
    echo "<script>
            alert('Jadwal berhasil dihapus!');
            location.href='kelola_jadwal.php';
          </script>";
} else {
    echo "<script>
            alert('Gagal menghapus jadwal!');
            location.href='kelola_jadwal.php';
          </script>";
}
?>