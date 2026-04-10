<?php
session_start();
include '../config.php';

// Proteksi Session Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: ../login.php"); 
    exit; 
}

// --- LOGIKA TAMBAH BUKU ---
if (isset($_POST['add_book'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $penulis = mysqli_real_escape_string($conn, $_POST['penulis']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $id_kat = $_POST['id_kategori'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']); 
    
    $nama_file = time() . '_' . $_FILES['gambar']['name']; 
    $source = $_FILES['gambar']['tmp_name'];
    $folder = '../assets/image/books/';
    
    if(move_uploaded_file($source, $folder.$nama_file)) {
        mysqli_query($conn, "INSERT INTO buku (judul, penulis, harga, stok, deskripsi, id_kategori, gambar) 
                             VALUES ('$judul', '$penulis', '$harga', '$stok', '$deskripsi', '$id_kat', '$nama_file')");
    }
    header("Location: buku.php");
    exit;
}

// --- LOGIKA EDIT BUKU ---
if (isset($_POST['edit_book'])) {
    $id = $_POST['id_buku'];
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $penulis = mysqli_real_escape_string($conn, $_POST['penulis']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $id_kat = $_POST['id_kategori'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    if ($_FILES['gambar']['name'] != "") {
        $nama_file = time() . '_' . $_FILES['gambar']['name'];
        move_uploaded_file($_FILES['gambar']['tmp_name'], '../assets/image/books/'.$nama_file);
        
        $old_img_q = mysqli_query($conn, "SELECT gambar FROM buku WHERE id_buku = '$id'");
        $old_img = mysqli_fetch_assoc($old_img_q);
        if($old_img['gambar'] != '' && file_exists('../assets/image/books/'.$old_img['gambar'])) unlink('../assets/image/books/'.$old_img['gambar']);

        mysqli_query($conn, "UPDATE buku SET judul='$judul', penulis='$penulis', harga='$harga', stok='$stok', deskripsi='$deskripsi', id_kategori='$id_kat', gambar='$nama_file' WHERE id_buku='$id'");
    } else {
        mysqli_query($conn, "UPDATE buku SET judul='$judul', penulis='$penulis', harga='$harga', stok='$stok', deskripsi='$deskripsi', id_kategori='$id_kat' WHERE id_buku='$id'");
    }
    header("Location: buku.php");
    exit;
}

// --- LOGIKA HAPUS ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $query_img = mysqli_query($conn, "SELECT gambar FROM buku WHERE id_buku = '$id'");
    $data_img = mysqli_fetch_assoc($query_img);
    if($data_img['gambar'] != '' && file_exists('../assets/image/books/'.$data_img['gambar'])) {
        unlink('../assets/image/books/'.$data_img['gambar']);
    }
    mysqli_query($conn, "DELETE FROM buku WHERE id_buku = '$id'");
    header("Location: buku.php");
    exit;
}

$buku_list = mysqli_query($conn, "SELECT buku.*, kategori.nama_kategori FROM buku LEFT JOIN kategori ON buku.id_kategori = kategori.id_kategori ORDER BY buku.id_buku DESC");
$kategori_query = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data Buku | Admin M&N</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { 
            color-scheme: dark; /* Memaksa elemen UI browser ke mode gelap */
            --bg-dark: #020617; 
            --sidebar-bg: #0f172a; 
            --accent-purple: #818cf8; 
            --glass: rgba(30, 41, 59, 0.4); 
            --glass-border: rgba(255, 255, 255, 0.1); 
            --text-main: #f8fafc; 
            --text-dim: #94a3b8; 
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-dark); color: var(--text-main); display: flex; overflow-x: hidden; }

        .main-content { margin-left: 280px; flex-grow: 1; padding: 40px; width: calc(100% - 280px); }
        h1 { font-size: 2rem; font-weight: 800; margin-bottom: 30px; }
        h1 span { color: var(--accent-purple); }

        .form-card { background: var(--glass); padding: 30px; border-radius: 24px; border: 1px solid var(--glass-border); margin-bottom: 40px; backdrop-filter: blur(10px); }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px; }
        .input-group { display: flex; flex-direction: column; gap: 8px; }
        .input-group label { font-size: 0.85rem; color: var(--text-dim); }
        
        /* PERBAIKAN: Input & Select agar tidak transparan putih */
        input, select, textarea { 
            background: #1e293b; /* Background solid biru gelap */
            border: 1px solid var(--glass-border); 
            padding: 14px; 
            border-radius: 12px; 
            color: white; 
            outline: none; 
            font-family: inherit; 
            width: 100%; 
        }

        /* Menghilangkan background putih pada opsi select di browser tertentu */
        select option {
            background-color: #0f172a;
            color: white;
        }

        textarea { resize: none; height: 100px; }
        
        .btn-add { background: var(--accent-purple); color: white; border: none; padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: 10px; width: 100%; }
        .btn-add:hover { opacity: 0.9; transform: translateY(-2px); }

        .table-card { background: var(--glass); border-radius: 24px; padding: 20px; border: 1px solid var(--glass-border); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: var(--text-dim); border-bottom: 1px solid var(--glass-border); font-size: 0.8rem; }
        td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.03); }
        .img-preview { width: 50px; height: 70px; border-radius: 8px; object-fit: cover; }

        /* MODAL STYLE */
        .modal { 
            display: none; 
            position: fixed; 
            z-index: 2000; 
            left: 0; top: 0; 
            width: 100%; height: 100%; 
            background: rgba(0,0,0,0.85); 
            backdrop-filter: blur(8px); 
            overflow-y: auto;
            padding: 40px 0;
            justify-content: center;
            align-items: flex-start;
        }
        .modal-content { 
            background: #111827; 
            margin: auto; 
            padding: 35px; 
            width: 90%; 
            max-width: 600px; 
            border-radius: 28px; 
            border: 1px solid var(--glass-border); 
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .modal-content h3 span { color: var(--accent-purple); }
    </style>
</head>
<body>
    <?php include '../include/sidebar_admin.php'; ?>

    <main class="main-content">
        <h1>Kelola <span>Data Buku</span></h1>
        
        <div class="form-card">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="input-group"><label>Judul Buku</label><input type="text" name="judul" required></div>
                    <div class="input-group"><label>Penulis</label><input type="text" name="penulis" required></div>
                </div>
                <div class="form-grid" style="grid-template-columns: 1fr 1fr 1fr;">
                    <div class="input-group"><label>Harga</label><input type="number" name="harga" required></div>
                    <div class="input-group"><label>Stok</label><input type="number" name="stok" required></div>
                    <div class="input-group">
                        <label>Kategori</label>
                        <select name="id_kategori" required>
                            <option value="" disabled selected>Pilih Kategori</option>
                            <?php mysqli_data_seek($kategori_query, 0); while($k = mysqli_fetch_assoc($kategori_query)): ?>
                                <option value="<?= $k['id_kategori']; ?>"><?= $k['nama_kategori']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="input-group" style="margin-bottom: 15px;">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" placeholder="Tulis deskripsi buku..." required></textarea>
                </div>
                <div class="input-group" style="margin-bottom: 10px;"><label>Sampul</label><input type="file" name="gambar" required></div>
                <button type="submit" name="add_book" class="btn-add">+ Tambah Koleksi</button>
            </form>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr><th>Cover</th><th>Buku</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php while($b = mysqli_fetch_assoc($buku_list)): ?>
                    <tr>
                        <td><img src="../assets/image/books/<?= $b['gambar']; ?>" class="img-preview"></td>
                        <td><b><?= $b['judul']; ?></b><br><small style="color:var(--text-dim)"><?= $b['penulis']; ?></small></td>
                        <td><?= $b['nama_kategori']; ?></td>
                        <td>Rp <?= number_format($b['harga'], 0, ',', '.'); ?></td>
                        <td><?= $b['stok']; ?></td>
                        <td>
                            <a href="javascript:void(0)" onclick="openEdit(<?= htmlspecialchars(json_encode($b)); ?>)" style="color:#f59e0b; margin-right:15px;"><i class="fa-solid fa-pen-to-square"></i></a>
                            <a href="?delete=<?= $b['id_buku']; ?>" onclick="return confirm('Hapus buku ini?')" style="color:#ef4444"><i class="fa-solid fa-trash-can"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <h3>Edit <span>Data Buku</span></h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_buku" id="eid">
                
                <div class="form-grid">
                    <div class="input-group"><label>Judul</label><input type="text" name="judul" id="ejudul" required></div>
                    <div class="input-group"><label>Penulis</label><input type="text" name="penulis" id="epenulis" required></div>
                </div>

                <div class="form-grid" style="grid-template-columns: 1fr 1fr 1fr; margin-top: 15px;">
                    <div class="input-group"><label>Harga</label><input type="number" name="harga" id="eharga" required></div>
                    <div class="input-group"><label>Stok</label><input type="number" name="stok" id="estok" required></div>
                    <div class="input-group">
                        <label>Kategori</label>
                        <select name="id_kategori" id="ekat">
                            <?php mysqli_data_seek($kategori_query, 0); while($k = mysqli_fetch_assoc($kategori_query)): ?>
                                <option value="<?= $k['id_kategori']; ?>"><?= $k['nama_kategori']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="input-group" style="margin-top: 15px;">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" id="edeskripsi" style="height: 120px;" required></textarea>
                </div>

                <div class="input-group" style="margin-top: 15px;">
                    <label>Ganti Sampul (Opsional)</label>
                    <input type="file" name="gambar">
                </div>

                <div style="display: flex; gap: 12px; margin-top: 30px;">
                    <button type="submit" name="edit_book" class="btn-add" style="background:#f59e0b; margin-top:0;">Update Data</button>
                    <button type="button" onclick="document.getElementById('editModal').style.display='none'" class="btn-add" style="background:rgba(255,255,255,0.05); margin-top:0;">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEdit(d) {
            document.getElementById('eid').value = d.id_buku;
            document.getElementById('ejudul').value = d.judul;
            document.getElementById('epenulis').value = d.penulis;
            document.getElementById('eharga').value = d.harga;
            document.getElementById('estok').value = d.stok;
            document.getElementById('ekat').value = d.id_kategori;
            document.getElementById('edeskripsi').value = d.deskripsi;
            document.getElementById('editModal').style.display = 'flex';
        }

        window.onclick = function(event) {
            let modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>