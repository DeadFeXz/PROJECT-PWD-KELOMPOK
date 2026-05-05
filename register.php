<?php
session_start();
require_once 'config/database.php';

// Jika sudah login, redirect ke index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    
    // Validasi
    $errors = [];
    
    if (empty($username)) $errors[] = "Username harus diisi";
    elseif (strlen($username) < 3) $errors[] = "Username minimal 3 karakter";
    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = "Username hanya boleh huruf, angka, dan underscore";
    
    if (empty($email)) $errors[] = "Email harus diisi";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid";
    
    if (empty($password)) $errors[] = "Password harus diisi";
    elseif (strlen($password) < 4) $errors[] = "Password minimal 4 karakter";
    elseif ($password !== $confirm) $errors[] = "Password dan konfirmasi tidak cocok";
    
    if (empty($nama_lengkap)) $errors[] = "Nama lengkap harus diisi";
    
    if (empty($errors)) {
        // Cek username/email sudah ada
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' OR email = '$email'");
        
        if (mysqli_num_rows($check) > 0) {
            $error = "Username atau email sudah terdaftar!";
        } else {
            
            $sql = "INSERT INTO users (username, email, password, nama_lengkap) 
                    VALUES ('$username', '$email', '$password', '$nama_lengkap')";
            
            if (mysqli_query($conn, $sql)) {
                // Ambil data user yang baru dibuat
                $user_id = mysqli_insert_id($conn);
                
                // Set session auto login
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $_SESSION['email'] = $email;
                
                // Redirect ke index
                header('Location: index.php');
                exit;
            } else {
                $error = "Pendaftaran gagal! Silakan coba lagi.";
            }
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Daftar | UPNFOODHEMAT - Elite Culinary Experience</title>
    
    <!-- Bootstrap 5 + Icons + Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/upnvylogo.png">
    
    <style>
        :root {
            --primary-red: #ed2739;
            --primary-dark: #b91c2c;
            --premium-gold: #f5b042;
            --dark-bg: #0f0f1a;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
        
        /* Animated Background Elements */
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
        
        /* Main Register Card */
        .register-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 48px;
            padding: 28px 32px;
            box-shadow: 0 35px 70px -20px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255,255,255,0.1);
            transition: all 0.4s ease;
        }
        
        /* Logo Section - COMPACT */
        .logo-section {
            text-align: center;
            margin-bottom: 18px;
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
        
        /* Form Styling - COMPACT */
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
        
        .form-control-modern.error {
            border-color: #ef4444;
            background: #fef2f2;
        }
        
        /* Password Toggle - Tanpa JavaScript (hidden) */
        .password-toggle {
            display: none;
        }
        
        /* Button Styles - COMPACT */
        .btn-register {
            width: 100%;
            padding: 11px;
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
            position: relative;
            overflow: hidden;
        }
        
        .btn-register::before {
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
        
        .btn-register:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-register:hover {
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
        }
        
        .btn-back:hover {
            border-color: var(--primary-red);
            color: var(--primary-red);
            transform: translateY(-2px);
            background: #fff5f5;
        }
        
        .d-flex {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }
        
        /* Divider - COMPACT */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 16px 0;
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
        
        /* Login Link - COMPACT */
        .login-link {
            text-align: center;
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid #e2e8f0;
        }
        
        .login-link p {
            color: #64748b;
            font-size: 0.75rem;
            margin: 0;
        }
        
        .login-link a {
            color: var(--primary-red);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        /* Alert Styling - COMPACT */
        .alert-premium {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: none;
            border-radius: 20px;
            padding: 10px 16px;
            color: #991b1b;
            font-weight: 500;
            font-size: 0.75rem;
            margin-bottom: 16px;
            border-left: 4px solid var(--primary-red);
        }
        
        .alert-premium .btn-close {
            font-size: 0.6rem;
            padding: 0.5rem;
        }
        
        /* Small text - COMPACT */
        .form-text {
            font-size: 0.65rem;
            color: #94a3b8;
            margin-top: 4px;
            margin-left: 16px;
        }
        
        /* Terms Check - COMPACT */
        .terms-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 14px 0;
            font-size: 0.7rem;
        }
        
        .terms-check input {
            width: 14px;
            height: 14px;
            accent-color: var(--primary-red);
        }
        
        .terms-check a {
            color: var(--primary-red);
            text-decoration: none;
            font-weight: 600;
        }
        
        /* Animation */
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
        
        .register-card {
            animation: fadeInUp 0.5s ease-out;
        }
        
        /* Responsive untuk layar kecil */
        @media (max-width: 520px) {
            .register-card {
                padding: 22px 24px;
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
            
            .d-flex {
                gap: 10px;
            }
            
            .btn-register, .btn-back {
                padding: 9px;
            }
        }
        
        /* Untuk layar sangat pendek */
        @media (max-height: 680px) {
            .register-card {
                padding: 18px 28px;
            }
            
            .logo-section {
                margin-bottom: 10px;
            }
            
            .logo-wrapper {
                padding: 5px 16px;
                margin-bottom: 8px;
            }
            
            .logo-wrapper img {
                height: 28px;
            }
            
            .logo-wrapper h2 {
                font-size: 1.2rem;
            }
            
            .form-group {
                margin-bottom: 10px;
            }
            
            .terms-check {
                margin: 10px 0;
            }
            
            .divider {
                margin: 12px 0;
            }
            
            .login-link {
                margin-top: 12px;
                padding-top: 10px;
            }
        }
    </style>
</head>
<body>
   
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    
    <div class="register-container">
        <div class="register-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-wrapper">
                    <img src="assets/upnvylogo.png" alt="Logo">
                    <h2>UPNFOOD</h2>
                </div>
                
                <p>Bergabunglah dengan komunitas pecinta kuliner premium</p>
            </div>
            
            <!-- Error Alert -->
            <?php if($error): ?>
                <div class="alert-premium alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Register Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-person-circle"></i> Username
                    </label>
                    <div class="input-group-modern">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" 
                               class="form-control-modern" 
                               name="username" 
                               placeholder="contoh: john_doe"
                               value="<?= isset($username) ? htmlspecialchars($username) : '' ?>"
                               required>
                    </div>
                    <div class="form-text">
                        <i class="bi bi-info-circle-fill me-1"></i> Minimal 3 karakter, huruf/angka/underscore
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-envelope-fill"></i> Email
                    </label>
                    <div class="input-group-modern">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" 
                               class="form-control-modern" 
                               name="email" 
                               placeholder="email@example.com"
                               value="<?= isset($email) ? htmlspecialchars($email) : '' ?>"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-card-text"></i> Nama Lengkap
                    </label>
                    <div class="input-group-modern">
                        <i class="bi bi-person-badge input-icon"></i>
                        <input type="text" 
                               class="form-control-modern" 
                               name="nama_lengkap" 
                               placeholder="Nama lengkap sesuai KTP"
                               value="<?= isset($nama_lengkap) ? htmlspecialchars($nama_lengkap) : '' ?>"
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
                               placeholder="Minimal 4 karakter"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="bi bi-shield-lock-fill"></i> Konfirmasi Password
                    </label>
                    <div class="input-group-modern">
                        <i class="bi bi-check-circle input-icon"></i>
                        <input type="password" 
                               class="form-control-modern" 
                               name="confirm_password" 
                               id="confirm_password"
                               placeholder="Ketik ulang password"
                               required>
                    </div>
                </div>
                
                <!-- Terms & Conditions -->
                <div class="terms-check">
                    <input type="checkbox" id="terms" required>
                    <label for="terms">
                        Saya menyetujui <a href="#">Syarat & Ketentuan</a> dan <a href="#">Kebijakan Privasi</a>
                    </label>
                </div>
                
                <div class="d-flex">
                    <a href="index.php" class="btn-back">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn-register">
                        <i class="bi bi-person-plus-fill"></i> Daftar
                    </button>
                </div>
            </form>
            
            <div class="divider">
                <span>atau</span>
            </div>
            
            <div class="login-link">
                <p>Sudah punya akun premium? <a href="login.php">Login di sini <i class="bi bi-arrow-right"></i></a></p>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>