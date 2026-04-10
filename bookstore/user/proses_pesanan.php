<?php
session_start();
include '../config.php';

// 1. Proteksi Login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data dari form dan bersihkan (Security)
$nama = mysqli_real_escape_string($conn, $_POST['nama']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']);
$alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
$metode_bayar = mysqli_real_escape_string($conn, $_POST['metode_bayar']);
$total_bayar = mysqli_real_escape_string($conn, $_POST['total_bayar']);

// --- LOGIKA UPLOAD BUKTI PEMBAYARAN ---
$bukti_nama_file = null;

if ($metode_bayar !== 'COD') {
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
        $target_dir = "../assets/image/bukti_bayar/";
        
        // Buat folder otomatis jika belum ada
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["bukti_pembayaran"]["name"], PATHINFO_EXTENSION);
        // Nama file unik: bukti_USERID_TIMESTAMP.ext
        $bukti_nama_file = "bukti_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $bukti_nama_file;

        // Pindahkan file dari folder sementara ke folder tujuan
        if (!move_uploaded_file($_FILES["bukti_pembayaran"]["tmp_name"], $target_file)) {
            echo "<script>alert('Gagal mengupload gambar!'); window.history.back();</script>";
            exit;
        }
    } else {
        // Jika bukan COD tapi nggak ada file, balikkan
        echo "<script>alert('Bukti pembayaran wajib diupload!'); window.history.back();</script>";
        exit;
    }
}

// 2. Ambil semua item dari keranjang user
$query_cart = mysqli_query($conn, "SELECT id_buku FROM cart WHERE user_id = '$user_id'");

if (mysqli_num_rows($query_cart) > 0) {
    while($item = mysqli_fetch_assoc($query_cart)) {
        $book_id = $item['id_buku'];

        // 3. Simpan ke tabel orders (Termasuk kolom bukti_pembayaran)
        $query_insert = "INSERT INTO orders 
            (user_id, book_id, nama, phone, alamat, metode_bayar, total_amount, status, bukti_pembayaran) 
            VALUES 
            ('$user_id', '$book_id', '$nama', '$phone', '$alamat', '$metode_bayar', '$total_bayar', 'pending', '$bukti_nama_file')";
        
        mysqli_query($conn, $query_insert);
    }

    // 4. Hapus keranjang setelah order sukses
    mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'");

    echo "<script>alert('Pesanan berhasil dibuat!'); window.location.href='riwayat_pesanan.php';</script>";
} else {
    header("Location: cart.php");
}
exit;
?>