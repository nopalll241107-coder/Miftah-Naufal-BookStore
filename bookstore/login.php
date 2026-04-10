<?php
session_start();
include 'config.php'; 

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $res = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($res) === 1) {
        $row = mysqli_fetch_assoc($res);
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id']; 
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                header("Location: admin/admin_dashboard.php");
            } else {
                header("Location: user/user_dashboard.php");
            }
            exit;
        }
    }
    $error = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Member | M&N Edition</title>
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

        /* --- SISI KIRI: SPANDUK WELCOME --- */
        .welcome-side {
            flex: 1.2;
            background: linear-gradient(rgba(2, 6, 23, 0.7), rgba(2, 6, 23, 0.7)), 
                        url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&q=80&w=2000');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px;
            position: relative;
        }

        .welcome-side::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, transparent, var(--bg-dark));
        }

        .welcome-content { position: relative; z-index: 2; }
        .brand-logo { margin-bottom: 30px; }
        .brand-logo h1 { font-size: 2.2rem; font-weight: 800; line-height: 1; }
        .brand-logo h1 span { color: var(--accent-blue); }
        .brand-logo p { font-size: 0.7rem; letter-spacing: 5px; color: var(--text-dim); text-transform: uppercase; font-weight: 700; margin-top: 5px; }

        .welcome-content h2 { font-size: 3rem; font-weight: 800; margin-bottom: 20px; line-height: 1.2; }
        .welcome-content h2 span { color: var(--accent-blue); text-shadow: 0 0 20px rgba(56, 189, 248, 0.5); }

        /* --- SISI KANAN: FORM LOGIN --- */
        .form-side {
            flex: 0.8;
            display: flex;
            justify-content: center;
            align-items: center;
            background: radial-gradient(circle at bottom right, #1e1b4b, #020617);
            padding: 40px;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
            background: var(--glass);
            backdrop-filter: blur(20px);
            padding: 45px;
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
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

        .login-box h3 { font-size: 1.8rem; margin-bottom: 10px; text-align: center; font-weight: 800; }
        .login-box p.subtitle { text-align: center; color: var(--text-dim); font-size: 0.9rem; margin-bottom: 30px; }

        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 12px;
            border-radius: 10px;
            font-size: 0.85rem;
            text-align: center;
            margin-bottom: 20px;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

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
            font-size: 1rem;
        }

        input[name="password"] { padding-right: 45px; }

        input:focus {
            border-color: var(--accent-blue);
            background: rgba(255,255,255,0.1);
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.2);
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-dim);
            transition: 0.3s;
            z-index: 10;
        }
        .toggle-password:hover { color: var(--accent-blue); }

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

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(56, 189, 248, 0.4);
        }

        .footer-link { text-align: center; margin-top: 25px; font-size: 0.9rem; color: var(--text-dim); }
        .footer-link a { color: var(--accent-blue); text-decoration: none; font-weight: 600; }

        @media (max-width: 1000px) {
            .welcome-side { display: none; }
            .form-side { flex: 1; }
        }
    </style>
</head>
<body>

    <div class="welcome-side">
        <div class="welcome-content">
            <div class="brand-logo">
                <h1>BOOK<span>STORE</span></h1>
                <p>M&N Edition</p>
            </div>
            <h2>Selamat Datang <span>Kembali.</span></h2>
            <p>Silakan masuk untuk melanjutkan akses ke perpustakaan digital pribadimu dan eksplorasi ribuan buku terbaru.</p>
        </div>
    </div>

    <div class="form-side">
        <div class="login-box">
            <a href="index.php" class="back-to-home">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>

            <h3>Login Member</h3>
            <p class="subtitle">Akses akun M&N Edition kamu.</p>

            <?php if(isset($error)): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-circle"></i> Username atau Password salah!
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                
                <div class="input-group">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <i class="fa-solid fa-eye toggle-password" id="eyeIcon"></i>
                </div>
                
                <button type="submit" name="login">Masuk Sekarang</button>
            </form>
            
            <div class="footer-link">
                Belum punya akun? <a href="user/register.php">Daftar di sini</a>
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