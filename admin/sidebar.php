<!-- admin/includes/sidebar.php -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header"><h3>KlinikCare</h3></div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'class="active"':'' ?>><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="kelola_jadwal.php" <?= basename($_SERVER['PHP_SELF'])=='kelola_jadwal.php'?'class="active"':'' ?>><i class="fas fa-calendar-alt"></i> Kelola Jadwal</a></li>
        <li><a href="kelola_data.php" <?= basename($_SERVER['PHP_SELF'])=='kelola_data.php'?'class="active"':'' ?>><i class="fas fa-database"></i> Kelola Data</a></li>
        <li><a href="konfirmasi_pendaftaran.php" <?= basename($_SERVER['PHP_SELF'])=='konfirmasi_pendaftaran.php'?'class="active"':'' ?>>
        <i class="fas fa-calendar-check"></i> Konfirmasi Pendaftaran
    </a>
</li>
    </ul>
    <div class="sidebar-footer">
        <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
    </div>
</div>
<div class="overlay" id="overlay"></div>