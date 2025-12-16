<?php
session_start();
$nama_user = isset($_SESSION['nama_user']) ? $_SESSION['nama_user'] : null;
$role_user = $_SESSION['role_user'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>H-Deeja Psychology Center</title>

    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS internal -->
    <style>
        /* FONT & RESET */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            color: #333;
            background: #fff;
        }

        /* NAVBAR */
        .navbar {
            width: 100%;
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .logo {
            font-weight: 600;
            font-size: 20px;
        }

        .btn-login {
            padding: 8px 18px;
            border-radius: 25px;
            background: #dbeafe;
            color: #1e3a8a;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        /* HERO */
        .hero {
            height: 90vh;
            background: url('img/klinik.jpeg');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            align-items: center;
            padding-left: 60px;
        }

        .overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.4);
        }

        .hero-content {
            position: relative;
            max-width: 550px;
            color: white;
        }

        .hero-content h1 {
            font-size: 36px;
            line-height: 1.3;
            margin-bottom: 20px;
        }

        .hero-content span {
            color: #56ccf2;
        }

        .btn-primary {
            padding: 12px 28px;
            background: #56ccf2;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            display: inline-block;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background: #3ab3d8;
        }

        .center {
            display: block;
            margin: 30px auto 0 auto;
            width: fit-content;
        }

        /* === LAYANAN KAMI === */
        .services {
            padding: 80px 60px;
            background: #f8fdff;
            text-align: center;
        }

        .services h2 {
            font-size: 32px;
            margin-bottom: 20px;
            color: #1e3a8a;
        }

        .services p.subtitle {
            font-size: 16px;
            color: #555;
            margin-bottom: 50px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .service-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .service-icon {
            font-size: 48px;
            margin-bottom: 20px;
            color: #56ccf2;
        }

        .service-card h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #1e3a8a;
        }

        .service-card p {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
        }

        /* ABOUT */
        .about {
            padding: 80px 60px;
            background: #eef6fc;
        }

        .about h2 {
            font-size: 28px;
            margin-bottom: 40px;
        }

        .about-container {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .about-img img {
            border-radius: 10px;
            width: 100%;
        }

        .about-text p {
            margin-bottom: 16px;
        }

        .btn-secondary {
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            border-radius: 25px;
            text-decoration: none;
        }

        /* TEAM */
        .team {
            padding: 80px 60px;
        }

        .team h2 {
            font-size: 28px;
            margin-bottom: 40px;
        }

        .team-container {
            display: flex;
            justify-content: center;
            gap: 40px;
        }

        .team-card {
            width: 280px;
            text-align: center;
        }

        .team-card img {
            width: 100%;
            border-radius: 15px;
            margin-bottom: 12px;
        }

        .team-card h3 {
            font-size: 18px;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .hero {
                padding-left: 20px;
                justify-content: center;
                text-align: center;
            }

            .about-container, .team-container {
                flex-direction: column;
            }

            .services, .about, .team {
                padding: 60px 20px;
            }

            .hero-content h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
<header class="navbar">
    <div class="logo-wrapper" style="display: flex; align-items: center; gap: 12px;">
        <!-- GAMBAR LOGO -->
        <img src="img/logo.png" alt="H-Deeja Logo" style="height: 40px; width: auto;">
        
        <!-- TULISAN -->
        <div class="logo" style="font-weight: 600; font-size: 20px; color: #1e3a8a;">
            H-Deeja Psychology Center
        </div>
    </div>

    <!-- Tombol Masuk tetap di kanan -->
    <a href="auth/login.php" class="btn-login">Masuk</a>
</header>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="overlay"></div>
        <div class="hero-content">
            <h1>Kamu Tidak Sendiri. <span>Kami Siap</span> Mendengarkan</h1>
            <a href="auth/login.php" class="btn-primary">Mulai Konseling</a>
        </div>
    </section>

    <!-- LAYANAN KAMI -->
    <section class="services">
        <h2>Layanan Kami</h2>
        <p class="subtitle">Kami menyediakan berbagai layanan profesional untuk mendukung kesehatan mental Anda, keluarga, dan organisasi</p>

        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">👦</div>
                <h3>Konseling Anak & Remaja</h3>
                <p>Bantuan psikologis khusus untuk anak dan remaja dalam menghadapi tantangan perkembangan dan emosional.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">🧑</div>
                <h3>Konseling Dewasa</h3>
                <p>Konsultasi individu untuk mengatasi stres, kecemasan, depresi, dan masalah kehidupan sehari-hari.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">👨‍👩‍👧‍👦</div>
                <h3>Konseling Keluarga</h3>
                <p>Terapi untuk memperbaiki komunikasi, menyelesaikan konflik, dan memperkuat hubungan keluarga.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">🧩</div>
                <h3>Pendampingan ABK</h3>
                <p>Pendampingan khusus untuk anak berkebutuhan khusus (autisme, ADHD, dll) dan dukungan orang tua.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">🧠</div>
                <h3>Terapi Psikologi</h3>
                <p>Terapi untuk anak, remaja, dan dewasa dengan pendekatan berbasis bukti (CBT, Play Therapy, dll).</p>
            </div>

            <div class="service-card">
                <div class="service-icon">📊</div>
                <h3>Tes Psikologi</h3>
                <p>Tes IQ, kepribadian, minat bakat, dan asesmen psikologis lainnya dengan alat standar.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">👩‍🏫</div>
                <h3>Seminar Parenting</h3>
                <p>Workshop dan pelatihan untuk orang tua dalam mendidik dan memahami anak dengan lebih baik.</p>
            </div>

            <div class="service-card">
                <div class="service-icon">💼</div>
                <h3>Asesmen Karyawan & Rekrutmen</h3>
                <p>Tes psikologi untuk rekrutmen, promosi jabatan, dan pengembangan SDM perusahaan.</p>
            </div>
        </div>
    </section>

    <!-- TENTANG KAMI -->
    <section class="about">
        <h2>Tentang Kami</h2>

        <div class="about-container">
            <div class="about-img">
                <img src="img/psikolog.png" alt="Tim H-Deeja">
            </div>

            <div class="about-text">
                <p>
                    H-Deeja Psychology Center adalah pusat layanan psikologi yang berfokus pada peningkatan kesehatan mental masyarakat.
                    Kami menyediakan berbagai layanan seperti konseling anak, remaja, dewasa, keluarga, terapi psikologi, pendampingan ABK,
                    tes psikologi, seminar parenting, asesmen karyawan, dan outbound training.
                </p>

                <p>
                    Dengan tenaga profesional bersertifikat, kami berkomitmen memberikan pelayanan yang ramah, profesional,
                    dan berbasis etika psikologi.
                </p>

                <p class="info">
                    <strong>Senin – Jumat :</strong> 08.00 – 17.00<br>
                    <strong>Alamat :</strong> Jl. Serma Abdullah No.239, Bojonegoro, Jawa Timur
                </p>

                <a href="#" class="btn-secondary">Hubungi Kami</a>
            </div>
        </div>
    </section>

    <!-- TIM PSIKOLOG -->
    <section class="team">
        <h2>Tim Psikolog</h2>

        <div class="team-container">
            <div class="team-card">
                <img src="img/psikolog.png" alt="Psikolog 1">
                <h3>Rizal Ahmad, M.Psi</h3>
            </div>

            <div class="team-card">
                <img src="img/terapis.png" alt="Psikolog 2">
                <h3>Dr. Sarah Wijaya, M.Psi</h3>
            </div>
        </div>

        <a href="auth/login.php" class="btn-primary center">Daftar Konseling Sekarang</a>
    </section>

</body>
</html>