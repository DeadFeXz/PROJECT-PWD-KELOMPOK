<?php
session_start();
require_once 'config/database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// ========== JOIN: Ambil data user ==========
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT * FROM users WHERE id = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);
$user_data = mysqli_fetch_assoc($result_user);

$order_success = false;
$order_error = false;
$selected_menu = null;

// ========== JOIN: Ambil data menu dari database ==========
$menu_items = [];
$sql_menu_all = "SELECT * FROM menu ORDER BY id ASC";
$result_menu_all = mysqli_query($conn, $sql_menu_all);
while ($row = mysqli_fetch_assoc($result_menu_all)) {
    $menu_items[$row['id']] = $row;
}

// Ambil data menu dari URL
if (isset($_GET['id']) && isset($menu_items[$_GET['id']])) {
    $selected_menu = $menu_items[$_GET['id']];
}

// Proses submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menu_id = (int)$_POST['menu_id'];
    $menu_nama = mysqli_real_escape_string($conn, $_POST['menu_nama']);
    $quantity = (int)$_POST['quantity'];
    $harga = (int)$_POST['harga'];
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    
    $nama_pemesan = mysqli_real_escape_string($conn, $_POST['nama_pemesan']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_telpon = mysqli_real_escape_string($conn, $_POST['no_telpon']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $errors = [];
    if (empty($nama_pemesan)) $errors[] = "Nama pemesan harus diisi";
    if (empty($alamat)) $errors[] = "Alamat harus diisi";
    if (empty($no_telpon)) $errors[] = "No telpon harus diisi";
    if (empty($email)) $errors[] = "Email harus diisi";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid";
    if ($quantity < 1) $errors[] = "Jumlah pesanan minimal 1";
    
    if (empty($errors)) {
        $total_harga = $quantity * $harga;
        
        // ========== INSERT dengan user_id (JOIN ke users) ==========
        $sql = "INSERT INTO orders (user_id, menu_id, menu_nama, quantity, harga, total_harga, nama_pemesan, alamat, no_telpon, email, catatan, status) 
                VALUES ($user_id, $menu_id, '$menu_nama', $quantity, $harga, $total_harga, '$nama_pemesan', '$alamat', '$no_telpon', '$email', '$catatan', 'pending')";
        
        if (mysqli_query($conn, $sql)) {
            $order_success = true;
            
            // Update terjual
            mysqli_query($conn, "UPDATE menu SET terjual = terjual + $quantity WHERE id = $menu_id");
            
            $order_data = [
                'menu_nama' => $menu_nama,
                'quantity' => $quantity,
                'total_harga' => $total_harga,
                'nama_pemesan' => $nama_pemesan,
                'alamat' => $alamat,
                'no_telpon' => $no_telpon,
                'email' => $email,
                'catatan' => $catatan,
                'order_id' => mysqli_insert_id($conn)
            ];
        } else {
            $order_error = true;
            $error_message = "Gagal menyimpan pesanan!";
        }
    } else {
        $order_error = true;
        $error_message = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Pesan | UPNFOODHEMAT</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="https://upload.wikimedia.org/wikipedia/id/0/0d/Logo_Universitas_Pembangunan_Nasional_Veteran_Yogyakarta.png">
    
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
        
        .order-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .order-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 48px;
            padding: 20px 35px;
            box-shadow: 0 35px 70px -20px rgba(0, 0, 0, 0.5);
            transition: all 0.4s ease;
            overflow: visible;
            max-height: none;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .logo-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(135deg, #fff5f5, #ffffff);
            padding: 8px 24px;
            border-radius: 100px;
            margin-bottom: 8px;
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
        }
        
        .badge-premium {
            background: linear-gradient(135deg, #f5b042, #e6a020);
            color: white;
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 0.65rem;
            font-weight: 700;
            display: inline-block;
        }
        
        .logo-section p {
            font-size: 0.7rem;
            color: #64748b;
            margin-top: 5px;
            margin-bottom: 0;
        }
        
        .menu-info {
            background: linear-gradient(135deg, #fff5f5, #fee2e2);
            border-left: 4px solid var(--primary-red);
            padding: 10px 18px;
            border-radius: 20px;
            margin-bottom: 15px;
        }
        
        .menu-info h5 {
            font-size: 0.9rem;
            margin-bottom: 2px;
        }
        
        .menu-info small {
            font-size: 0.7rem;
        }
        
        .menu-info i {
            font-size: 1.6rem;
        }
        
        .form-group {
            margin-bottom: 10px;
        }
        
        .form-label {
            font-weight: 700;
            font-size: 0.7rem;
            margin-bottom: 4px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .form-label i {
            color: var(--primary-red);
            font-size: 0.75rem;
        }
        
        .required-field::after {
            content: " *";
            color: var(--primary-red);
        }
        
        .input-group-modern {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 0.85rem;
            z-index: 1;
        }
        
        .form-control-modern {
            width: 100%;
            padding: 9px 14px 9px 40px;
            border: 2px solid #e2e8f0;
            border-radius: 24px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control-modern:focus {
            outline: none;
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(237, 39, 57, 0.1);
        }
        
        textarea.form-control-modern {
            padding: 9px 14px 9px 40px;
            min-height: 55px;
        }
        
        .row.g-2 {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 0.5rem;
        }
        
        .btn-order {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 40px;
            font-weight: 800;
            font-size: 0.8rem;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-dark));
            color: white;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-order:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px -8px rgba(237, 39, 57, 0.5);
        }
        
        .btn-back {
            width: 100%;
            padding: 8px;
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
            background: #fff5f5;
        }
        
        .d-flex {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }
        
        .alert-premium {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
            color: #991b1b;
            font-weight: 500;
            font-size: 0.7rem;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary-red);
        }
        
        .alert-info-premium {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
            color: #1e40af;
            font-weight: 500;
            font-size: 0.65rem;
            margin-bottom: 15px;
            border-left: 4px solid #3b82f6;
        }
        
        .success-icon {
            font-size: 55px;
            color: #22c55e;
            margin-bottom: 10px;
        }
        
        .order-summary {
            background: #f8fafc;
            border-radius: 20px;
            padding: 12px;
            margin: 12px 0;
        }
        
        .order-summary table {
            font-size: 0.75rem;
        }
        
        .order-summary h6 {
            font-size: 0.8rem;
            margin-bottom: 8px;
        }
        
        @media (max-width: 768px) {
            .order-container {
                max-width: 95%;
            }
            
            .order-card {
                padding: 15px 22px;
                border-radius: 36px;
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
        }
        
        @media (max-width: 600px) {
            .d-flex {
                flex-direction: column;
                gap: 8px;
            }
            
            .order-card {
                padding: 12px 18px;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    
    <div class="order-container">
        <div class="order-card">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-wrapper">
                    <img src="assets/upnvylogo.png" alt="Logo">
                    <h2>UPNFOOD</h2>
                </div>
               
                <p>Hi, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>!</p>
            </div>
            
            <?php if ($order_success): ?>
                <!-- Halaman Sukses -->
                <div class="text-center">
                    <div class="success-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h4 class="mb-1 fw-bold">Pesanan Berhasil! 🎉</h4>
                    <p class="text-muted small">Terima Kasih, <?= htmlspecialchars($order_data['nama_pemesan']) ?>!</p>
                    <p class="text-muted small">Pesanan Anda telah diterima.</p>
                    <div class="badge-premium mb-2" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                        <i class="bi bi-receipt"></i> #<?= str_pad($order_data['order_id'], 5, '0', STR_PAD_LEFT) ?>
                    </div>
                    
                    <div class="order-summary">
                        <h6 class="fw-bold mb-2"><i class="bi bi-receipt text-danger"></i> Detail Pesanan:</h6>
                        <table class="table table-borderless mb-0">
                            <tr><th class="ps-0">Menu</th><td class="text-end fw-bold"><?= $order_data['menu_nama'] ?> </td></tr>
                            <tr><th class="ps-0">Jumlah</th><td class="text-end"><?= $order_data['quantity'] ?> porsi</td></tr>
                            <tr><th class="ps-0">Total</th><td class="text-end fw-bold text-danger">Rp <?= number_format($order_data['total_harga'], 0, ',', '.') ?></td></tr>
                        <tr>
                    </div>
                    
                    <div class="d-flex mt-2">
                        <a href="upnfoodhemat.php" class="btn-order" style="text-decoration: none;">
                            <i class="bi bi-cart"></i> Pesan Lagi
                        </a>
                        <a href="my-orders.php" class="btn-back" style="text-decoration: none;">
                            <i class="bi bi-receipt"></i> Pesanan Saya
                        </a>
                    </div>
                </div>
                
            <?php else: ?>
                
                <!-- Form Pemesanan -->
                <?php if($order_error): ?>
                    <div class="alert-premium alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <?= isset($error_message) ? $error_message : 'Gagal memesan! Silakan lengkapi data.' ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size: 0.6rem;"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="menu_id" value="<?= $selected_menu ? $selected_menu['id'] : '' ?>">
                    <input type="hidden" name="menu_nama" value="<?= $selected_menu ? $selected_menu['nama'] : '' ?>">
                    <input type="hidden" name="harga" value="<?= $selected_menu ? $selected_menu['harga'] : '' ?>">
                    
                    <?php if(!$selected_menu): ?>
                        <div class="alert-premium text-center">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Silakan pilih menu dari <a href="upnfoodhemat.php" class="fw-bold" style="color: var(--primary-red);">UpnFood HEMAT</a>
                        </div>
                        <a href="upnfoodhemat.php" class="btn-order" style="text-decoration: none;">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    <?php else: ?>
                        <!-- Menu Info -->
                        <div class="menu-info">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-cup-straw text-danger"></i>
                                <div>
                                    <small class="text-muted">Menu yang dipesan:</small>
                                    <h5 class="mb-0 fw-bold"><?= $selected_menu['nama'] ?></h5>
                                    <small class="text-danger fw-bold">Rp <?= number_format($selected_menu['harga'], 0, ',', '.') ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form 2 KOLOM -->
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required-field"><i class="bi bi-person-circle"></i> Nama Pemesan</label>
                                    <div class="input-group-modern">
                                        <i class="bi bi-person input-icon"></i>
                                        <input type="text" class="form-control-modern" name="nama_pemesan" value="<?= htmlspecialchars($_SESSION['nama_lengkap']) ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required-field"><i class="bi bi-telephone"></i> No Telpon</label>
                                    <div class="input-group-modern">
                                        <i class="bi bi-telephone input-icon"></i>
                                        <input type="tel" class="form-control-modern" name="no_telpon" value="<?= htmlspecialchars($user_data['no_telpon'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label required-field"><i class="bi bi-envelope"></i> Email</label>
                                    <div class="input-group-modern">
                                        <i class="bi bi-envelope input-icon"></i>
                                        <input type="email" class="form-control-modern" name="email" value="<?= htmlspecialchars($user_data['username'] ?? '') ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label required-field"><i class="bi bi-geo-alt"></i> Alamat Lengkap</label>
                                    <div class="input-group-modern">
                                        <i class="bi bi-geo-alt input-icon"></i>
                                        <textarea class="form-control-modern" name="alamat" rows="2" required><?= htmlspecialchars($user_data['alamat'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required-field"><i class="bi bi-cart"></i> Jumlah Pesanan</label>
                                    <div class="input-group-modern">
                                        <i class="bi bi-plus-slash-minus input-icon"></i>
                                        <input type="number" class="form-control-modern" name="quantity" value="1" min="1" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"><i class="bi bi-pencil"></i> Catatan</label>
                                    <div class="input-group-modern">
                                        <i class="bi bi-chat-dots input-icon"></i>
                                        <input type="text" class="form-control-modern" name="catatan" placeholder="Tidak pakai pedas, dll">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert-info-premium">
                            <i class="bi bi-info-circle-fill me-1"></i> Minimal pembelian Rp 30.000 GRATIS ONGKIR!
                        </div>
                        
                        <div class="d-flex">
                            <a href="upnfoodhemat.php" class="btn-back" style="text-decoration: none;">
                                <i class="bi bi-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn-order">
                                <i class="bi bi-check-lg"></i> Pesan Sekarang
                            </button>
                        </div>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>