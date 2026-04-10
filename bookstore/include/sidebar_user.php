<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
.sidebar {
    width: 280px;
    background: var(--sidebar-bg);
    height: 100vh;
    position: fixed;
    padding: 40px 25px;
    display: flex;
    flex-direction: column;
    border-right: 1px solid rgba(255,255,255,0.1);
    z-index: 100;
}

.brand h2 {
    font-size: 1.5rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.brand span {
    color: var(--accent-blue);
}

.brand p {
    font-size: 0.65rem;
    color: var(--text-dim);
    letter-spacing: 2px;
    font-weight: 600;
    margin-top: -5px;
}

.nav-menu {
    flex: 1;
    margin-top: 40px;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 14px 20px;
    color: var(--text-dim);
    text-decoration: none;
    border-radius: 12px;
    margin-bottom: 10px;
    transition: 0.3s;
    font-weight: 600;
}

.nav-link:hover,
.nav-link.active {
    background: rgba(56, 189, 248, 0.1);
    color: var(--accent-blue);
}

.logout-link {
    color: #ef4444;
    text-decoration: none;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    margin-top: auto;
    border-radius: 12px;
    transition: 0.3s;
}

.logout-link:hover {
    background: rgba(239, 68, 68, 0.1);
}
</style>

<aside class="sidebar">
    <div class="brand">
        <h2>BOOK<span>STORE</span></h2>
        <p>M&N EDITION</p>
    </div>

    <nav class="nav-menu">
        <a href="user_dashboard.php" 
           class="nav-link <?= ($current_page == 'user_dashboard.php') ? 'active' : ''; ?>">
           <i class="fa-solid fa-house"></i> Dashboard
        </a>

        <a href="cart.php" 
           class="nav-link <?= ($current_page == 'cart.php') ? 'active' : ''; ?>">
           <i class="fa-solid fa-cart-shopping"></i> Keranjang
        </a>

        <a href="favorites.php" 
           class="nav-link <?= ($current_page == 'favorites.php') ? 'active' : ''; ?>">
           <i class="fa-solid fa-heart"></i> Favorit
        </a>

        <a href="riwayat_pesanan.php" 
           class="nav-link <?= ($current_page == 'riwayat_pesanan.php') ? 'active' : ''; ?>">
           <i class="fa-solid fa-clock-rotate-left"></i> Riwayat
        </a>
    </nav>

    <a href="../logout.php" class="logout-link" onclick="return confirm('Keluar?')">
        <i class="fa-solid fa-right-from-bracket"></i> Keluar
    </a>
</aside>