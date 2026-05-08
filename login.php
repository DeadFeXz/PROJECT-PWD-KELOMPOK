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

<style>

:root{
    --primary-red:#ed2739;
    --primary-dark:#b91c2c;
    --premium-gold:#f5b042;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

html,body{
    width:100%;
    height:100%;
    overflow:hidden;
    font-family:'Plus Jakarta Sans',sans-serif;
}

body{
    display:flex;
    justify-content:center;
    align-items:center;
    padding:15px;
    background:
    radial-gradient(circle at top left, rgba(237,39,57,.15), transparent 25%),
    radial-gradient(circle at bottom right, rgba(245,176,66,.12), transparent 25%),
    linear-gradient(135deg,#0f0f1a,#171727,#1d1d33);
    position:relative;
}

/* glow */
body::before{
    content:'';
    position:absolute;
    width:300px;
    height:300px;
    background:rgba(237,39,57,.12);
    border-radius:50%;
    top:-100px;
    left:-100px;
    filter:blur(90px);
}

body::after{
    content:'';
    position:absolute;
    width:250px;
    height:250px;
    background:rgba(245,176,66,.10);
    border-radius:50%;
    bottom:-100px;
    right:-100px;
    filter:blur(90px);
}

/* container */
.login-container{
    width:100%;
    max-width:500px;
    position:relative;
    z-index:2;
}

/* card */
.login-card{
    width:100%;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.08);
    backdrop-filter:blur(30px);
    border-radius:35px;
    padding:35px;
    box-shadow:
    0 20px 60px rgba(0,0,0,.45),
    inset 0 1px 0 rgba(255,255,255,.04);
}

/* logo */
.logo-section{
    text-align:center;
    margin-bottom:28px;
}

.logo-wrapper{
    width:90px;
    height:90px;
    margin:auto;
    display:flex;
    justify-content:center;
    align-items:center;
    margin-bottom:15px;
}

.logo-wrapper img{
    width:105px;
    height:105px;
    object-fit:contain;
}

.logo-section h2{
    color:white;
    font-size:2rem;
    font-weight:800;
    margin-bottom:5px;
}

.logo-section p{
    color:rgba(255,255,255,.65);
    font-size:.9rem;
}

/* alert */
.error-box{
    background:rgba(237,39,57,.12);
    border:1px solid rgba(237,39,57,.25);
    color:#ffd4d4;
    padding:14px 18px;
    border-radius:16px;
    margin-bottom:20px;
    font-size:.88rem;
}

/* label */
.form-label{
    color:white;
    font-size:.83rem;
    font-weight:700;
    margin-bottom:8px;
    display:flex;
    align-items:center;
    gap:8px;
}

.form-label i{
    color:var(--premium-gold);
}

/* form */
.form-group{
    margin-bottom:18px;
}

.input-group-modern{
    position:relative;
}

.input-icon{
    position:absolute;
    top:50%;
    left:18px;
    transform:translateY(-50%);
    color:rgba(255,255,255,.4);
}

.form-control-modern{
    width:100%;
    height:56px;
    border:none;
    outline:none;
    border-radius:18px;
    background:rgba(255,255,255,.07);
    border:1px solid rgba(255,255,255,.08);
    padding:0 18px 0 50px;
    color:white;
    font-size:.92rem;
    transition:.25s;
}

.form-control-modern:focus{
    border-color:rgba(245,176,66,.5);
    background:rgba(255,255,255,.09);
}

.form-control-modern::placeholder{
    color:rgba(255,255,255,.45);
}

/* options */
.options-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:22px;
    margin-top:5px;
}

.checkbox-custom{
    display:flex;
    align-items:center;
    gap:8px;
    color:rgba(255,255,255,.75);
    font-size:.82rem;
}

.checkbox-custom input{
    accent-color:var(--primary-red);
}

.forgot-link{
    color:var(--premium-gold);
    text-decoration:none;
    font-size:.82rem;
    font-weight:600;
}

/* buttons */
.button-group{
    display:flex;
    gap:15px;
    margin-top:5px;
}

.btn-login,
.btn-back{
    flex:1;
    height:54px;
    border:none;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    font-weight:700;
    text-decoration:none;
    transition:.25s;
}

.btn-login{
    background:linear-gradient(135deg,var(--primary-red),var(--primary-dark));
    color:white;
}

.btn-login:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 25px rgba(237,39,57,.3);
}

.btn-back{
    background:rgba(255,255,255,.07);
    color:white;
}

.btn-back:hover{
    background:rgba(255,255,255,.12);
}

/* register */
.register-box{
    text-align:center;
    margin-top:24px;
    color:rgba(255,255,255,.7);
    font-size:.9rem;
}

.register-box a{
    color:var(--premium-gold);
    text-decoration:none;
    font-weight:700;
}

/* responsive */
@media(max-width:576px){

    .login-card{
        padding:28px 24px;
        border-radius:28px;
    }

    .button-group{
        flex-direction:column;
    }

    .logo-section h2{
        font-size:1.7rem;
    }

}

</style>
</head>

<body>

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
            <div class="error-box">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?= $error ?>
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