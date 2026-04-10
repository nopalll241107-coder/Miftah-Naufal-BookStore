<?php
session_start();
include '../config.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../login.php"); 
    exit; 
}

// --- LOGIKA HAPUS MEMBER ---
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    $delete_query = "DELETE FROM users WHERE id = '$id' AND role != 'admin'";
    
    if (mysqli_query($conn, $delete_query)) {
        header("Location: member.php?status=deleted");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    exit;
}

// --- LOGIKA PENCARIAN ---
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query_sql = "SELECT * FROM users 
                  WHERE role = 'user' 
                  AND (username LIKE '%$search%' 
                  OR email LIKE '%$search%'
                  OR phone LIKE '%$search%')
                  ORDER BY id DESC";
} else {
    $query_sql = "SELECT * FROM users WHERE role = 'user' ORDER BY id DESC";
}

$query_member = mysqli_query($conn, $query_sql);
$total_member = ($query_member) ? mysqli_num_rows($query_member) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Member | M&N Edition</title>
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #020617;
            --sidebar-bg: #0f172a;
            --accent-purple: #818cf8;
            --glass: rgba(30, 41, 59, 0.4);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
            --danger: #ef4444;
            --success: #22c55e;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-dark); color: var(--text-main); display: flex; min-height: 100vh; }

        /* SIDEBAR STYLE */
        
        /* CONTENT AREA */
        .main-content { margin-left: 280px; flex-grow: 1; padding: 40px; width: calc(100% - 280px); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        h1 { font-size: 2rem; font-weight: 800; }
        h1 span { color: var(--accent-purple); }
        .stats-badge { background: var(--glass); padding: 8px 15px; border-radius: 10px; border: 1px solid var(--glass-border); color: var(--text-dim); font-size: 0.9rem; }

        /* SEARCH BAR */
        .search-form { display: flex; gap: 12px; margin-bottom: 25px; }
        .search-wrapper { position: relative; flex-grow: 1; max-width: 400px; }
        .search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-dim); }
        .search-input { 
            width: 100%; background: var(--glass); border: 1px solid var(--glass-border); 
            padding: 12px 15px 12px 45px; border-radius: 12px; color: white; font-family: inherit; outline: none;
        }
        .btn-search { 
            background: var(--accent-purple); color: white; border: none; padding: 0 25px; 
            border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s;
        }

        /* TABLE STYLE */
        .table-card { 
            background: var(--glass); border-radius: 24px; padding: 15px; 
            border: 1px solid var(--glass-border); overflow-x: auto;
            backdrop-filter: blur(10px);
        }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 20px; color: var(--text-dim); border-bottom: 1px solid var(--glass-border); font-size: 0.75rem; text-transform: uppercase; }
        td { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.03); vertical-align: middle; }
        
        .no-column { color: #ffffff; font-weight: 700; }
        .phone-text { color: var(--accent-purple); font-weight: 600; font-family: monospace; }

        .user-avatar { 
            width: 40px; height: 40px; background: linear-gradient(135deg, var(--accent-purple), #6366f1); 
            border-radius: 10px; display: flex; align-items: center; justify-content: center; 
            font-weight: 800; color: white; text-transform: uppercase;
        }
        .role-badge { 
            background: rgba(34, 197, 94, 0.1); color: var(--success); 
            padding: 4px 10px; border-radius: 8px; font-size: 0.7rem; font-weight: 700; border: 1px solid rgba(34, 197, 94, 0.2); 
        }
        .btn-delete { color: var(--danger); transition: 0.3s; font-size: 1.1rem; cursor: pointer; text-decoration: none; }
    </style>
</head>
<body>

    <?php include '../include/sidebar_admin.php'; ?>
    <main class="main-content">
        <div class="header-flex">
            <h1>Data <span>Member</span></h1>
            <div class="stats-badge">Total: <b><?= $total_member; ?></b> Member</div>
        </div>

        <form action="" method="GET" class="search-form">
            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" class="search-input" placeholder="Cari username, email, atau telepon..." value="<?= htmlspecialchars($search); ?>">
            </div>
            <button type="submit" class="btn-search">Cari</button>
        </form>
        
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Gabung</th>
                        <th>Role</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($total_member > 0): ?>
                        <?php 
                        $no = 1;
                        while($m = mysqli_fetch_assoc($query_member)): 
                        ?>
                        <tr>
                            <td class="no-column">#<?= $no++; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="user-avatar"><?= substr($m['username'], 0, 1); ?></div>
                                    <b><?= htmlspecialchars($m['username']); ?></b>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($m['email']); ?></td>
                            <td class="phone-text"><?= $m['phone'] ? htmlspecialchars($m['phone']) : '-'; ?></td>
                            <td><?= date('d/m/y', strtotime($m['created_at'])); ?></td>
                            <td><span class="role-badge"><?= strtoupper($m['role']); ?></span></td>
                            <td style="text-align: center;">
                                <a href="?delete=<?= $m['id']; ?>" class="btn-delete" onclick="return confirm('Hapus member ini?')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align: center; padding: 50px; color: var(--text-dim);">Data tidak ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>