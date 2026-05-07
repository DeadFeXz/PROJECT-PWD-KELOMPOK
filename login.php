<?php
session_start();
require_once 'config/database.php';

// Jika sudah login, redirect ke index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username = '$username' OR email = '$username'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['email'] = $user['email'];
        
        // Redirect ke halaman sebelumnya atau index
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
        header("Location: $redirect");
        exit;
    } else {
        $error = "Username/email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Login | UPNFOODHEMAT</title>
    
    <!-- Bootstrap 5 + Icons + Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/upnvylogo.png">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-red: #ed2739;
            --primary-dark: #b91c2c;
            --premium-gold: #f5b042;
            --dark-bg: #0f0f1a;
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            position: fixed;
            width: 100%;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f0f1a 0%, #1a1a2e 50%, #16213e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Animasi background */
        body::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(237,39,57,0.15) 0%, transparent 70%);
            top: -150px;
            left: -150px;
            border-radius: 50%;
            pointer-events: none;
        }
        
        body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(245,176,66,0.08) 0%, transparent 70%);
            bottom: -200px;
            right: -200px;
            border-radius: 50%;
            pointer-events: none;
        }
        
        .floating-shape {
            position: absolute;
            pointer-events: none;
            z-index: 0;
        }
        
        .shape-1 {
            top: 10%;
            left: 5%;
            width: 200px;
            height: 200px;
            background: rgba(237,39,57,0.05);
            border-radius: 30px;
            transform: rotate(45deg);
            animation: float 20s infinite ease-in-out;
        }
        
        .shape-2 {
            bottom: 15%;
            right: 3%;
            width: 150px;
            height: 150px;
            background: rgba(245,176,66,0.06);
            border-radius: 50%;
            animation: float 15s infinite ease-in-out reverse;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(10deg); }
        }
        
        /* Main Login Card  */
        .login-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 48px;
            padding: 32px 36px;
            box-shadow: 0 35px 70px -20px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255,255,255,0.1);
            transition: all 0.4s ease;
        }
        
        /* Logo Section -  */
        .logo-section {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(135deg, #fff5f5, #ffffff);
            padding: 8px 22px;
            border-radius: 100px;
            margin-bottom: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }
        
        .logo-wrapper img {
            height: 38px;
        }
        
        .logo-wrapper h2 {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .badge-premium {
            background: linear-gradient(135deg, #f5b042, #e6a020);
            color: white;
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 0.65rem;
            font-weight: 700;
            display: inline-block;
            letter-spacing: 0.5px;
        }
        
        .logo-section p {
            font-size: 0.7rem;
            color: #64748b;
            margin-top: 8px;
            margin-bottom: 0;
        }
        
        /* Welcome Text  */
        .welcome-text {
            text-align: center;
            margin-bottom: 18px;
        }
        
        .welcome-text h3 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }
        
        .welcome-text p {
            color: #64748b;
            font-size: 0.75rem;
            margin: 0;
        }
        
        /* Form Styling  */
        .form-group {
            margin-bottom: 14px;
        }
        
        .form-label {
            font-weight: 700;
            font-size: 0.75rem;
            margin-bottom: 6px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .form-label i {
            color: var(--primary-red);
            font-size: 0.85rem;
        }
        
        .input-group-modern {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            color: #94a3b8;
            font-size: 1rem;
            z-index: 1;
        }
        
        .form-control-modern {
            width: 100%;
            padding: 10px 16px 10px 44px;
            border: 2px solid #e2e8f0;
            border-radius: 24px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            background: white;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        
        .form-control-modern:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(237, 39, 57, 0.1);
        }
        
        /* Options Row */
        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }
        
        .checkbox-custom {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 500;
            cursor: pointer;
        }
        
        .checkbox-custom input {
            width: 14px;
            height: 14px;
            cursor: pointer;
            accent-color: var(--primary-red);
            margin: 0;
        }
        
        .forgot-link {
            color: var(--primary-red);
            text-decoration: none;
            font-size: 0.7rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .forgot-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        /* Button Styles  */
        .btn-login {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 40px;
            font-weight: 800;
            font-size: 0.85rem;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-dark));
            color: white;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -8px rgba(237, 39, 57, 0.5);
        }
        
        .btn-back {
            width: 100%;
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.8rem;
            background: white;
            color: #475569;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            margin-bottom: 10px;
        }
        
        .btn-back:hover {
            border-color: var(--primary-red);
            color: var(--primary-red);
            transform: translateY(-2px);
            background: #fff5f5;
        }
        
        /* Divider - */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 18px 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .divider span {
            padding: 0 12px;
            color: #94a3b8;
            font-size: 0.7rem;
            font-weight: 500;
        }
        
        /* Social Login - */
        .social-login {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 18px;
        }
        
        .social-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            color: #64748b;
            text-decoration: none;
            font-size: 1rem;
        }
        
        .social-btn:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #fff5f5, #ffffff);
            border-color: var(--primary-red);
            color: var(--primary-red);
            box-shadow: 0 4px 12px rgba(237, 39, 57, 0.15);
        }
        
        /* Register Link  */
        .register-link {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }
        
        .register-link p {
            color: #64748b;
            font-size: 0.75rem;
            margin: 0;
        }
        
        .register-link a {
            color: var(--primary-red);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        /* Alert Styling  */
        .alert-premium {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: none;
            border-radius: 20px;
            padding: 10px 16px;
            color: #991b1b;
            font-weight: 500;
            font-size: 0.75rem;
            margin-bottom: 18px;
            border-left: 4px solid var(--primary-red);
        }
        
        .alert-premium .btn-close {
            font-size: 0.6rem;
            padding: 0.5rem;
        }
        
        /* Animasi */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card {
            animation: fadeInUp 0.5s ease-out;
        }
        
        /* Responsive untuk layar kecil */
        @media (max-width: 480px) {
            .login-card {
                padding: 24px 24px;
                border-radius: 40px;
            }
            
            .logo-wrapper {
                padding: 6px 18px;
            }
            
            .logo-wrapper img {
                height: 32px;
            }
            
            .logo-wrapper h2 {
                font-size: 1.3rem;
            }
            
            .welcome-text h3 {
                font-size: 1.2rem;
            }
            
            .btn-login, .btn-back {
                padding: 10px;
            }
        }
        
        /* Untuk layar sangat pendek (max-height 650px) */
        @media (max-height: 700px) {
            .login-card {
                padding: 20px 36px;
            }
            
            .logo-section {
                margin-bottom: 12px;
            }
            
            .logo-wrapper {
                padding: 6px 20px;
                margin-bottom: 8px;
            }
            
            .logo-wrapper img {
                height: 32px;
            }
            
            .welcome-text {
                margin-bottom: 12px;
            }
            
            .welcome-text h3 {
                font-size: 1.1rem;
            }
            
            .form-group {
                margin-bottom: 10px;
            }
            
            .options-row {
                margin-bottom: 12px;
            }
            
            .divider {
                margin: 12px 0;
            }
            
            .social-login {
                margin-bottom: 12px;
            }
            
            .register-link {
                margin-top: 10px;
                padding-top: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    
    <div class="login-container">
        <div class="login-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-wrapper">
                    <img src="assets/upnvylogo.png" alt="Logo">
                    <h2>UPNFOOD</h2>
                </div>
                
                <p>Platform pesan antar makanan resmi civitas UPN</p>
            </div>
            
            <!-- Welcome Text -->
            <div class="welcome-text">
                <h3>Selamat Datang Kembali! 👋</h3>
                <p>Login untuk mulai pesan makanan favoritmu</p>
            </div>
            
            <!-- Error Alert -->
            <?php if($error): ?>
                <div class="alert-premium alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-person-circle"></i> Username atau Email
                    </label>
                    <div class="input-group-modern">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" 
                               class="form-control-modern" 
                               name="username" 
                               placeholder="Masukkan username atau email"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-lock-fill"></i> Password
                    </label>
                    <div class="input-group-modern">
                        <i class="bi bi-key input-icon"></i>
                        <input type="password" 
                               class="form-control-modern" 
                               name="password" 
                               id="password"
                               placeholder="Masukkan password"
                               required>
                    </div>
                </div>
                
                <div class="options-row">
                    <label class="checkbox-custom">
                        <input type="checkbox" name="remember"> Ingat saya
                    </label>
                    <a href="forgot-password.php" class="forgot-link">Lupa password?</a>
                </div>
                
                <a href="index.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
                
                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Login Sekarang
                </button>
            </form>
            
            <div class="divider">
                <span>atau login dengan</span>
            </div>
            
            <div class="social-login">
                <a href="#" class="social-btn">
                    <i class="bi bi-google"></i>
                </a>
                <a href="#" class="social-btn">
                    <i class="bi bi-facebook"></i>
                </a>
                <a href="#" class="social-btn">
                    <i class="bi bi-apple"></i>
                </a>
            </div>
            
            <div class="register-link">
                <p>Belum punya akun premium? <a href="register.php">Daftar sekarang <i class="bi bi-arrow-right"></i></a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>