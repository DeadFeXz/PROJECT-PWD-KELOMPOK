<?php
// Memulai session untuk menyimpan data user setelah login nanti
session_start();

// Memanggil file koneksi database
require_once 'config/database.php';

// Jika session user_id sudah ada (artinya sudah login)
if (isset($_SESSION['user_id'])) {
    // Redirect ke halaman index (tidak boleh register lagi)
    header('Location: index.php');
    exit; // Hentikan eksekusi kode
}

// Variabel untuk menyimpan pesan error
$error = '';

// Cek apakah form sudah disubmit (method POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Membersihkan input dari karakter berbahaya (SQL Injection)
    $username       = mysqli_real_escape_string($conn, $_POST['username']);
    $email          = mysqli_real_escape_string($conn, $_POST['email']);
    $password       = $_POST['password']; // Tidak perlu escape, akan di-hash nanti
    $confirm        = $_POST['confirm_password'];
    $nama_lengkap   = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);

    // Array untuk menampung pesan error
    $errors = [];

    // ========== VALIDASI USERNAME ==========
    if (empty($username)) {
        $errors[] = "Username harus diisi";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter";
    }

    //  VALIDASI EMAIL 
    if (empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // filter_var dengan FILTER_VALIDATE_EMAIL mengecek format email
        $errors[] = "Format email tidak valid";
    }

    //  VALIDASI PASSWORD 
    if (empty($password)) {
        $errors[] = "Password harus diisi";
    } elseif (strlen($password) < 4) {
        $errors[] = "Password minimal 4 karakter";
    }

    //  VALIDASI KONFIRMASI PASSWORD 
    if ($password !== $confirm) {
        $errors[] = "Konfirmasi password tidak cocok";
    }

    //  VALIDASI NAMA LENGKAP 
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap harus diisi";
    }

    // Jika tidak ada error validasi
    if (empty($errors)) {

        // Cek apakah username atau email sudah terdaftar
        $check = mysqli_query($conn,
            "SELECT id FROM users 
             WHERE username='$username' 
             OR email='$email'"
        );

        // Jika ditemukan (>=1) berarti sudah terdaftar
        if (mysqli_num_rows($check) > 0) {
            $error = "Username atau email sudah terdaftar!";
        } else {

            // ⚠️ PERINGATAN: Password disimpan dalam bentuk PLAIN TEXT (TIDAK AMAN!)
            // Seharusnya pakai: password_hash($password, PASSWORD_DEFAULT)
            $sql = "INSERT INTO users 
                    (username, email, password, nama_lengkap)
                    VALUES
                    ('$username', '$email', '$password', '$nama_lengkap')";

            // Eksekusi query INSERT
            if (mysqli_query($conn, $sql)) {

                // Ambil ID user yang baru saja terdaftar
                $user_id = mysqli_insert_id($conn);

                // Simpan data user ke session (langsung login setelah register)
                $_SESSION['user_id']      = $user_id;
                $_SESSION['username']     = $username;
                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $_SESSION['email']        = $email;

                // Redirect ke halaman index
                header('Location: index.php');
                exit;

            } else {
                $error = "Pendaftaran gagal!";
            }
        }

    } else {
        // Gabungkan semua pesan error dengan <br>
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
<link rel="stylesheet" href="style.css">
<link rel="icon" type="image/png" href="assets/upnvylogo.png">

</head>

<body class="register-page">

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
    <div class="popup-overlay">
        <div class="popup-box">
            <i class="bi bi-exclamation-triangle-fill popup-icon"></i>
            <h3>Pendaftaran Gagal</h3>
            <p><?= $error ?></p>

            <a href="register.php" class="popup-btn">
                Tutup
            </a>
        </div>
    </div>
    <?php endif; ?>

        <form method="POST">

            <div class="form-grid">

                <div class="form-group">
                    <div class="form-label"><i class="bi bi-person"></i>Username</div>
                    <div class="input-group-modern">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" name="username" placeholder="Username minimal 3 karakter" class="form-control-modern" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label"><i class="bi bi-envelope"></i>Email</div>
                    <div class="input-group-modern">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" name="email" placeholder="Email harus valid" class="form-control-modern" required>
                    </div>
                </div>

                <div class="form-group full">
                    <div class="form-label"><i class="bi bi-person-badge"></i>Nama Lengkap</div>
                    <div class="input-group-modern">
                        <i class="bi bi-person-badge input-icon"></i>
                        <input type="text" name="nama_lengkap" placeholder="Nama lengkap sesuai identitas" class="form-control-modern" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label"><i class="bi bi-lock"></i>Password</div>
                    <div class="input-group-modern">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" name="password" placeholder="Minimal 4 karakter" class="form-control-modern" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-label"><i class="bi bi-shield-lock"></i>Konfirmasi Password</div>
                    <div class="input-group-modern">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" name="confirm_password" placeholder="Masukkan ulang password" class="form-control-modern" required>
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