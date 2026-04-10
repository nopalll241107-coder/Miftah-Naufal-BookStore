<?php
session_start();
include '../config.php';

// 1. Proteksi Login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') { 
    header("Location: ../login.php"); 
    exit; 
}

$user_id = $_SESSION['user_id'];

// 2. Ambil Data User (untuk info nama & hp otomatis)
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$data_user = mysqli_fetch_assoc($query_user);

// 3. Ambil Data Keranjang (Join dengan tabel buku)
$query_cart = mysqli_query($conn, "SELECT cart.*, buku.judul, buku.harga, buku.gambar 
                                    FROM cart 
                                    JOIN buku ON cart.id_buku = buku.id_buku 
                                    WHERE cart.user_id = '$user_id'");

// Jika keranjang kosong, balikkan ke halaman cart
if (mysqli_num_rows($query_cart) == 0) {
    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Checkout | M&N Edition</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --bg: #020617;
            --card: #0f172a;
            --accent: #38bdf8;
            --accent-gradient: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
            --border: rgba(255, 255, 255, 0.08);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg); 
            color: var(--text-main); 
            background-image: radial-gradient(circle at top right, rgba(56, 189, 248, 0.05), transparent);
            min-height: 100vh;
        }

        .main-content { margin-left: 280px; width: calc(100% - 280px); padding: 50px; }
        .checkout-container { max-width: 1050px; margin: 40px auto; }
        .top-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .back-btn { text-decoration: none; color: var(--text-dim); font-weight: 600; display: flex; align-items: center; gap: 8px; }
        
        .checkout-grid { display: grid; grid-template-columns: 1fr 400px; gap: 24px; align-items: start; }
        .glass-card { background: var(--card); border-radius: 20px; border: 1px solid var(--border); padding: 24px; }

        h3 { font-size: 0.9rem; margin-bottom: 20px; color: var(--accent); text-transform: uppercase; letter-spacing: 1px; }

        /* Form */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.75rem; color: var(--text-dim); margin-bottom: 6px; }
        .form-input { width: 100%; padding: 12px; background: rgba(0,0,0,0.3); border: 1px solid var(--border); border-radius: 12px; color: white; }

        /* Items List */
        .item-row { display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(255,255,255,0.02); border-radius: 14px; margin-bottom: 10px; }
        .item-row img { width: 40px; height: 55px; border-radius: 6px; object-fit: cover; }
        .item-info h4 { font-size: 0.85rem; }

        /* Summary & Payment */
        .summary-card { background: var(--accent-gradient); color: #020617; padding: 24px; border-radius: 24px; margin-bottom: 20px; }
        .calc-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-weight: 600; }
        .calc-row.total { border-top: 2px dashed rgba(0,0,0,0.1); padding-top: 15px; font-size: 1.6rem; font-weight: 900; }

        .payment-section { background: var(--card); border: 1px solid var(--border); border-radius: 24px; padding: 24px; }
        .payment-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 15px; }
        .payment-label { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 12px 5px; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 12px; cursor: pointer; }
        .payment-item input { display: none; }
        .payment-item input:checked + .payment-label { background: rgba(56, 189, 248, 0.1); border-color: var(--accent); color: var(--accent); }

        /* Area Upload */
        .payment-instruction { margin-top: 20px; background: rgba(255, 255, 255, 0.03); border: 1px dashed var(--accent); border-radius: 16px; padding: 15px; display: none; }
        .btn-confirm { width: 100%; padding: 18px; background: var(--accent-gradient); color: #020617; border: none; border-radius: 16px; font-weight: 800; cursor: pointer; margin-top: 20px; opacity: 0.5; cursor: not-allowed; }
    </style>
</head>
<body>
<?php include '../include/sidebar_user.php'; ?>
<main class="main-content">
    <div class="checkout-container">
        <div class="top-nav">
            <a href="cart.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i> Kembali</a>
            <div class="header-section"><h2>Final <span>Step</span></h2></div>
        </div>

        <form action="proses_pesanan.php" method="POST" enctype="multipart/form-data" class="checkout-grid">
            <div class="left-col">
                <div class="glass-card" style="margin-bottom: 24px;">
                    <h3><i class="fa-solid fa-map-location-dot"></i> Alamat Pengiriman</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Penerima</label>
                            <input type="text" name="nama" class="form-input" value="<?= $data_user['fullname']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>No. WhatsApp</label>
                            <input type="text" name="phone" class="form-input" value="<?= $data_user['phone']; ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat Lengkap</label>
                        <textarea name="alamat" class="form-input" rows="3" required></textarea>
                    </div>
                </div>

                <div class="glass-card">
                    <h3><i class="fa-solid fa-basket-shopping"></i> Review Produk</h3>
                    <div class="item-list">
                        <?php 
                        $subtotal = 0;
                        while($item = mysqli_fetch_assoc($query_cart)): 
                            $total_per_item = $item['harga'] * $item['qty'];
                            $subtotal += $total_per_item;
                        ?>
                        <div class="item-row">
                            <img src="../assets/image/books/<?= $item['gambar']; ?>">
                            <div class="item-info">
                                <h4><?= $item['judul']; ?></h4>
                                <p><?= $item['qty']; ?> x Rp <?= number_format($item['harga'], 0, ',', '.'); ?></p>
                            </div>
                            <div style="margin-left:auto; font-weight:700;">Rp <?= number_format($total_per_item, 0, ',', '.'); ?></div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <div class="right-col" style="position: sticky; top: 30px;">
                <div class="summary-card">
                    <h2>Pembayaran</h2>
                    <div class="calc-row"><span>Total Produk</span><span>Rp <?= number_format($subtotal, 0, ',', '.'); ?></span></div>
                    <div class="calc-row"><span>Biaya Admin</span><span>Rp 2.000</span></div>
                    <div class="calc-row total"><span>Total Bayar</span><span>Rp <?= number_format($subtotal + 2000, 0, ',', '.'); ?></span></div>
                    <input type="hidden" name="total_bayar" value="<?= $subtotal + 2000; ?>">
                </div>

                <div class="payment-section">
                    <h3><i class="fa-solid fa-credit-card"></i> Metode</h3>
                    <div class="payment-grid">
                        <div class="payment-item">
                            <input type="radio" name="metode_bayar" id="tf_bank" value="Transfer Bank" onclick="pilihBayar('bank')" required>
                            <label for="tf_bank" class="payment-label"><i class="fa-solid fa-building-columns"></i><span>Bank</span></label>
                        </div>
                        <div class="payment-item">
                            <input type="radio" name="metode_bayar" id="gopay" value="GoPay" onclick="pilihBayar('qris')">
                            <label for="gopay" class="payment-label"><i class="fa-solid fa-wallet"></i><span>GoPay</span></label>
                        </div>
                        <div class="payment-item">
                            <input type="radio" name="metode_bayar" id="dana" value="DANA" onclick="pilihBayar('qris')">
                            <label for="dana" class="payment-label"><i class="fa-solid fa-mobile-screen"></i><span>DANA</span></label>
                        </div>
                        <div class="payment-item">
                            <input type="radio" name="metode_bayar" id="ovo" value="OVO" onclick="pilihBayar('qris')">
                            <label for="ovo" class="payment-label"><i class="fa-solid fa-coins"></i><span>OVO</span></label>
                        </div>
                        <div class="payment-item">
                            <input type="radio" name="metode_bayar" id="qris" value="QRIS" onclick="pilihBayar('qris')">
                            <label for="qris" class="payment-label"><i class="fa-solid fa-qrcode"></i><span>QRIS</span></label>
                        </div>
                        <div class="payment-item">
                            <input type="radio" name="metode_bayar" id="cod" value="COD" onclick="pilihBayar('cod')">
                            <label for="cod" class="payment-label"><i class="fa-solid fa-hand-holding-dollar"></i><span>COD</span></label>
                        </div>
                    </div>

                    <div id="payment-box" class="payment-instruction">
                        <div id="content-bank" class="instruction-content" style="display:none; text-align:center;">
                            <p style="font-size: 0.75rem;">Transfer ke Rekening Gopay:</p>
                            <div style="background:rgba(0,0,0,0.3); padding:10px; border-radius:10px; margin:10px 0;">
                                <strong>0895 33747 5348</strong><br><small>A/N: Miftah Naufal</small>
                            </div>
                        </div>

                        <div id="content-qris" class="instruction-content" style="display:none; text-align:center;">
                            <p style="font-size: 0.75rem;">Silakan Scan QRIS:</p>
                            <img src="../assets/image/qris.jpeg" style="width:150px; border:4px solid white; border-radius:10px; margin-top:10px;">
                        </div>

                        <div id="content-cod" class="instruction-content" style="display:none; text-align:center;">
                            <p style="font-weight:600;">COD (Bayar di Tempat)</p>
                        </div>

                        <div id="upload-section" style="margin-top:15px; border-top:1px solid var(--border); padding-top:15px;">
                            <label style="font-size:0.7rem; color:var(--accent); font-weight:800;">UPLOAD BUKTI TRANSFER:</label>
                            <input type="file" name="bukti_pembayaran" id="input_bukti" class="form-input" accept="image/*" onchange="validasiTombol()">
                        </div>
                    </div>

                    <button type="submit" id="btn-submit" class="btn-confirm" disabled>
                        Konfirmasi & Bayar <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    function pilihBayar(tipe) {
        const box = document.getElementById('payment-box');
        const uploadArea = document.getElementById('upload-section');
        const inputBukti = document.getElementById('input_bukti');
        
        box.style.display = 'block';
        document.getElementById('content-bank').style.display = 'none';
        document.getElementById('content-qris').style.display = 'none';
        document.getElementById('content-cod').style.display = 'none';

        if(tipe === 'cod') {
            document.getElementById('content-cod').style.display = 'block';
            uploadArea.style.display = 'none';
            inputBukti.required = false;
        } else {
            if(tipe === 'bank') {
                document.getElementById('content-bank').style.display = 'block';
            } else {
                document.getElementById('content-qris').style.display = 'block';
            }
            uploadArea.style.display = 'block';
            inputBukti.required = true;
        }
        validasiTombol();
    }

    function validasiTombol() {
        const btn = document.getElementById('btn-submit');
        const metode = document.querySelector('input[name="metode_bayar"]:checked').value;
        const inputBukti = document.getElementById('input_bukti');

        if (metode === 'COD' || inputBukti.files.length > 0) {
            btn.disabled = false;
            btn.style.opacity = "1";
            btn.style.cursor = "pointer";
        } else {
            btn.disabled = true;
            btn.style.opacity = "0.5";
            btn.style.cursor = "not-allowed";
        }
    }
</script>
</body>
</html>