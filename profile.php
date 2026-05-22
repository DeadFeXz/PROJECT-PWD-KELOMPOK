<?php
session_start();
require_once 'config/database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data user langsung dengan query
$sql_user = "SELECT * FROM users WHERE id = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user = mysqli_fetch_assoc($result_user);

// Jika user tidak ditemukan
if (!$user) {
    header('Location: logout.php');
    exit;
}

$success = '';
$error = '';
$current_page = basename($_SERVER['PHP_SELF']);

// Update profil (termasuk username)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_telpon = mysqli_real_escape_string($conn, $_POST['no_telpon'] ?? '');
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat'] ?? '');
    
    // Validasi username tidak boleh kosong
    if (empty($username)) {
        $error = "Username tidak boleh kosong!";
    }
    // Cek username duplikat (kecuali untuk user ini sendiri)
    elseif ($username !== $user['username']) {
        $check_username_sql = "SELECT id FROM users WHERE username = ? AND id != ?";
        $check_username_stmt = mysqli_prepare($conn, $check_username_sql);
        mysqli_stmt_bind_param($check_username_stmt, "si", $username, $user_id);
        mysqli_stmt_execute($check_username_stmt);
        $check_username_result = mysqli_stmt_get_result($check_username_stmt);
        
        if (mysqli_num_rows($check_username_result) > 0) {
            $error = "Username sudah digunakan oleh akun lain!";
        }
    }
    
    // Cek email duplikat
    if (empty($error)) {
        $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "si", $email, $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Email sudah digunakan oleh akun lain!";
        }
    }
    
    if (empty($error)) {
        $update_sql = "UPDATE users SET username = ?, nama_lengkap = ?, email = ?, no_telpon = ?, alamat = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "sssssi", $username, $nama_lengkap, $email, $no_telpon, $alamat, $user_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;
            $success = "Profil berhasil diupdate!";
            
            // Refresh data user
            $refresh_sql = "SELECT * FROM users WHERE id = ?";
            $refresh_stmt = mysqli_prepare($conn, $refresh_sql);
            mysqli_stmt_bind_param($refresh_stmt, "i", $user_id);
            mysqli_stmt_execute($refresh_stmt);
            $refresh_result = mysqli_stmt_get_result($refresh_stmt);
            $user = mysqli_fetch_assoc($refresh_result);
        } else {
            $error = "Gagal update profil!";
        }
    }
}

// Ganti password 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    if ($current === $user['password']) {
        if ($new === $confirm) {
            if (strlen($new) >= 4) {
                // Update password langsung sebagai plain text
                $update_pass_sql = "UPDATE users SET password = ? WHERE id = ?";
                $update_pass_stmt = mysqli_prepare($conn, $update_pass_sql);
                mysqli_stmt_bind_param($update_pass_stmt, "si", $new, $user_id);
                
                if (mysqli_stmt_execute($update_pass_stmt)) {
                    $success = "Password berhasil diubah!";
                } else {
                    $error = "Gagal mengubah password!";
                }
            } else {
                $error = "Password baru minimal 4 karakter!";
            }
        } else {
            $error = "Password baru tidak cocok!";
        }
    } else {
        $error = "Password saat ini salah!";
    }
}

// Hapus akun
if (isset($_GET['delete'])) {
    // Hapus data terkait (orders dll) 
    mysqli_query($conn, "DELETE FROM orders WHERE user_id = $user_id");
    mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
    session_destroy();
    header('Location: register.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - UpnFood</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="assets/upnvylogo.png">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 50px auto;
        }
        .profile-card {
            border-radius: 24px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .profile-header {
            background: linear-gradient(135deg, #ed2739, #c41e2d);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .profile-body {
            padding: 30px;
        }
        .btn-save {
            background: #ed2739;
            border: none;
            padding: 10px 24px;
            border-radius: 30px;
        }
        .btn-save:hover {
            background: #c41e2d;
        }
        .btn-delete {
            background: #dc3545;
            border: none;
            padding: 10px 24px;
            border-radius: 30px;
        }
        .btn-delete:hover {
            background: #bb2d3b;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm" id="mainNavbar">
    <div class="container">
        <!-- Logo di KIRI -->
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="assets/upnvylogo.png" alt="Logo" height="40">
            <span class="fw-bold ms-2 fs-4 text-danger">UpnFood</span>
        </a>
        
        <!-- Tombol Toggler untuk mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Menu TENGAH -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link fw-bold <?= ($current_page == 'index.php') ? 'active-red' : '' ?>" href="index.php">Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-bold <?= ($current_page == 'upnfoodhemat.php') ? 'active-red' : '' ?>" href="upnfoodhemat.php">
                        UpnFood HEMAT <span class="badge bg-danger rounded-pill">Baru</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Bagian KANAN (Profile/Login) -->
        <div class="d-flex align-items-center" style="gap: 10px;">
            <i class="bi bi-search fs-5 cursor-pointer" style="cursor: pointer;"></i>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-success rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profil Saya</a></li>
                        <li><a class="dropdown-item" href="my-orders.php"><i class="bi bi-receipt"></i> Pesanan Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-success rounded-pill px-3 me-2 fw-bold">Masuk</a>
                <a href="register.php" class="btn btn-outline-success rounded-pill px-3 fw-bold">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>


<div class="container profile-container">
    <div class="card profile-card">
        <div class="profile-header">
            <i class="bi bi-person-circle fs-1"></i>
            <h3 class="mt-2 mb-0">Profil Saya</h3>
            <p class="mb-0 opacity-75">@<?= htmlspecialchars($user['username']) ?></p>
        </div>
        <div class="profile-body">
            
            <?php if($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Form Edit Profil (dengan username bisa diubah) -->
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                    <small class="text-muted">Username</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" 
                    required>
                    <small class="text-muted">Email</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                    <small class="text-muted">Nama Lengkap</small>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Bergabung Sejak</label>
                    <input type="text" class="form-control bg-light" value="<?= date('d F Y', strtotime($user['created_at'])) ?>" disabled>
                </div>
                
                <button type="submit" name="update_profile" class="btn btn-save text-white rounded-pill px-4">
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
            </form>
            
            <hr class="my-4">
            
            <!-- Form Ganti Password -->
            <h5 class="fw-bold mb-3"><i class="bi bi-key me-2 text-danger"></i> Ganti Password</h5>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Password Saat Ini</label>
                    <input type="password" name="current_password" class="form-control" required 
                        placeholder="Masukkan password Anda saat ini">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="new_password" class="form-control" required 
                        placeholder="Minimal 4 karakter">
                    <small class="text-muted">Minimal 4 karakter</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" class="form-control" required 
                        placeholder="Ketik ulang password baru Anda">
                </div>
                <button type="submit" name="change_password" class="btn btn-warning rounded-pill px-4">
                    <i class="bi bi-shield-lock"></i> Ganti Password
                </button>
            </form>
            
            <hr class="my-4">
            
            <!-- Hapus Akun -->
            <div class="text-center">
                <p class="text-muted small">⚠️ Menghapus akun akan menghapus semua data Anda secara permanen</p>
                <a href="?delete=1" class="btn btn-delete text-white rounded-pill px-4" onclick="return confirm('Yakin ingin menghapus akun? Data tidak bisa dikembalikan!')">
                    <i class="bi bi-trash"></i> Hapus Akun
                </a>
            </div>
        </div>
    </div>
</div>

<footer class="bg-white pt-5 mt-5 border-top position-relative overflow-hidden">
        <div class="footer-circle-decoration d-none d-lg-block"></div>

        <div class="container position-relative z-index-2">
            <div class="row bg-danger rounded-5 p-4 p-md-5 mb-5 align-items-center text-white overflow-hidden position-relative shadow-lg">
                <div class="col-lg-7">
                    <h2 class="fw-800 mb-3">Makin Hemat Pakai Aplikasi <span class="text-warning">UpnFood</span></h2>
                    <p class="lead opacity-90 mb-4">Dapatkan promo eksklusif, pelacakan pesanan real-time, dan gratis ongkir khusus pengguna aplikasi pertama kali!</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#" class="store-badge">
                            <img src="assets/gplay.png" alt="Play Store" height="45">
                        </a>
                        <a href="#" class="store-badge">
                            <img src="assets/appstoree.png" alt="App Store" height="45">
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block text-end footer-phone-mockup">
                    <i class="bi bi-phone-vibrate text-white"></i>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center mb-4">
                        <img src="assets/upnvylogo.png" alt="Logo" height="50">
                        <div class="ms-3">
                            <h4 class="fw-800 mb-0 text-danger">UpnFood</h4>
                            <small class="text-muted fw-bold">Solusi Makan Civitas UPN</small>
                        </div>
                    </div>
                    <p class="text-muted pe-lg-4">Platform pesan antar makanan resmi untuk memudahkan mahasiswa dan dosen mendapatkan asupan energi terbaik tanpa ribet.</p>
                    <div class="d-flex gap-2 mt-4">
                        <a href="#" class="filter-btn active rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="filter-btn rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="bi bi-tiktok"></i></a>
                        <a href="#" class="filter-btn rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="bi bi-facebook"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="fw-800 text-dark mb-4">Navigasi</h6>
                    <ul class="list-unstyled text-muted footer-links">
                        <li class="mb-2"><a href="index.php" class="text-decoration-none text-muted">Beranda</a></li>
                        <li class="mb-2"><a href="upnfoodhemat.php" class="text-decoration-none text-muted fw-bold text-danger">UpnFood HEMAT</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-6 col-6">
                    <h6 class="fw-800 text-dark mb-4">Bantuan</h6>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Pusat Bantuan</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Cara Pesan</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-muted">FAQ</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Kontak</a></li>
                    </ul>
                </div>

                <div class="col-lg-4">
                    <h6 class="fw-800 text-dark mb-4">Lokasi Kami</h6>
                    <div class="d-flex mb-3">
                        <i class="bi bi-geo-alt-fill text-danger fs-5 me-3"></i>
                        <p class="text-muted small mb-0">Kampus Terpadu UPN "Veteran" Yogyakarta, Jl. SWK Jl. Ring Road Utara, Condongcatur, Sleman.</p>
                    </div>
                    <div class="d-flex align-items-center bg-light p-3 rounded-4">
                        <i class="bi bi-headset text-danger fs-3 me-3"></i>
                        <div>
                            <small class="d-block text-muted">Butuh bantuan cepat?</small>
                            <span class="fw-bold text-dark">+62 812-3456-7890</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="py-4 border-top">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0 text-muted small">© 2026 <span class="fw-bold text-danger">UpnFood</span>. Merek milik Universitas Pembangunan Nasional.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                        <img src="assets/upnvylogo.png" alt="UPN" height="25" class="opacity-50 grayscale me-3">
                        <a href="#" class="text-decoration-none text-muted small me-3">Terms</a>
                        <a href="#" class="text-decoration-none text-muted small">Privacy</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>