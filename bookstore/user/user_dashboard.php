<?php
session_start();
include '../config.php';

// 1. Proteksi Halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') { 
    header("Location: ../login.php"); 
    exit; 
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; 
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Member';

// --- FITUR 1: LOGIKA TAMBAH KE KERANJANG ---
if (isset($_POST['add_to_cart']) && $user_id != 0) {
    $bid = mysqli_real_escape_string($conn, $_POST['id_buku']);
    
    // Cek dulu stoknya ada atau nggak sebelum masuk keranjang
    $cek_stok = mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku = '$bid'");
    $s = mysqli_fetch_assoc($cek_stok);

    if ($s['stok'] > 0) {
        $cek_cart = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND id_buku = '$bid'");
        if (mysqli_num_rows($cek_cart) > 0) {
            mysqli_query($conn, "UPDATE cart SET qty = qty + 1 WHERE user_id = '$user_id' AND id_buku = '$bid'");
        } else {
            mysqli_query($conn, "INSERT INTO cart (user_id, id_buku, qty) VALUES ('$user_id', '$bid', 1)");
        }
        header("Location: cart.php");
        exit;
    } else {
        echo "<script>alert('Maaf, stok baru saja habis!'); window.location.href='user_dashboard.php';</script>";
        exit;
    }
}

// --- FITUR 2: LOGIKA TOGGLE FAVORIT ---
if (isset($_POST['toggle_favorite']) && $user_id != 0) {
    $bid = mysqli_real_escape_string($conn, $_POST['id_buku']);
    $cek_fav = mysqli_query($conn, "SELECT * FROM favorites WHERE user_id = '$user_id' AND id_buku = '$bid'");
    
    if (mysqli_num_rows($cek_fav) > 0) {
        mysqli_query($conn, "DELETE FROM favorites WHERE user_id = '$user_id' AND id_buku = '$bid'");
    } else {
        mysqli_query($conn, "INSERT INTO favorites (user_id, id_buku) VALUES ('$user_id', '$bid')");
    }
    
    $redirect = "user_dashboard.php?";
    if(isset($_GET['search'])) $redirect .= "search=".$_GET['search']."&";
    if(isset($_GET['kategori'])) $redirect .= "kategori=".$_GET['kategori'];
    header("Location: " . rtrim($redirect, '&?'));
    exit;
}

// --- FITUR 3: AMBIL DATA ---
$list_kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filter_kat = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';

// Pastikan buku.* dipanggil agar kolom 'stok' terbawa
$query_str = "SELECT buku.*, kategori.nama_kategori 
              FROM buku 
              LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori WHERE 1=1";

if (!empty($search)) {
    $query_str .= " AND (buku.judul LIKE '%$search%' OR buku.penulis LIKE '%$search%')";
}
if (!empty($filter_kat)) {
    $query_str .= " AND buku.id_kategori = '$filter_kat'";
}

$query_str .= " ORDER BY buku.id_buku DESC";
$query_buku = mysqli_query($conn, $query_str);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Member | M&N Edition</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --bg-dark: #020617;
            --sidebar-bg: #0f172a;
            --accent-blue: #38bdf8;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-dark); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }

        .main-content { margin-left: 280px; width: calc(100% - 280px); padding: 50px; transition: 0.3s; }
        
        .welcome-section { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
        .filter-wrapper { display: flex; flex-direction: column; gap: 15px; width: 350px; }
        .search-box { background: var(--card-bg); padding: 12px 20px; border-radius: 12px; display: flex; align-items: center; gap: 12px; border: 1px solid rgba(255,255,255,0.05); }
        .search-box input { background: none; border: none; color: white; outline: none; width: 100%; font-size: 0.9rem; }
        .category-select { background: var(--card-bg); color: #fff; border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 12px; outline: none; cursor: pointer; }

        .book-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 25px; }
        .book-item { background: var(--card-bg); padding: 15px; border-radius: 24px; border: 1px solid rgba(255,255,255,0.05); position: relative; transition: 0.3s; }
        .book-item:hover { transform: translateY(-8px); border-color: var(--accent-blue); box-shadow: 0 10px 30px rgba(56, 189, 248, 0.1); }
        .book-cover { width: 100%; height: 280px; border-radius: 18px; object-fit: cover; margin-bottom: 15px; cursor: pointer; transition: 0.3s; }

        .fav-form { position: absolute; top: 25px; right: 25px; z-index: 10; }
        .fav-btn { background: rgba(0,0,0,0.6); color: white; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 1px solid rgba(255,255,255,0.2); transition: 0.2s; }
        .fav-btn.active { color: #ff4757; border-color: #ff4757; background: rgba(255, 71, 87, 0.1); }
        .kategori-badge { position: absolute; top: 25px; left: 25px; background: var(--accent-blue); color: #020617; padding: 5px 12px; border-radius: 10px; font-size: 0.65rem; font-weight: 800; z-index: 5; text-transform: uppercase; }
        
        .add-btn { width: 100%; background: var(--accent-blue); color: #020617; border: none; padding: 12px; border-radius: 12px; margin-top: 15px; font-weight: 800; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .add-btn:hover:not(:disabled) { background: #7dd3fc; }
        .add-btn:disabled { background: #475569; cursor: not-allowed; opacity: 0.7; }

        .book-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(2, 6, 23, 0.9); backdrop-filter: blur(10px); justify-content: center; align-items: center; }
        .modal-content-wrapper { background: #1e293b; width: 90%; max-width: 650px; padding: 40px; border-radius: 32px; position: relative; border: 1px solid rgba(255,255,255,0.1); }
        .close-detail { position: absolute; top: 25px; right: 25px; color: var(--text-dim); font-size: 30px; cursor: pointer; }
        .modal-body { display: flex; gap: 30px; }
        .modal-img { width: 220px; height: 320px; object-fit: cover; border-radius: 20px; }
        .modal-desc-text { flex: 1; }
        .modal-price { font-size: 1.5rem; font-weight: 800; color: #10b981; margin-top: 15px; }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; width: 100%; padding: 20px; }
            .modal-body { flex-direction: column; align-items: center; text-align: center; }
        }
    </style>
</head>
<body>

    <?php include '../include/sidebar_user.php'; ?>

    <main class="main-content">
        <div class="welcome-section">
            <div class="welcome-text">
                <h1 style="font-size: 2rem;">Halo, <?= htmlspecialchars($username); ?>! 👋</h1>
                <p style="color: var(--text-dim);">Temukan koleksi buku terbaik untukmu hari ini.</p>
            </div>

            <form method="GET" action="" class="filter-wrapper">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="search" placeholder="Cari judul atau penulis..." value="<?= htmlspecialchars($search); ?>">
                </div>
                <select name="kategori" class="category-select" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php while($kat = mysqli_fetch_assoc($list_kategori)): ?>
                        <option value="<?= $kat['id_kategori']; ?>" <?= ($filter_kat == $kat['id_kategori']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($kat['nama_kategori']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>

        <div class="book-grid">
            <?php while($b = mysqli_fetch_assoc($query_buku)): 
                $original_desc = $b['deskripsi'];
                $auto_desc = "Buku luar biasa berjudul '" . addslashes($b['judul']) . "' karya " . addslashes($b['penulis']) . ".";
                $final_desc = (!empty($original_desc)) ? addslashes(htmlspecialchars($original_desc)) : $auto_desc;
            ?>
            <div class="book-item">
                <span class="kategori-badge"><?= htmlspecialchars($b['nama_kategori']); ?></span>

                <form action="" method="POST" class="fav-form">
                    <input type="hidden" name="id_buku" value="<?= $b['id_buku']; ?>">
                    <?php 
                        $bid = $b['id_buku'];
                        $is_fav = mysqli_num_rows(mysqli_query($conn, "SELECT id_fav FROM favorites WHERE user_id = '$user_id' AND id_buku = '$bid'")) > 0;
                    ?>
                    <button type="submit" name="toggle_favorite" class="fav-btn <?= $is_fav ? 'active' : ''; ?>">
                        <i class="<?= $is_fav ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                    </button>
                </form>
              
                <img src="../assets/image/books/<?= htmlspecialchars($b['gambar']); ?>" 
                     class="book-cover" 
                     onclick="openDetail(
                        '<?= addslashes(htmlspecialchars($b['judul'])); ?>', 
                        '<?= addslashes(htmlspecialchars($b['penulis'])); ?>', 
                        '<?= $final_desc; ?>', 
                        '../assets/image/books/<?= $b['gambar']; ?>',
                        'Rp <?= number_format($b['harga'], 0, ',', '.'); ?>',
                        '<?= $b['stok']; ?>'
                     )">
                
                <div class="book-info">
                    <h4 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 5px;"><?= htmlspecialchars($b['judul']); ?></h4>
                    <p style="color: var(--text-dim); font-size: 0.8rem; margin-bottom: 5px;">Karya: <?= htmlspecialchars($b['penulis']); ?></p>
                    
                    <p style="color: <?= ($b['stok'] > 0) ? 'var(--text-dim)' : '#f87171'; ?>; font-size: 0.75rem; margin-bottom: 10px;">
                        Sisa Stok: <?= $b['stok']; ?>
                    </p>

                    <p style="color: var(--accent-blue); font-weight: 800;">Rp <?= number_format($b['harga'], 0, ',', '.'); ?></p>
                </div>

                <form action="" method="POST">
                    <input type="hidden" name="id_buku" value="<?= $b['id_buku']; ?>">
                    <?php if($b['stok'] > 0): ?>
                        <button type="submit" name="add_to_cart" class="add-btn">
                            <i class="fa-solid fa-cart-shopping"></i> Beli Sekarang
                        </button>
                    <?php else: ?>
                        <button type="button" class="add-btn" disabled>
                            <i class="fa-solid fa-circle-xmark"></i> Stok Habis
                        </button>
                    <?php endif; ?>
                </form>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <div id="bookDetailModal" class="book-modal">
        <div class="modal-content-wrapper">
            <span class="close-detail" onclick="closeDetail()">&times;</span>
            <div class="modal-body">
                <img id="m-img" src="" class="modal-img">
                <div class="modal-desc-text">
                    <h2 id="m-judul">Judul Buku</h2>
                    <span id="m-penulis" class="author" style="color: var(--accent-blue); font-weight: 600; display: block; margin-bottom: 15px;">Nama Penulis</span>
                    <p id="m-deskripsi" style="color: var(--text-dim); font-size: 0.95rem; line-height: 1.6;"></p>
                    <div id="m-harga" class="modal-price">Harga</div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function openDetail(judul, penulis, deskripsi, gambar, harga, stok) {
        document.getElementById('m-judul').innerText = judul;
        document.getElementById('m-penulis').innerText = "Karya: " + penulis;
        
        // Gabungkan deskripsi dengan info stok di modal
        let warnaStok = stok > 0 ? '#38bdf8' : '#f87171';
        let htmlDeskripsi = deskripsi + `<br><br><b style="color: ${warnaStok}">Sisa Stok: ${stok}</b>`;
        document.getElementById('m-deskripsi').innerHTML = htmlDeskripsi;
        
        document.getElementById('m-img').src = gambar;
        document.getElementById('m-harga').innerText = harga;
        
        document.getElementById('bookDetailModal').style.display = "flex";
        document.body.style.overflow = "hidden";
    }

    function closeDetail() {
        document.getElementById('bookDetailModal').style.display = "none";
        document.body.style.overflow = "auto";
    }

    window.onclick = function(event) {
        let modal = document.getElementById('bookDetailModal');
        if (event.target == modal) { closeDetail(); }
    }
    </script>

</body>
</html>