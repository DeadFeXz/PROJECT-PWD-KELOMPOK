<?php
// Mendapatkan nama file untuk logika garis merah di navbar
$current_page = basename($_SERVER['PHP_SELF']);

session_start();
require_once 'config/database.php';

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Ambil data menu dari database
$sql = "SELECT * FROM menu ORDER BY id ASC";
$result = mysqli_query($conn, $sql);

$menu_items = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Jika harga_asli NULL di database, hitung otomatis (markup 50%)
    if ($row['harga_asli'] == NULL || $row['harga_asli'] == 0) {
        $row['harga_asli'] = round($row['harga'] * 1.5);
    }
    
    // Jika rating NULL, beri default 4.0
    if ($row['rating'] == NULL) {
        $row['rating'] = 4.0;
    }
    
    // Path gambar
    if (!empty($row['gambar'])) {
        $row['image'] = $row['gambar'];
    } else {
        // generate dari nama
        $row['image'] = 'assets/' . strtolower(str_replace(' ', ' ', $row['nama'])) . '.png';
    }
    
    $menu_items[] = $row;
}

// Filter menu berdasarkan kategori
$filtered_menu = $menu_items;
if ($filter !== 'all') {
    $filtered_menu = array_filter($menu_items, function($item) use ($filter) {
        return $item['kategori'] === $filter;
    });
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpnFood - Menu Hemat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="assets/upnvylogo.png">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm" id="mainNavbar">
        <div class="container">
            <!-- Logo di KIRI -->
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="https://upload.wikimedia.org/wikipedia/id/0/0d/Logo_Universitas_Pembangunan_Nasional_Veteran_Yogyakarta.png" alt="Logo" height="40">
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
            
            <!-- Bagian KANAN (Search + Profile) -->
            <div class="d-flex align-items-center" style="gap: 10px;">
                <i class="bi bi-search fs-5 cursor-pointer" style="cursor: pointer;"></i>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-success rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
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

    <!-- Hero Section HEMAT -->
    <section class="hemat-hero-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <div class="hemat-badge mb-3">
                        <span class="badge bg-danger px-4 py-2 rounded-pill fs-6">
                            <i class="bi bi-fire me-1"></i> Hemat Special
                        </span>
                    </div>
                    <h1 class="display-4 fw-bold text-dark mb-3">
                        Menu HEMAT <span class="text-danger">30rb+</span> udah ongkir
                    </h1>
                    <p class="lead text-muted mb-4">Makan enak tanpa pusing mikirin ongkir! Cukup 30rb+ sudah gratis ongkir</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Filter Kategori Menu -->
    <section class="container py-4">
        <div class="row">
            <div class="col-12">
                <div class="filter-scroll-wrapper">
                    <div class="filter-categories d-flex gap-2 overflow-auto pb-3">
                        <a href="?filter=all" class="filter-btn <?= $filter == 'all' ? 'active' : '' ?>">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i> Semua menu
                        </a>
                        <a href="?filter=bubur-ayam" class="filter-btn <?= $filter == 'bubur-ayam' ? 'active' : '' ?>">
                            <i class="bi bi-cup-straw me-1"></i> Bubur Ayam
                        </a>
                        <a href="?filter=mie" class="filter-btn <?= $filter == 'mie' ? 'active' : '' ?>">
                            <i class="bi bi-egg-fried me-1"></i> Mie Ayam
                        </a>
                        <a href="?filter=nasi-ayam" class="filter-btn <?= $filter == 'nasi-ayam' ? 'active' : '' ?>">
                            <i class="bi bi-droplet me-1"></i> Nasi Ayam
                        </a>
                        <a href="?filter=nasi-goreng" class="filter-btn <?= $filter == 'nasi-goreng' ? 'active' : '' ?>">
                            <i class="bi bi-basket me-1"></i> Nasi Goreng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Cards Section -->
    <section class="container py-4">
        <?php if(count($filtered_menu) > 0): ?>
            <div class="row g-4" id="menu-container">
                <?php foreach($filtered_menu as $menu): ?>
                <div class="col-md-6 col-lg-3 menu-item">
                    <div class="menu-card h-100">
                        <div class="menu-card-image">
                            <img src="<?= htmlspecialchars($menu['image']) ?>" alt="<?= htmlspecialchars($menu['nama']) ?>" onerror="this.src='assets/default.png'">
                            <span class="promo-badge">HEMAT</span>
                        </div>
                        <div class="menu-card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="menu-title mb-0"><?= htmlspecialchars($menu['nama']) ?></h5>
                                <div class="rating">
                                    <i class="bi bi-star-fill"></i>
                                    <span><?= number_format($menu['rating'], 1) ?></span>
                                </div>
                            </div>
                            <p class="resto-name mb-2">
                                <i class="bi bi-shop"></i> <?= htmlspecialchars($menu['resto']) ?>
                            </p>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="terjual">
                                    <i class="bi bi-cart-check"></i> Terjual <?= number_format($menu['terjual']) ?>+
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="harga-asli">Rp <?= number_format($menu['harga_asli'], 0, ',', '.') ?></span>
                                    <span class="harga">Rp <?= number_format($menu['harga'], 0, ',', '.') ?></span>
                                </div>
                                <a href="order.php?id=<?= $menu['id'] ?>" class="btn-pesan">
                                    <i class="bi bi-cart-plus"></i> Pesan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-emoji-frown fs-1 text-muted"></i>
                <p class="mt-3 text-muted">Maaf, tidak ada menu untuk kategori ini.</p>
                <a href="?filter=all" class="btn btn-danger rounded-pill px-4 mt-2">Lihat Semua Menu</a>
            </div>
        <?php endif; ?>
    </section>

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