<?php
// Sidebar dinamis
$currentPage = basename($_SERVER['PHP_SELF']); // Ambil nama file halaman saat ini
?>
<aside class="sidebar">
    <div class="brand">
        <h2>ADMIN<span>PANEL</span></h2>
        <p>M&N Edition</p>
    </div>
    <nav>
        <a href="admin_dashboard.php" class="nav-link <?= ($currentPage=='admin_dashboard.php')?'active':'' ?>">
            <i class="fa-solid fa-chart-line"></i> Dashboard
        </a>
        <a href="kategori.php" class="nav-link <?= ($currentPage=='kategori.php')?'active':'' ?>">
            <i class="fa-solid fa-layer-group"></i> Kategori Buku
        </a>
        <a href="buku.php" class="nav-link <?= ($currentPage=='buku.php')?'active':'' ?>">
            <i class="fa-solid fa-book"></i> Data Buku
        </a>
        <a href="member.php" class="nav-link <?= ($currentPage=='member.php')?'active':'' ?>">
            <i class="fa-solid fa-users"></i> Data Member
        </a>
        <a href="pesanan.php" class="nav-link <?= ($currentPage=='pesanan.php')?'active':'' ?>">
            <i class="fa-solid fa-cart-shopping"></i> Pesanan
        </a>
        <a href="../logout.php" class="nav-link logout-link" onclick="return confirm('Yakin ingin keluar?')">
            <i class="fa-solid fa-right-from-bracket"></i> Keluar
        </a>
    </nav>
</aside>

<style>
:root {
    --bg-dark: #020617;
    --sidebar-bg: #0f172a;
    --accent-purple: #818cf8;
    --glass: rgba(30, 41, 59, 0.5);
    --text-main: #f8fafc;
    --text-dim: #94a3b8;
    --danger: #ef4444;
}

/* --- Sidebar --- */
.sidebar { 
    width: 280px; 
    background: var(--sidebar-bg); 
    padding: 40px 20px; 
    position: fixed; 
    height: 100vh; 
    border-right: 1px solid rgba(255,255,255,0.05);
    display: flex;
    flex-direction: column;
    z-index: 1000;
}

.brand { margin-bottom: 50px; padding-left: 10px; }
.brand h2 { font-size: 1.75rem; font-weight: 800; color: white; line-height: 1; }
.brand h2 span { color: var(--accent-purple); }
.brand p { font-size: 0.75rem; letter-spacing: 1.5px; color: var(--text-dim); text-transform: uppercase; margin-top: 5px; font-weight: 600; }

nav { display: flex; flex-direction: column; gap: 10px; }
.nav-link { 
    display: flex; align-items: center; gap: 15px; padding: 14px 22px; 
    color: var(--text-dim); text-decoration: none; border-radius: 12px; 
    font-weight: 700; font-size: 1rem; transition: all 0.3s ease;
}
.nav-link i { font-size: 1.2rem; }
.nav-link:hover { 
    background: rgba(129, 140, 248, 0.15); 
    color: var(--accent-purple); 
}
.nav-link.active { 
    background: var(--accent-purple); 
    color: #020617; 
    font-size: 1.05rem; 
    font-weight: 800;
    box-shadow: 0 4px 20px rgba(129, 140, 248, 0.4);
}

.logout-link { margin-top: auto; color: var(--danger) !important; font-weight: 700; }
.logout-link:hover { background: rgba(239, 68, 68, 0.15); color: var(--danger); }

/* --- Main Content --- */
.main-content { 
    margin-left: 280px; 
    flex-grow: 1; 
    padding: 50px; 
    width: calc(100% - 280px);
    transition: all 0.3s ease;
}
</style>