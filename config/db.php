<?php
$host = "localhost:3307"; // Coba ganti ke "localhost" jika ini gagal
$user = "root";
$pass = "";
$conn = mysqli_connect($host, $user, $pass);

if (!$conn) {
    die("Koneksi ke Server Gagal: " . mysqli_connect_error());
}

// Cek apakah database 'mypsikolog' bisa dipilih
if (!mysqli_select_db($conn, "mypsikolog")) {
    echo "Koneksi Server Berhasil, TAPI Database 'mypsikolog' TIDAK DITEMUKAN.<br>";
    echo "Daftar database yang tersedia di server ini:<br>";
    
    $res = mysqli_query($conn, "SHOW DATABASES");
    while ($row = mysqli_fetch_assoc($res)) {
        echo "- " . $row['Database'] . "<br>";
    }
    exit();
} else {
//    echo "Koneksi Berhasil! Database ditemukan.";
}
?>