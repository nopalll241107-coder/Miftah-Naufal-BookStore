<?php
session_start();
include '../config.php';

// 1. Proteksi Halaman (Cek apakah sudah login sebagai user)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') { 
    header("Location: ../login.php"); 
    exit; 
}

// 2. Definisi variabel agar tidak error di baris 11
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Logika Hapus dari Favorit jika tombol hati diklik lagi
if (isset($_POST['remove_fav'])) {
    $bid = $_POST['id_buku'];
    mysqli_query($conn, "DELETE FROM favorites WHERE user_id = '$user_id' AND id_buku = '$bid'");
    header("Location: favorites.php");
    exit;
}

// 3. Ambil data buku favorit berdasarkan user_id yang login
$query_fav = mysqli_query($conn, "SELECT buku.*, kategori.nama_kategori 
                                  FROM favorites 
                                  JOIN buku ON favorites.id_buku = buku.id_buku 
                                  LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori
                                  WHERE favorites.user_id = '$user_id'
                                  ORDER BY favorites.id_fav DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Koleksi Favorit | M&N Edition</title>
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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-dark); color: var(--text-main); display: flex; min-height: 100vh; }

        /* SIDEBAR */
        .sidebar { width: 280px; background: var(--sidebar-bg); height: 100vh; position: fixed; padding: 40px 25px; display: flex; flex-direction: column; border-right: 1px solid rgba(255,255,255,0.1); }
        .brand h2 { font-size: 1.5rem; font-weight: 800; text-transform: uppercase; }
        .brand span { color: var(--accent-blue); }
        .brand p { font-size: 0.65rem; color: var(--text-dim); letter-spacing: 2px; font-weight: 600; margin-top: -5px; }

        .nav-menu { list-style: none; flex: 1; margin-top: 40px; }
        .nav-link { display: flex; align-items: center; gap: 15px; padding: 14px 20px; color: var(--text-dim); text-decoration: none; border-radius: 12px; margin-bottom: 10px; transition: 0.3s; font-weight: 600; }
        .nav-link:hover, .nav-link.active { background: rgba(56, 189, 248, 0.1); color: var(--accent-blue); }

        .logout-link { color: #ef4444; text-decoration: none; font-weight: 700; display: flex; align-items: center; gap: 10px; padding: 14px 20px; margin-top: auto; border-radius: 12px; }

        /* MAIN CONTENT */
        .main-content { margin-left: 280px; width: calc(100% - 280px); padding: 50px; }
        h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 10px; }
        h1 span { color: var(--accent-blue); }
        .subtitle { color: var(--text-dim); margin-bottom: 40px; font-size: 1.1rem; }

        /* GRID */
        .book-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 30px; }
        .book-item { background: var(--card-bg); padding: 18px; border-radius: 26px; border: 1px solid rgba(255,255,255,0.05); position: relative; transition: 0.4s; }
        .book-item:hover { transform: translateY(-10px); border-color: var(--accent-blue); }
        
        .book-cover { width: 100%; height: 290px; border-radius: 20px; object-fit: cover; margin-bottom: 15px; }
        .book-info h4 { font-size: 1.1rem; font-weight: 700; margin-bottom: 5px; }
        .price-tag { color: var(--accent-blue); font-weight: 800; font-size: 1.2rem; }

        .btn-remove { position: absolute; top: 30px; right: 30px; background: rgba(239, 68, 68, 0.2); color: #ef4444; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(8px); }
        .btn-remove:hover { background: #ef4444; color: white; transform: scale(1.1); }
        
        .empty-state { text-align: center; padding: 100px 0; grid-column: 1 / -1; }
        .empty-state i { font-size: 5rem; color: var(--text-dim); margin-bottom: 25px; display: block; opacity: 0.2; }
        .empty-state h3 { font-size: 1.5rem; color: var(--text-main); margin-bottom: 10px; }
    </st:root>
</head>
<body>

    <?php include '../include/sidebar_user.php'; ?>

    <main class="main-content">
        <h1>Koleksi <span>Favorit</span></h1>
        <p class="subtitle">Buku-buku yang kamu tandai dengan hati.</p>

        <div class="book-grid">
            <?php if(mysqli_num_rows($query_fav) > 0): ?>
                <?php while($f = mysqli_fetch_assoc($query_fav)): ?>
                <div class="book-item">
                    <form action="" method="POST">
                        <input type="hidden" name="id_buku" value="<?= $f['id_buku']; ?>">
                        <button type="submit" name="remove_fav" class="btn-remove" title="Hapus dari favorit">
                            <i class="fa-solid fa-heart"></i>
                        </button>
                    </form>

                    <img src="../assets/image/books/<?= htmlspecialchars($f['gambar']); ?>" class="book-cover">
                    <div class="book-info">
                        <h4><?= htmlspecialchars($f['judul']); ?></h4>
                        <p style="color: var(--text-dim); font-size: 0.85rem; margin-bottom: 10px;"><?= htmlspecialchars($f['penulis']); ?></p>
                        <p class="price-tag">Rp <?= number_format($f['harga'], 0, ',', '.'); ?></p>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-heart-circle-xmark"></i>
                    <h3>Belum ada favorit</h3>
                    <p style="color: var(--text-dim);">Klik icon hati di dashboard untuk menambahkan koleksi di sini.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>