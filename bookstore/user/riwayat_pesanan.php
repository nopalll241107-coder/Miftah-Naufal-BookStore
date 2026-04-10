<?php
session_start();
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') { 
    header("Location: ../login.php"); 
    exit; 
}

$user_id = $_SESSION['user_id'];

// Query join ke tabel buku berdasarkan book_id di tabel orders
$query_pesanan = mysqli_query($conn, "SELECT orders.*, buku.judul, buku.gambar 
    FROM orders 
    JOIN buku ON orders.book_id = buku.id_buku 
    WHERE orders.user_id = '$user_id' 
    ORDER BY orders.order_date DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan | M&N Edition</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --bg: #020617;
            --sidebar-bg: #0f172a;
            --accent-blue: #38bdf8;
            --card-bg: #1e293b;
            --text-main: #f8fafc;   
            --text-dim: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg); 
            color: var(--text-main); 
            display: flex; 
            min-height: 100vh;
            overflow-x: hidden;
        }

        .main-content { 
            margin-left: 260px; 
            flex-grow: 1; 
            padding: 50px; 
            max-width: calc(100vw - 260px); 
        }

        .header-title h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 10px; }
        .header-title span { color: var(--accent-blue); }

        .order-card {
            background: var(--card-bg); 
            border-radius: 20px; 
            padding: 20px;
            border: 1px solid rgba(255,255,255,0.05); 
            margin-bottom: 20px;
            display: flex; 
            gap: 20px; 
            align-items: center;
            transition: 0.3s;
        }
        .order-card:hover { border-color: var(--accent-blue); }

        .book-cover { 
            width: 85px; 
            height: 120px; 
            object-fit: cover; 
            border-radius: 12px; 
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }

        .order-info { flex-grow: 1; }
        .order-info h3 { font-size: 1.2rem; margin-bottom: 5px; }
        .order-info p { font-size: 0.85rem; color: var(--text-dim); margin-bottom: 3px; }
        .order-info .address { margin-top: 8px; color: var(--text-main); opacity: 0.8; }
        
        .price-tag { 
            font-weight: 800; 
            color: var(--accent-blue); 
            font-size: 1.1rem; 
            display: block; 
            margin-top: 10px; 
        }

        /* WARNA STATUS BADGE OTOMATIS */
        .status-badge {
            padding: 6px 14px; 
            border-radius: 8px; 
            font-size: 0.7rem; 
            font-weight: 800;
            text-transform: uppercase;
            display: inline-block;
        }

        /* Status Pending (Kuning) */
        .status-pending { background: rgba(234, 179, 8, 0.1); color: #eab308; }
        /* Status Paid (Hijau) */
        .status-paid { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        /* Status Shipped (Biru) */
        .status-shipped { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        
        .action-area { text-align: right; min-width: 150px; }
        
        .chat-btn {
            display: inline-block; 
            margin-top: 15px; 
            background: #25D366; 
            color: white; 
            padding: 8px 15px; 
            border-radius: 10px; 
            font-size: 0.8rem; 
            font-weight: 700; 
            text-decoration: none;
            transition: 0.3s;
        }
        .chat-btn:hover { transform: scale(1.05); background: #1ebd5b; }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; max-width: 100%; padding: 20px; }
            .order-card { flex-direction: column; align-items: flex-start; }
            .action-area { text-align: left; width: 100%; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; }
        }
    </style>
</head>
<body>
<?php include '../include/sidebar_user.php'; ?>

<main class="main-content">
    <div class="header-title">
        <h1>Riwayat <span>Pesanan</span></h1>
        <p>Pantau status buku impianmu di sini.</p>
    </div>

    <div style="margin-top: 40px;">
        <?php if(mysqli_num_rows($query_pesanan) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($query_pesanan)): ?>
                <div class="order-card">
                    <img src="../assets/image/books/<?= htmlspecialchars($row['gambar']); ?>" class="book-cover" alt="cover">
                    
                    <div class="order-info">
                        <p style="color: var(--accent-blue); font-weight: 800; font-size: 0.75rem;">ID ORDER: #<?= $row['id']; ?></p>
                        <h3><?= htmlspecialchars($row['judul']); ?></h3>
                        <p><i class="fa-regular fa-calendar" style="margin-right: 5px;"></i> <?= date('d M Y, H:i', strtotime($row['order_date'])); ?></p>
                        
                        <p class="address">
                            <i class="fa-solid fa-location-dot" style="margin-right: 5px; color: var(--accent-blue);"></i> 
                            <?= htmlspecialchars($row['alamat']); ?>
                        </p>

                        <span class="price-tag">Rp <?= number_format($row['total_amount'], 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="action-area">
                        <div class="status-badge status-<?= strtolower($row['status']); ?>">
                            <?= strtoupper($row['status']); ?>
                        </div>
                        
                        <p style="font-size: 0.7rem; color: var(--text-dim); margin-top: 10px;">Metode: <?= $row['metode_bayar']; ?></p>
                        <a href="https://wa.me/62895337475348?text=Halo%20Admin,%20saya%20mau%20tanya%20order%20#<?= $row['id']; ?>" 
                           target="_blank" class="chat-btn">
                           <i class="fa-brands fa-whatsapp"></i> Chat Admin
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 100px 0; opacity: 0.5;">
                <i class="fa-solid fa-receipt" style="font-size: 4rem; margin-bottom: 20px;"></i>
                <h2>Belum ada pesanan</h2>
            </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>