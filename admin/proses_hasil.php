<?php
session_start();
include "../config/db.php";

if (isset($_POST['simpan_hasil'])) {
    // Memperbaiki typo: mysqli_real_escape_string adalah fungsi yang benar
    $id_konseling = mysqli_real_escape_string($conn, $_POST['id_konseling']);
    $catatan      = mysqli_real_escape_string($conn, $_POST['catatan']);
    $rekomendasi  = mysqli_real_escape_string($conn, $_POST['rekomendasi']);

    // 1. Dapatkan id_pengguna dari tabel konseling agar data hasil_konseling akurat
    $query_user = "SELECT id_pengguna FROM konseling WHERE id_konseling = '$id_konseling'";
    $res_user = mysqli_query($conn, $query_user);
    $user_data = mysqli_fetch_assoc($res_user);
    $id_pengguna = $user_data['id_pengguna'];

    // 2. Simpan ke tabel hasil_konseling
    $sql_insert = "INSERT INTO hasil_konseling (id_konseling, id_pengguna, catatan, rekomendasi) 
                   VALUES ('$id_konseling', '$id_pengguna', '$catatan', '$rekomendasi')";

    if (mysqli_query($conn, $sql_insert)) {
        // 3. Update status di tabel konseling menjadi 'Selesai'
        mysqli_query($conn, "UPDATE konseling SET status = 'Selesai' WHERE id_konseling = '$id_konseling'");
        
        echo "<script>alert('Hasil konseling berhasil disimpan!'); window.location='kelola_data.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: kelola_data.php");
}
?>