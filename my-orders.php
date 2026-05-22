<?php
session_start();
require_once 'config/database.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_id = $_SESSION['user_id'];
$nama_pemesan = $_SESSION['nama_lengkap'];

// ========== INNER JOIN: Ambil semua pesanan user dengan detail menu ==========
// SELECT <field1>, <field2>, <fieldn> FROM <tabel1> INNER JOIN <tabel2> ON <key.tabel1> = <key.tabel2>
$sql = "SELECT o.*, 
               m.nama as menu_original_name,
               m.gambar as menu_image,
               m.resto as resto_name,
               m.rating as menu_rating
        FROM orders o
        INNER JOIN menu m ON o.menu_id = m.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);   
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

// ========== LEFT JOIN:  pesanan user  ==========
$sql_stats = "SELECT u.nama_lengkap,
                     COUNT(o.id) as total_pesanan,
                     COALESCE(SUM(o.total_harga), 0) as total_belanja,
                     MAX(o.created_at) as last_order
              FROM users u
              LEFT JOIN orders o ON u.id = o.user_id AND o.status != 'cancelled'
              WHERE u.id = ?
              GROUP BY u.id";

$stmt_stats = mysqli_prepare($conn, $sql_stats);
mysqli_stmt_bind_param($stmt_stats, "i", $user_id);
mysqli_stmt_execute($stmt_stats);
$stats_result = mysqli_stmt_get_result($stmt_stats);
$user_stats = mysqli_fetch_assoc($stats_result);

// Proses pembatalan pesanan
if (isset($_GET['cancel']) && isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
    
    // Cek apakah pesanan milik user ini dan status pending
    $check_sql = "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'pending'";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "ii", $order_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        $order = mysqli_fetch_assoc($check_result);
        
        // UPDATE status pesanan menjadi cancelled
        $update_sql = "UPDATE orders SET status = 'cancelled' WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "i", $order_id);
        mysqli_stmt_execute($update_stmt);
        
        // UPDATE mengurangi stok terjual di tabel menu
        $update_terjual_sql = "UPDATE menu SET terjual = terjual - ? WHERE id = ?";
        $update_terjual_stmt = mysqli_prepare($conn, $update_terjual_sql);
        mysqli_stmt_bind_param($update_terjual_stmt, "ii", $order['quantity'], $order['menu_id']);
        mysqli_stmt_execute($update_terjual_stmt);
        
        header('Location: my-orders.php?msg=cancelled');
        exit;
    }
}

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'cancelled') {
        $message = '<div class="alert alert-success">Pesanan berhasil dibatalkan!</div>';
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - UpnFood</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="assets/upnvylogo.png">
     
    
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="assets/upnvylogo.png" alt="Logo" height="40">
            <span class="fw-bold ms-2 fs-4 text-danger">UpnFood</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
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

<div class="container orders-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-receipt text-danger me-2"></i> Pesanan Saya</h2>
        <a href="upnfoodhemat.php" class="btn btn-outline-danger rounded-pill">
            <i class="bi bi-plus-circle"></i> Pesan Lagi
        </a>
    </div>
    
    <!-- Statistik Cards (LEFT JOIN) -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                <h3 class="fw-bold mb-0"><?= $user_stats['total_pesanan'] ?? 0 ?></h3>
                <p class="text-muted mb-0">Total Pesanan</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                <h3 class="fw-bold mb-0">Rp <?= number_format($user_stats['total_belanja'] ?? 0, 0, ',', '.') ?></h3>
                <p class="text-muted mb-0">Total Belanja</p>
            </div>
        </div>
    </div>
    
    <?= $message ?>
    
    <!-- Daftar Pesanan (INNER JOIN) -->
    <?php if(count($orders) > 0): ?>
        <?php foreach($orders as $order): ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <span class="fw-bold">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></span>
                    <span class="text-muted ms-3"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></span>
                </div>
                <div>
                    <?php
                    $status = $order['status'];
                    $status_class = '';
                    $status_text = '';
                    
                    switch($status) {
                        case 'pending':
                            $status_class = 'status-pending';
                            $status_text = 'Menunggu Konfirmasi';
                            break;
                        case 'processing':
                            $status_class = 'status-processing';
                            $status_text = 'Sedang Diproses';
                            break;
                        case 'completed':
                            $status_class = 'status-completed';
                            $status_text = 'Selesai';
                            break;
                        case 'cancelled':
                            $status_class = 'status-cancelled';
                            $status_text = 'Dibatalkan';
                            break;
                        default:
                            $status_class = 'status-pending';
                            $status_text = ucfirst($status);
                    }
                    ?>
                    <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                </div>
            </div>
            <div class="order-body">
            <div class="row">
                <div class="col-md-8">
                    <p class="mb-1">
                        <i class="bi bi-shop"></i> <?= htmlspecialchars($order['resto_name'] ?? 'UpnFood Official') ?>
                    </p>
                    <h5 class="fw-bold mb-2"><?= htmlspecialchars($order['menu_nama']) ?></h5>
                    <p class="text-muted mb-1">
                        <i class="bi bi-cart"></i> <?= $order['quantity'] ?> porsi
                    </p>
                    <p class="text-muted mb-1">
                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($order['no_telpon']) ?>
                    </p>
                    <div class="order-address">
                        <i class="bi bi-geo-alt text-danger me-1"></i>
                        <small><?= htmlspecialchars($order['alamat']) ?></small>
                    </div>
                    <?php if(!empty($order['catatan'])): ?>
                        <p class="text-muted mb-0 mt-2">
                            <i class="bi bi-chat"></i> Catatan: <?= htmlspecialchars($order['catatan']) ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end">
                    <h5 class="text-danger fw-bold mb-2">
                        Rp <?= number_format($order['total_harga'], 0, ',', '.') ?>
                    </h5>
                    <?php if($order['status'] == 'pending'): ?>
                        <a href="?cancel=1&id=<?= $order['id'] ?>" class="btn-cancel" onclick="return confirm('Yakin ingin membatalkan pesanan ini?')">
                            <i class="bi bi-x-circle"></i> Batalkan
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-orders">
            <i class="bi bi-inbox"></i>
            <h4 class="text-muted">Belum Ada Pesanan</h4>
            <p class="text-muted">Yuk, pesan makanan favoritmu sekarang!</p>
            <a href="upnfoodhemat.php" class="btn btn-danger rounded-pill px-4 mt-3">
                <i class="bi bi-cart-plus"></i> Mulai Pesan
            </a>
        </div>
    <?php endif; ?>
</div>

<footer class="bg-white pt-5 mt-5 border-top">
    <div class="container text-center py-4">
        <p class="mb-0 text-muted small">© 2026 UpnFood | Merek milik Universitas Pembangunan Nasional.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>