<?php
session_start();
include "../config/db.php";

// Cek login
if (!isset($_SESSION['login_status']) || !isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$id_jadwal = (int)($_GET['id_jadwal'] ?? 0);
$id_pengguna = $_SESSION['user_id'];

if ($id_jadwal <= 0) {
    die("Jadwal tidak valid");
}

// Cek apakah jadwal ada dan masih ada kuota
$cek = mysqli_query($conn, "SELECT kuota, terisi FROM jadwal WHERE id_jadwal = $id_jadwal");
$j = mysqli_fetch_assoc($cek);

if (!$j || ($j['kuota'] - $j['terisi']) <= 0) {
    echo "<script>alert('Maaf, kuota sudah penuh!'); window.location='jadwal.php';</script>";
    exit();
}

// Cek apakah user sudah daftar di jadwal ini
$cek_duplikat = mysqli_query($conn, "SELECT id_antrian FROM antrian 
                                     WHERE id_pengguna = $id_pengguna 
                                     AND id_konseling IS NULL");
if (mysqli_num_rows($cek_duplikat) > 0) {
    echo "<script>alert('Anda sudah terdaftar di antrian!'); window.location='jadwal.php';</script>";
    exit();
}

// Insert langsung ke tabel antrian
$insert = mysqli_query($conn, "INSERT INTO antrian (id_pengguna, waktuDaftar, id_konseling) 
                               VALUES ($id_pengguna, NOW(), NULL)");

if ($insert) {
    // Tambah terisi di jadwal
    mysqli_query($conn, "UPDATE jadwal SET terisi = terisi + 1 WHERE id_jadwal = $id_jadwal");

    echo "<script>
            alert('Pendaftaran berhasil!\\nAnda sudah masuk antrian.\\nSilakan datang sesuai jadwal.');
            window.location='riwayat.php';
          </script>";
} else {
    echo "<script>alert('Gagal mendaftar. Coba lagi nanti.'); window.location='jadwal.php';</script>";
}
?>