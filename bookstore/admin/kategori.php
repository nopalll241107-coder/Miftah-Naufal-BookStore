<?php
session_start();
include '../config.php';

// Proteksi Session Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- LOGIKA CRUD ---

// 1. Tambah Kategori
if (isset($_POST['add_category'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    mysqli_query($conn, "INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
    header("Location: kategori.php");
    exit;
}

// 2. Hapus Kategori (DENGAN PROTEKSI DATA BUKU)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Cek apakah ada buku yang masih menggunakan kategori ini
    $check_buku = mysqli_query($conn, "SELECT id_buku FROM buku WHERE id_kategori = '$id'");
    $jumlah_buku = mysqli_num_rows($check_buku);

    if ($jumlah_buku > 0) {
        // Jika masih ada buku, munculkan alert dan batalkan proses hapus
        echo "<script>
                alert('Gagal menghapus! Masih ada $jumlah_buku buku dalam kategori ini. Silakan hapus atau pindahkan data buku tersebut terlebih dahulu.');
                window.location='kategori.php';
              </script>";
    } else {
        // Jika sudah tidak ada buku, baru eksekusi hapus
        $delete = mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori = '$id'");
        if ($delete) {
            header("Location: kategori.php");
        } else {
            echo "<script>alert('Gagal menghapus karena kesalahan database.'); window.location='kategori.php';</script>";
        }
    }
    exit;
}

// 3. Edit Kategori (Proses Update)
if (isset($_POST['edit_category'])) {
    $id = $_POST['id_kategori'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    mysqli_query($conn, "UPDATE kategori SET nama_kategori = '$nama' WHERE id_kategori = '$id'");
    header("Location: kategori.php");
    exit;
}

// Ambil semua data kategori untuk ditampilkan di tabel
$result = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id_kategori DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori | Admin M&N</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-dark); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }

        /* --- SIDEBAR --- */
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
        .brand h2 { font-size: 1.5rem; font-weight: 800; color: white; line-height: 1; }
        .brand h2 span { color: var(--accent-purple); }
        .brand p { font-size: 0.65rem; letter-spacing: 2px; color: var(--text-dim); text-transform: uppercase; margin-top: 5px; font-weight: 600; }

        nav { display: flex; flex-direction: column; gap: 8px; }
        .nav-link {
            display: flex; align-items: center; gap: 15px; padding: 12px 20px;
            color: var(--text-dim); text-decoration: none; border-radius: 12px;
            font-weight: 600; transition: 0.3s;
        }
        .nav-link:hover, .nav-link.active { background: rgba(129, 140, 248, 0.1); color: var(--accent-purple); }
        .logout-link { margin-top: auto; color: var(--danger) !important; }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 280px; flex-grow: 1; padding: 40px; width: calc(100% - 280px); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header-flex h1 span { color: var(--accent-purple); }

        .card-form { background: var(--glass); padding: 25px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); margin-bottom: 30px; }
        .form-group { display: flex; gap: 15px; align-items: center; }
        
        input[type="text"] {
            flex: 1; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            padding: 12px 20px; border-radius: 10px; color: white; outline: none; transition: 0.3s;
        }
        input[type="text"]:focus { border-color: var(--accent-purple); }
        
        .btn-submit { background: var(--accent-purple); color: white; border: none; padding: 12px 25px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(129, 140, 248, 0.3); }

        /* --- TABLE --- */
        .table-container { background: var(--glass); border-radius: 20px; padding: 20px; border: 1px solid rgba(255,255,255,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: var(--text-dim); border-bottom: 1px solid rgba(255,255,255,0.1); font-size: 0.85rem; text-transform: uppercase; }
        td { padding: 18px 15px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        
        .btn-edit { color: #f59e0b; margin-right: 15px; font-size: 1.1rem; }
        .btn-delete { color: var(--danger); font-size: 1.1rem; }
        .id-real { font-size: 0.7rem; color: var(--text-dim); background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; margin-left: 8px; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">
            <h2>ADMIN<span>PANEL</span></h2>
            <p>M&N Edition</p>
        </div>
        <nav>
            <a href="admin_dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="kategori.php" class="nav-link active"><i class="fa-solid fa-layer-group"></i> Kategori Buku</a>
            <a href="buku.php" class="nav-link"><i class="fa-solid fa-book"></i> Data Buku</a>
            <a href="member.php" class="nav-link"><i class="fa-solid fa-users"></i> Data Member</a>
            <a href="pesanan.php" class="nav-link"><i class="fa-solid fa-cart-shopping"></i> Pesanan</a>
            
            <a href="../logout.php" class="nav-link logout-link" onclick="return confirm('Yakin ingin keluar?')">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header-flex">
            <h1>Kelola <span>Kategori</span></h1>
            <p style="color: var(--text-dim);">Total: <b><?= mysqli_num_rows($result); ?></b> Kategori</p>
        </div>

        <div class="card-form">
            <?php if(isset($_GET['edit'])): 
                $id_edit = $_GET['edit'];
                $data_edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM kategori WHERE id_kategori = '$id_edit'"));
            ?>
                <h4 style="margin-bottom: 15px; color: var(--accent-purple);">Mode Edit Kategori</h4>
                <form action="" method="POST" class="form-group">
                    <input type="hidden" name="id_kategori" value="<?= $data_edit['id_kategori']; ?>">
                    <input type="text" name="nama_kategori" value="<?= $data_edit['nama_kategori']; ?>" required autofocus>
                    <button type="submit" name="edit_category" class="btn-submit">Update</button>
                    <a href="kategori.php" style="color: var(--text-dim); text-decoration: none; font-size: 0.9rem; margin-left: 10px;">Batal</a>
                </form>
            <?php else: ?>
                <h4 style="margin-bottom: 15px;">Tambah Kategori Baru</h4>
                <form action="" method="POST" class="form-group">
                    <input type="text" name="nama_kategori" placeholder="Contoh: Programming, Novel, Desain..." required>
                    <button type="submit" name="add_category" class="btn-submit"><i class="fa-solid fa-plus"></i> Simpan</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="80px">No.</th>
                        <th>Nama Kategori</th>
                        <th width="120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <b><?= $no++; ?>.</b>
                            <span class="id-real">#<?= $row['id_kategori']; ?></span>
                        </td>
                        <td><b><?= $row['nama_kategori']; ?></b></td>
                        <td>
                            <a href="?edit=<?= $row['id_kategori']; ?>" class="btn-edit" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a href="?delete=<?= $row['id_kategori']; ?>" class="btn-delete" onclick="return confirm('Hapus kategori ini?')" title="Hapus"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    
                    <?php if(mysqli_num_rows($result) == 0): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-dim); padding: 30px;">Belum ada data kategori.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>