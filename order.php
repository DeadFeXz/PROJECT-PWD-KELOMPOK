<?php
session_start();
require_once 'config/database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ambil data user 
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

// Ambil data menu dari database 
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
    
    // Validasi No Telpon yang diperbaiki
    if (empty($no_telpon)) {
        $errors[] = "No telpon harus diisi";
    } elseif (!preg_match('/^[0-9\-\+\s]{8,15}$/', $no_telpon)) {
        $errors[] = "No telpon tidak valid! Minimal 8 digit, maksimal 15 digit. Hanya boleh berisi angka, +, -, dan spasi.";
    }
    
    if (empty($email)) $errors[] = "Email harus diisi";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid";
    if ($quantity < 1) $errors[] = "Jumlah pesanan minimal 1";
    
    if (empty($errors)) {
        $total_harga = $quantity * $harga;
        
        // INSERT dengan user_id 
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
    <link rel="stylesheet" href="style.css">
</head>
<body style="background: radial-gradient(circle at top left, rgba(237,39,57,.15), transparent 25%),
            radial-gradient(circle at bottom right, rgba(245,176,66,.12), transparent 25%),
            linear-gradient(135deg,#0f0f1a,#171727,#1d1d33);
            position: relative;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;"
            >
    
    <div class="order-container">
        <div class="order-card">
            <!-- Logo Section -->
            <div class="logo-section">
                    <img src="assets/upnvylogo.png" alt="Logo">
                    <h2>UPNFOOD</h2>
                    <h2></h2>
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
                        </table>
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
                                    <h5 class="mb-0 fw-bold"><?= htmlspecialchars($selected_menu['nama']) ?></h5>
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
                                        <input type="tel" class="form-control-modern" name="no_telpon" value="<?= htmlspecialchars($user_data['no_telpon'] ?? '') ?>" maxlength="15" placeholder="08123456789 atau +628123456789" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label required-field"><i class="bi bi-envelope"></i> Email</label>
                                    <div class="input-group-modern">
                                        <i class="bi bi-envelope input-icon"></i>
                                        <input type="email" class="form-control-modern" name="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required>
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