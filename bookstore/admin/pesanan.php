<?php
session_start();
include '../config.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Logika Update Status
if (isset($_POST['update_status'])) {
    $id_order = mysqli_real_escape_string($conn, $_POST['id_order']);
    $status_baru = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE orders SET status = '$status_baru' WHERE id = '$id_order'");
    echo "<script>alert('Status berhasil diperbarui!'); window.location.href='pesanan.php';</script>";
    exit;
}

// Logika Hapus Pesanan
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM orders WHERE id = '$id'");
    header("Location: pesanan.php"); 
    exit;
}

// Ambil Data Pesanan
$result = mysqli_query($conn, "SELECT o.*, b.judul AS nama_buku, b.gambar AS cover 
                               FROM orders o 
                               LEFT JOIN buku b ON o.book_id = b.id_buku 
                               ORDER BY o.id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pesanan | Admin M&N Edition</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --bg-body: #0f172a;
            --sidebar-bg: #1e293b;
            --accent: #818cf8;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
            --success: #10b981;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-body); color: var(--text-main); display: flex; }

        .sidebar { width: 280px; position: fixed; height: 100vh; }
        .main-content { margin-left: 280px; flex-grow: 1; padding: 50px; width: calc(100% - 280px); }
        
        h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 8px; }
        h1 span { color: var(--accent); }
        .subtitle { color: var(--text-dim); margin-bottom: 40px; }

        .table-card { 
            background: var(--card-bg); border-radius: 24px; padding: 30px; 
            border: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(10px);
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: var(--text-dim); font-size: 0.75rem; text-transform: uppercase; border-bottom: 1px solid rgba(255,255,255,0.1); }
        td { padding: 20px 15px; border-bottom: 1px solid rgba(255,255,255,0.03); font-size: 0.9rem; }

        .badge { padding: 5px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .status-paid { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .status-shipped { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        
        select { background: #0f172a; border: 1px solid rgba(255,255,255,0.1); color: white; padding: 6px; border-radius: 8px; font-size: 0.8rem; outline: none; }
        .btn-check { background: var(--accent); border: none; color: white; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; transition: 0.3s; }
        .btn-check:hover { transform: scale(1.1); }
        .btn-delete { color: #f87171; text-decoration: none; margin-left: 10px; font-size: 1.1rem; }

        .btn-view-bukti { display: inline-block; margin-top: 5px; color: var(--accent); text-decoration: none; font-size: 0.75rem; font-weight: 700; }
        .btn-view-bukti:hover { text-decoration: underline; }

        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); justify-content: center; align-items: center; }
        .modal-content { max-width: 90%; max-height: 90%; border-radius: 12px; }
        .close-modal { position: absolute; top: 20px; right: 30px; color: white; font-size: 40px; cursor: pointer; }
    </style>
</head>
<body>
    
    <?php include '../include/sidebar_admin.php'; ?>

    <main class="main-content">
        <h1>Daftar <span>Pesanan</span></h1>
        <p class="subtitle">Verifikasi bukti pembayaran dan kelola status transaksi</p>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Member</th>
                        <th>Buku</th>
                        <th>Info Kontak</th>
                        <th>Metode & Bukti</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>#ORD-<?= $row['id']; ?></td>
                        <td><b><?= htmlspecialchars($row['nama'] ?? ''); ?></b></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <img src="../assets/image/books/<?= $row['cover']; ?>" style="width:40px; height:55px; border-radius:6px; object-fit:cover;">
                                <span style="font-size: 0.8rem;"><?= htmlspecialchars($row['nama_buku'] ?? 'Buku dihapus'); ?></span>
                            </div>
                        </td>
                        <td style="font-size:0.75rem; color:var(--text-dim);">
                            <?= htmlspecialchars($row['phone'] ?? '-'); ?><br>
                            <?= htmlspecialchars($row['alamat'] ?? '-'); ?>
                        </td>
                        <td>
                            <span style="font-weight: 600;"><?= htmlspecialchars($row['metode_bayar'] ?? 'N/A'); ?></span><br>
                            <?php if (!empty($row['bukti_pembayaran'])): ?>
                                <a href="javascript:void(0)" 
                                   onclick="showModal('../assets/image/bukti_bayar/<?= $row['bukti_pembayaran']; ?>')" 
                                   class="btn-view-bukti">
                                   <i class="fa-solid fa-image"></i> Lihat Bukti
                                </a>
                            <?php else: ?>
                                <small style="color:var(--text-dim); font-style:italic;">No upload (COD)</small>
                            <?php endif; ?>
                        </td>
                        <td><b style="color:var(--accent)">Rp <?= number_format($row['total_amount'],0,',','.'); ?></b></td>
                        <td>
                            <span class="badge status-<?= $row['status']; ?>">
                                <?= strtoupper($row['status'] ?? 'PENDING'); ?>
                            </span>
                        </td>
                        <td>
                            <form action="" method="POST" style="display:flex; align-items:center; gap: 5px;">
                                <input type="hidden" name="id_order" value="<?= $row['id']; ?>">
                                <select name="status">
                                    <option value="pending" <?= $row['status']=='pending'?'selected':''; ?>>Pending</option>
                                    <option value="paid" <?= $row['status']=='paid'?'selected':''; ?>>Paid</option>
                                    <option value="shipped" <?= $row['status']=='shipped'?'selected':''; ?>>Shipped</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-check" title="Update Status">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                                <a href="?delete=<?= $row['id']; ?>" class="btn-delete" onclick="return confirm('Hapus pesanan ini?')">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="buktiModal" class="modal">
        <span class="close-modal" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="imgBukti">
    </div>

    <script>
        function showModal(imgSrc) {
            document.getElementById('buktiModal').style.display = "flex";
            document.getElementById('imgBukti').src = imgSrc;
        }
        function closeModal() {
            document.getElementById('buktiModal').style.display = "none";
        }
        window.onclick = function(event) {
            const modal = document.getElementById('buktiModal');
            if (event.target == modal) { closeModal(); }
        }
    </script>
</body>
</html>