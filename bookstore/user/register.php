<?php
include '../config.php';

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Cek Duplicate Username (Proteksi agar tidak Fatal Error)
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Waduh! Username ini sudah dipakai. Cari yang lain ya!'); window.history.back();</script>";
    } else {
        $query = "INSERT INTO users (username, password, email, fullname, phone, role) 
        VALUES ('$username', '$password', '$email', '$fullname', '$phone', 'user')";
        if (mysqli_query($conn, $query)) { 
            echo "<script>alert('Pendaftaran Berhasil! Silakan Login.'); window.location='../login.php';</script>"; 
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join M&N Edition | Member Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --bg-dark: #020617;
            --accent-blue: #38bdf8;
            --glass: rgba(30, 41, 59, 0.7);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--bg-dark);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* --- SISI KIRI: SPANDUK IKLAN MEWAH --- */
        .promo-side {
            flex: 1.2;
            background: linear-gradient(rgba(2, 6, 23, 0.6), rgba(2, 6, 23, 0.6)), 
                        url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&q=80&w=2000');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px;
            position: relative;
        }

        .promo-side::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, transparent, var(--bg-dark));
        }

        .promo-content { position: relative; z-index: 2; }
        .promo-content h2 { font-size: 3.5rem; font-weight: 800; line-height: 1.1; margin-bottom: 20px; }
        .promo-content h2 span { color: var(--accent-blue); text-shadow: 0 0 20px rgba(56, 189, 248, 0.5); }
        .promo-content p { font-size: 1.2rem; color: var(--text-dim); max-width: 500px; margin-bottom: 30px; }

        .benefit-list { list-style: none; display: flex; flex-direction: column; gap: 15px; }
        .benefit-list li { display: flex; align-items: center; gap: 10px; font-weight: 600; color: #fff; }
        .benefit-list li::before { content: '✓'; color: var(--accent-blue); font-weight: 800; }

        /* --- SISI KANAN: FORM REGISTER --- */
        .form-side {
            flex: 0.8;
            display: flex;
            justify-content: center;
            align-items: center;
            background: radial-gradient(circle at bottom right, #1e1b4b, #020617);
            padding: 40px;
        }

        .register-box {
            width: 100%;
            max-width: 400px;
            background: var(--glass);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            position: relative;
        }

        /* TOMBOL KEMBALI */
        .back-to-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--text-dim);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 20px;
            transition: 0.3s;
        }
        .back-to-home:hover { color: var(--accent-blue); transform: translateX(-5px); }

        .register-box h3 { font-size: 1.8rem; margin-bottom: 10px; text-align: center; }
        .register-box p.subtitle { text-align: center; color: var(--text-dim); font-size: 0.9rem; margin-bottom: 30px; }

        .input-group { position: relative; margin-bottom: 20px; }

        input {
            width: 100%;
            padding: 15px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: #fff;
            outline: none;
            transition: 0.3s;
        }
        input[type="password"], input[type="text"], input[type="email"] { padding-right: 45px; }
        input:focus { border-color: var(--accent-blue); background: rgba(255,255,255,0.1); box-shadow: 0 0 15px rgba(56, 189, 248, 0.2); }

        .toggle-password {
            position: absolute;
            right: 15px; top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-dim);
            z-index: 10;
        }

        button {
            width: 100%;
            padding: 16px;
            background: var(--accent-blue);
            color: #020617;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        button:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(56, 189, 248, 0.4); }

        .footer-link { text-align: center; margin-top: 25px; font-size: 0.9rem; color: var(--text-dim); }
        .footer-link a { color: var(--accent-blue); text-decoration: none; font-weight: 600; }

        @media (max-width: 1000px) {
            .promo-side { display: none; }
            .form-side { flex: 1; }
        }
    </style>
</head>
<body>

    <div class="promo-side">
        <div class="promo-content">
            <div style="font-weight: 800; letter-spacing: 2px; color: var(--accent-blue); margin-bottom: 20px;">M&N EDITION</div>
            <h2>Unlock <span>Unlimited</span> <br> Knowledge.</h2>
            <p>Bergabunglah dengan komunitas pembaca eksklusif kami dan nikmati akses ke koleksi buku digital premium.</p>
            
            <ul class="benefit-list">
                <li>Akses 10,000+ Judul Buku</li>
                <li>Download PDF Gratis Setiap Minggu</li>
                <li>Rekomendasi Berbasis AI</li>
                <li>Tanpa Iklan yang Mengganggu</li>
            </ul>
        </div>
    </div>

    <div class="form-side">
        <div class="register-box">
            <a href="../index.php" class="back-to-home">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>

            <h3>Buat Akun</h3>
            <p class="subtitle">Mulai perjalanan literasimu hari ini.</p>
            
            <form method="POST">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-group">
                    <input type="text" name="fullname" placeholder="Nama Lengkap" required>
                </div>
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-group">
                    <input type="text" name="phone" placeholder="Masukkan No. Telp" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <i class="fa-solid fa-eye toggle-password" id="eyeIcon"></i>
                </div>
                
                <button type="submit" name="register">Daftar Sekarang</button>
            </form>
            
            <div class="footer-link">
                Sudah punya akun? <a href="../login.php">Masuk di sini</a>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        eyeIcon.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>

</body>
</html>