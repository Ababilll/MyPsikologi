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
        }

        .btn-login {
            padding: 8px 18px;
            border-radius: 25px;
            background: #dbeafe;
            color: #1e3a8a;
            text-decoration: none;
            font-size: 14px;
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
        }

        .center {
            display: block;
            margin: 30px auto 0 auto;
            width: fit-content;
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

            .about-container {
                flex-direction: column;
            }

            .team-container {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>

</head>
<body>

    <!-- NAVBAR -->
    <header class="navbar">
        <div class="logo">H-Deeja Psychology Center</div>
        <a href="auth/login.php" class="btn-login">Masuk</a>
    </header>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="overlay"></div>
        <div class="hero-content">
            <h1>Kamu Tidak Sendiri. <span>Kami Siap</span> Mendengarkan</h1>
            <a href="#" class="btn-primary">Mulai Konseling</a>
        </div>
    </section>

    <!-- TENTANG KAMI -->
    <section class="about">
        <h2>Tentang Kami</h2>

        <div class="about-container">
            <div class="about-img">
                <img src="img/psikolog.png" alt="">
            </div>

            <div class="about-text">
                <p>
                    H-Deeja Psychology Center adalah pusat layanan psikologi yang berfokus pada peningkatan kesehatan mental masyarakat.
                    Kami menyediakan berbagai layanan seperti: konsultasi anak, remaja, dewasa, keluarga, terapi psikologi, tes psikologi,
                    seminar, dan pelatihan.
                </p>

                <p>
                    Dengan tenaga profesional bersertifikat, 
                    H-Deeja Psychology Center berkomitmen memberikan pelayanan yang ramah, profesional, dan berbasis etika psikologi.
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
                <img src="img/psikolog.png" alt="">
                <h3>Nama Psikolog</h3>
            </div>

            <div class="team-card">
                <img src="img/terapis.png" alt="">
                <h3>Nama Terapis</h3>
            </div>

        </div>

        <a href="#" class="btn-primary center">Daftar Konseling</a>
    </section>

</body>
</html>
