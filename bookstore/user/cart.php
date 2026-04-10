<?php
session_start();
include '../config.php';

// 1. Proteksi Login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') { 
    header("Location: ../login.php"); 
    exit; 
}

$user_id = $_SESSION['user_id']; 

// 2. LOGIKA: UPDATE QTY DENGAN VALIDASI STOK
if (isset($_POST['update_qty'])) {
    $id_cart = $_POST['id_cart'];
    $new_qty = (int)$_POST['qty'];
    
    $query_stok = mysqli_query($conn, "SELECT buku.stok FROM cart 
                                       JOIN buku ON cart.id_buku = buku.id_buku 
                                       WHERE cart.id_cart = '$id_cart'");
    $data_stok = mysqli_fetch_assoc($query_stok);
    
    if ($data_stok) {
        $stok_tersedia = $data_stok['stok'];
        if ($new_qty > 0) {
            $final_qty = ($new_qty <= $stok_tersedia) ? $new_qty : $stok_tersedia;
            mysqli_query($conn, "UPDATE cart SET qty = '$final_qty' WHERE id_cart = '$id_cart'");
        }
    }
    header("Location: cart.php");
    exit;
}

// 3. LOGIKA: HAPUS ITEM
if (isset($_GET['delete'])) {
    $id_cart = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM cart WHERE id_cart = '$id_cart' AND user_id = '$user_id'");
    header("Location: cart.php");
    exit;
}

// 4. AMBIL DATA KERANJANG
$query_cart = mysqli_query($conn, "SELECT cart.*, buku.judul, buku.harga, buku.gambar, buku.stok 
                                   FROM cart 
                                   LEFT JOIN buku ON cart.id_buku = buku.id_buku 
                                   WHERE cart.user_id = '$user_id'");
?>

<!DOCTYPE html>btn-explore<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja | M&N Edition</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --bg: #020617;
            --sidebar-bg: #0f172a;
            --accent-blue: #38bdf8;
            --card: #0f172a;
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text-main); display: flex; min-height: 100vh; }

        /* SIDEBAR */
               
        /* CONTENT */
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 60px; }
        .header-title h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 10px; }
        .header-title span { color: var(--accent-blue); }
        .header-title p { color: var(--text-dim); margin-bottom: 40px; }

        .cart-grid { display: grid; grid-template-columns: 1fr 380px; gap: 30px; align-items: start; }
        .cart-list { background: rgba(15, 23, 42, 0.5); border-radius: 24px; padding: 30px; border: 1px solid rgba(255,255,255,0.05); }
        
        .item-row { display: flex; align-items: center; justify-content: space-between; padding: 20px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .item-row:last-child { border-bottom: none; }
        
        .book-detail { display: flex; align-items: center; gap: 20px; flex: 1; }
        .book-detail img { width: 70px; height: 100px; border-radius: 12px; object-fit: cover; }
        .book-info h4 { font-size: 1.1rem; margin-bottom: 5px; }

        .qty-box { display: flex; align-items: center; background: rgba(255,255,255,0.05); border-radius: 10px; padding: 5px; }
        .qty-btn { background: none; border: none; color: var(--text-main); width: 30px; height: 30px; cursor: pointer; border-radius: 8px; font-weight: bold; }
        .qty-btn:disabled { opacity: 0.2; cursor: not-allowed; }
        .qty-input { width: 40px; text-align: center; background: none; border: none; color: white; font-weight: 700; }

        .price-sub { font-weight: 700; color: var(--text-main); min-width: 120px; text-align: right; font-size: 1.1rem; }

        /* SUMMARY CARD */
        .summary-card { background: #0f172a; border-radius: 24px; padding: 30px; border: 1px solid rgba(56, 189, 248, 0.2); position: sticky; top: 40px; }
        .summary-card h3 { margin-bottom: 25px; font-size: 1.3rem; }
        
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 15px; color: var(--text-dim); font-size: 0.95rem; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 1px dashed rgba(255,255,255,0.1); }
        .summary-total span:last-child { color: var(--accent-blue); font-size: 1.4rem; font-weight: 800; }

        .btn-checkout { width: 100%; padding: 18px; background: var(--accent-blue); color: #ffffff; border: none; border-radius: 16px; font-weight: 800; cursor: pointer; margin-top: 25px; transition: 0.3s; font-size: 1rem; }
        .btn-checkout:hover { background: #7dd3fc; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(56, 189, 248, 0.2); }
        
        .empty-state { text-align: center; padding: 80px 40px; background: rgba(113, 115, 118, 0.3); border-radius: 30px; border: 1px dashed rgba(255, 255, 255, 0.1); }
        .btn-explore { display: inline-block; padding: 15px 35px; background: var(--accent-blue); color: #020617; text-decoration: none; border-radius: 12px; font-weight: 800; margin-top: 25px; }
    </style>
</head>
<body>

  <?php include '../include/sidebar_user.php'; ?>

    <main class="main-content">
        <div class="header-title">
            <h1>Keranjang <span>Kamu</span></h1>
            <p>Ada item menarik yang siap kamu miliki hari ini.</p>
        </div>

        <?php if(mysqli_num_rows($query_cart) > 0): ?>
        <div class="cart-grid">
            <div class="cart-list">
                <?php 
                $subtotal = 0;
                while($item = mysqli_fetch_assoc($query_cart)): 
                    if (!$item['judul']) continue;
                    $price_item = $item['harga'] * $item['qty'];
                    $subtotal += $price_item;
                ?>
                <div class="item-row">
                    <div class="book-detail">
                       <img src="../assets/image/books/<?= htmlspecialchars($item['gambar']); ?>" alt="Cover">
                        <div class="book-info">
                            <h4><?= $item['judul']; ?></h4>
                            <p style="font-size: 0.8rem; color: var(--text-dim);">Harga: Rp <?= number_format($item['harga'], 0, ',', '.'); ?></p>
                        </div>
                    </div>

                    <form action="" method="POST" class="qty-box">
                        <input type="hidden" name="id_cart" value="<?= $item['id_cart']; ?>">
                        <button type="submit" name="update_qty" class="qty-btn" onclick="this.form.qty.value--" <?= ($item['qty'] <= 1) ? 'disabled' : ''; ?>>-</button>
                        <input type="text" name="qty" class="qty-input" value="<?= $item['qty']; ?>" readonly>
                        <button type="submit" name="update_qty" class="qty-btn" onclick="this.form.qty.value++" <?= ($item['qty'] >= $item['stok']) ? 'disabled' : ''; ?>>+</button>
                    </form>

                    <div class="price-sub">Rp <?= number_format($price_item, 0, ',', '.'); ?></div>
                    <a href="?delete=<?= $item['id_cart']; ?>" onclick="return confirm('Hapus item?')" style="color: #ef4444; margin-left: 15px; opacity: 0.5;"><i class="fa-solid fa-trash-can"></i></a>
                </div>
                <?php endwhile; 
                
                $biaya_admin = 2000;
                $ongkir = 0; // Gratis
                $total_akhir = $subtotal + $biaya_admin + $ongkir;
                ?>
            </div>

            <div class="summary-side">
                <div class="summary-card">
                    <h3>Ringkasan Belanja</h3>
                    <div class="summary-item">
                        <span>Subtotal Produk</span>
                        <span>Rp <?= number_format($subtotal, 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Biaya Admin</span>
                        <span>Rp <?= number_format($biaya_admin, 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Biaya Pengiriman</span>
                        <span style="color: #22c55e; font-weight: 700;">Gratis</span>
                    </div>
                    
                    <div class="summary-total">
                        <span style="font-weight: 700;">Total Harga</span>
                        <span>Rp <?= number_format($total_akhir, 0, ',', '.'); ?></span>
                    </div>
                    
                    <a href="checkout.php" style="text-decoration: none;">
                        <button class="btn-checkout">Checkout Sekarang</button>
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fa-solid fa-basket-shopping" style="font-size: 4rem; color: var(--accent); opacity: 0.2; margin-bottom: 20px;"></i>
            <h2>Keranjang Masih Kosong</h2>
            <p style="color: var(--text-dim); margin-top: 10px;">Yuk, cari buku favoritmu dan mulai petualangan baru!</p>
            <a href="user_dashboard.php" class="btn-explore">Cari Buku</a>
        </div>
        <?php endif; ?>
    </main>

</body>
</html>