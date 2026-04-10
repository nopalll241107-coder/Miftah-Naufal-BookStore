<?php
include 'config.php'; // Hubungkan ke koneksi database lo

// Ambil data buku terbaru
$query_buku = mysqli_query($conn, "SELECT * FROM buku ORDER BY id_buku DESC LIMIT 10");
$buku_array = [];
while($row = mysqli_fetch_assoc($query_buku)) {
    $buku_array[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookstore Premium | M&N Edition</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #020617;
            --card-glass: rgba(30, 41, 59, 0.5);
            --accent-blue: #38bdf8;
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: 'Inter', sans-serif; 
            background: radial-gradient(circle at top right, #1e1b4b, #020617);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.6;
        }

        .wrapper { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 0 25px; 
        }

        /* --- NAVBAR --- */
        nav {
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            padding: 40px 0;
        }

        .brand-container {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .logo { 
            font-size: 2rem; 
            font-weight: 800; 
            letter-spacing: -1.5px; 
            line-height: 1;
        }
        .logo span { color: var(--accent-blue); text-shadow: 0 0 15px rgba(56, 189, 248, 0.4); }

        .sub-logo {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 5px;
            color: var(--text-dim);
            text-transform: uppercase;
            display: flex;
            align-items: center;
        }

        .sub-logo::after {
            content: '';
            width: 30px;
            height: 1px;
            background: var(--accent-blue);
            margin-left: 10px;
            box-shadow: 0 0 10px var(--accent-blue);
        }

        /* --- HERO SECTION --- */
        .hero {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            align-items: center;
            gap: 60px;
            padding: 60px 0 100px;
        }

        .hero-content h1 {
            font-size: 4rem;
            line-height: 1;
            margin-bottom: 25px;
            font-weight: 800;
        }

        .hero-content h1 span {
            display: block;
            background: linear-gradient(to right, #fff, var(--accent-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-content p {
            font-size: 1.1rem;
            color: var(--text-dim);
            max-width: 500px;
            margin-bottom: 40px;
        }

        /* --- BUTTONS --- */
        .btn-group { display: flex; gap: 20px; }
        
        .btn {
            padding: 16px 35px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.4s ease;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--accent-blue);
            color: #020617;
            box-shadow: 0 10px 25px rgba(56, 189, 248, 0.2);
        }
        .btn-primary:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 15px 35px rgba(56, 189, 248, 0.4); 
        }

        .btn-outline {
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            backdrop-filter: blur(5px);
        }
        .btn-outline:hover { background: rgba(255,255,255,0.05); border-color: #fff; }

        /* --- CAROUSEL --- */
        .carousel-wrapper {
            background: var(--card-glass);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 40px;
            padding: 40px;
            overflow: hidden;
            backdrop-filter: blur(15px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
            position: relative;
        }

        .carousel-title {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--accent-blue);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 30px;
            display: block;
            text-align: center;
        }

        .carousel-track {
            display: flex;
            width: max-content; 
            animation: infiniteScroll 30s linear infinite;
        }

        .book-card {
            width: 180px;
            margin: 0 15px;
            flex-shrink: 0;
            transition: 0.4s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .book-cover {
            width: 100%;
            height: 250px;
            background: #1e293b;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.05);
            margin-bottom: 15px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .book-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .book-card:hover { transform: scale(1.05) translateY(-10px); }

        @keyframes infiniteScroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .carousel-track:hover { animation-play-state: paused; }

        @media (max-width: 968px) {
            .hero { grid-template-columns: 1fr; text-align: center; }
            .hero-content h1 { font-size: 3rem; }
            .hero-content p { margin: 0 auto 30px; }
            .btn-group { justify-content: center; }
            .brand-container { align-items: center; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <nav>
        <div class="brand-container">
            <div class="logo">BOOK<span>STORE</span></div>
            <div class="sub-logo">M&N Edition</div>
        </div>
        <a href="login.php" class="btn-outline" style="padding: 10px 20px; font-size: 0.8rem; border-radius: 8px;">Sign In</a>
    </nav>

    <section class="hero">
        <div class="hero-content">
            <h1>Eksplorasi Dunia <span>Tanpa Batas.</span></h1>
            <p>Platform perpustakaan digital premium dengan koleksi terbaik untuk menunjang wawasan Anda.</p>
            
            <div class="btn-group">
                <a href="user/register.php" class="btn btn-primary">Daftar Sekarang</a>
                <a href="login.php" class="btn btn-outline">Masuk Dashboard</a>
            </div>
        </div>

        <div class="carousel-wrapper">
            <span class="carousel-title">-- NEW COLLECTIONS --</span>
            <div class="carousel-track">
                <?php 
                // Duplikasi data untuk efek infinite scroll
                for ($i = 0; $i < 2; $i++): 
                    foreach ($buku_array as $buku): 
                ?>
                <a href="login.php" class="book-card">
                    <div class="book-cover" style="border-bottom: 4px solid var(--accent-blue);">
                        <img src="assets/image/books/<?= $buku['gambar']; ?>" alt="<?= $buku['judul']; ?>">
                    </div>
                    <p style="font-size: 0.8rem; text-align: center; font-weight: 600;"><?= $buku['judul']; ?></p>
                    <p style="font-size: 0.7rem; text-align: center; color: var(--accent-blue);">Rp <?= number_format($buku['harga'], 0, ',', '.'); ?></p>
                </a>
                <?php 
                    endforeach; 
                endfor; 
                ?>
            </div>
        </div>
    </section>
</div>

</body>
</html>