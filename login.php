<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users 
            WHERE username='$username' 
            OR email='$username'";

    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && $password === $user['password']) {

        $_SESSION['user_id']      = $user['id'];
        $_SESSION['username']     = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['email']        = $user['email'];

        $redirect = isset($_GET['redirect'])
            ? $_GET['redirect']
            : 'index.php';

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | UPNFOODHEMAT</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<link rel="icon" type="image/png" href="assets/upnvylogo.png">

</head>

<body class="login-page">

<div class="login-container">

    <div class="login-card">

        <!-- logo -->
        <div class="logo-section">

            <div class="logo-wrapper">
                <img src="assets/upnvylogo.png" alt="UPNVY Logo">
            </div>

            <h2>UPNFOOD</h2>
            <p>Login untuk melanjutkan pemesanan makanan</p>

        </div>

        <!-- error -->
        <?php if($error): ?>
    <div class="popup-overlay">
        <div class="popup-box">
            <i class="bi bi-exclamation-triangle-fill popup-icon"></i>
            <h3>Pendaftaran Gagal</h3>
            <p><?= $error ?></p>

            <a href="login.php" class="popup-btn">
                Tutup
            </a>
        </div>
    </div>
    <?php endif; ?>

        <!-- form -->
        <form method="POST">

            <div class="form-group">

                <div class="form-label">
                    <i class="bi bi-person"></i>
                    Username / Email
                </div>

                <div class="input-group-modern">
                    <i class="bi bi-person input-icon"></i>

                    <input
                        type="text"
                        name="username"
                        class="form-control-modern"
                        placeholder="Masukkan username atau email"
                        required
                    >
                </div>

            </div>

            <div class="form-group">

                <div class="form-label">
                    <i class="bi bi-lock"></i>
                    Password
                </div>

                <div class="input-group-modern">
                    <i class="bi bi-key input-icon"></i>

                    <input
                        type="password"
                        name="password"
                        class="form-control-modern"
                        placeholder="Masukkan password"
                        required
                    >
                </div>

            </div>

            <div class="options-row">

                <label class="checkbox-custom">
                    <input type="checkbox" name="remember">
                    Ingat saya
                </label>

                <a href="forgot-password.php" class="forgot-link">
                    Lupa password?
                </a>

            </div>

            <div class="button-group">

                <a href="index.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i>
                    Kembali
                </a>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Login
                </button>

            </div>

        </form>

        <div class="register-box">
            Belum punya akun?
            <a href="register.php">Daftar disini</a>
        </div>

    </div>

</div>

</body>
</html>