<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username       = mysqli_real_escape_string($conn, $_POST['username']);
    $email          = mysqli_real_escape_string($conn, $_POST['email']);
    $password       = $_POST['password'];
    $confirm        = $_POST['confirm_password'];
    $nama_lengkap   = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);

    $errors = [];

    if (empty($username)) {
        $errors[] = "Username harus diisi";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter";
    }

    if (empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }

    if (empty($password)) {
        $errors[] = "Password harus diisi";
    } elseif (strlen($password) < 4) {
        $errors[] = "Password minimal 4 karakter";
    }

    if ($password !== $confirm) {
        $errors[] = "Konfirmasi password tidak cocok";
    }

    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap harus diisi";
    }

    if (empty($errors)) {

        $check = mysqli_query($conn,
            "SELECT id FROM users 
             WHERE username='$username' 
             OR email='$email'"
        );

        if (mysqli_num_rows($check) > 0) {
            $error = "Username atau email sudah terdaftar!";
        } else {

            $sql = "INSERT INTO users 
                    (username,email,password,nama_lengkap)
                    VALUES
                    ('$username','$email','$password','$nama_lengkap')";

            if (mysqli_query($conn, $sql)) {

                $user_id = mysqli_insert_id($conn);

                $_SESSION['user_id']      = $user_id;
                $_SESSION['username']     = $username;
                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $_SESSION['email']        = $email;

                header('Location: index.php');
                exit;

            } else {
                $error = "Pendaftaran gagal!";
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar | UPNFOODHEMAT</title>

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
.register-container{
    width:100%;
    max-width:860px;
    height:100%;
    display:flex;
    justify-content:center;
    align-items:center;
    position:relative;
    z-index:2;
}

/* card */
.register-card{
    width:100%;
    max-height:95vh;
    overflow:hidden;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.08);
    backdrop-filter:blur(30px);
    border-radius:35px;
    padding:32px 35px;
    box-shadow:0 20px 60px rgba(0,0,0,.45),
               inset 0 1px 0 rgba(255,255,255,.04);
}

/* logo */
.logo-section{
    text-align:center;
    margin-bottom:25px;
}

.logo-wrapper{
    width:85px;
    height:85px;
    margin:auto;
    display:flex;
    justify-content:center;
    align-items:center;
    margin-bottom:15px;
    background:transparent;
    box-shadow:none;
}

.logo-wrapper img{
    width:102px;
    height:102px;
    object-fit:contain;
    display:block;
}


.logo-section h2{
    color:white;
    font-size:2rem;
    font-weight:800;
    margin-bottom:5px;
}

.logo-section p{
    color:rgba(255,255,255,.6);
    font-size:.9rem;
}

/* grid */
.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

.form-group.full{
    grid-column:1 / -1;
}

/* label */
.form-label{
    color:white;
    font-size:.82rem;
    font-weight:700;
    margin-bottom:8px;
    display:flex;
    align-items:center;
    gap:8px;
}

.form-label i{
    color:var(--premium-gold);
}

/* input */
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
    height:55px;
    border:none;
    outline:none;
    border-radius:18px;
    background:rgba(255,255,255,.07);
    border:1px solid rgba(255,255,255,.08);
    padding:0 18px 0 50px;
    color:white;
    font-size:.9rem;
}

/* button */
.button-group{
    display:flex;
    gap:15px;
    margin-top:25px;
}

.btn-register,
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
}

.btn-register{
    background:linear-gradient(135deg,var(--primary-red),var(--primary-dark));
    color:white;
}

.btn-back{
    background:rgba(255,255,255,.07);
    color:white;
}

/* login */
.login-box{
    text-align:center;
    margin-top:20px;
    color:rgba(255,255,255,.7);
}

.login-box a{
    color:var(--premium-gold);
    text-decoration:none;
    font-weight:700;
}

/* responsive */
@media(max-width:768px){
    .form-grid{
        grid-template-columns:1fr;
    }
}

</style>
</head>

<body>

<div class="register-container">
    <div class="register-card">

        <div class="logo-section">
            <div class="logo-wrapper">
                <img src="assets/upnvylogo.png" alt="UPNVY Logo">
            </div>
            <h2>UPNFOOD</h2>
            <p>Gabung Komunitas kuliner</p>
        </div>

        <?php if($error): ?>
            <div style="color:#ffd7d7;margin-bottom:15px;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-grid">

                <div class="form-group">
                    <div class="form-label"><i class="bi bi-person"></i>Username</div>
                    <div class="input-group-modern">
                       <input type="text" name="username" placeholder="Username harus diisi" class="form-control-modern" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label"><i class="bi bi-envelope"></i>Email</div>
                    <div class="input-group-modern">
                        <input type="email" name="email" placeholder="Email harus diisi" class="form-control-modern" required>
                    </div>
                </div>

                <div class="form-group full">
                    <div class="form-label"><i class="bi bi-person-badge"></i>Nama Lengkap</div>
                    <div class="input-group-modern">
                        <input type="text" name="nama_lengkap" placeholder="Nama lengkap harus diisi" class="form-control-modern" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label"><i class="bi bi-lock"></i>Password</div>
                    <div class="input-group-modern">
                        <input type="password" name="password" placeholder="Minimal 4 karakter" class="form-control-modern" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label"><i class="bi bi-shield-lock"></i>Konfirmasi</div>
                    <div class="input-group-modern">
                        <input type="password" name="confirm_password" class="form-control-modern" required>
                    </div>
                </div>

            </div>

            <div class="button-group">
                <a href="index.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>

                <button type="submit" class="btn-register">
                    <i class="bi bi-person-plus"></i> Daftar
                </button>
            </div>

        </form>

        <div class="login-box">
            Sudah punya akun?
            <a href="login.php">Login disini</a>
        </div>

    </div>
</div>

</body>
</html>