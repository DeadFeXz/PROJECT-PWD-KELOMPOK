<?php
session_start();
require_once 'config/database.php';

// Mendapatkan nama file untuk logika garis merah di navbar
$current_page = basename($_SERVER['PHP_SELF']);

$register_success = false;
if (isset($_GET['register']) && $_GET['register'] == 'success') {
    $register_success = true;
}

// Ambil menu dari database
$menus = fetchAll("SELECT * FROM menu LIMIT 6");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UpnFood - Makan Enak?</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="assets/upnvylogo.png">
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
                <!-- <li class="nav-item">
                    <a class="nav-link fw-bold <?= ($current_page == 'rekomendasi.php') ? 'active-red' : '' ?>" href="rekomendasi.php">Rekomendasi</a>
                </li> -->
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

    <!-- Alert untuk notifikasi register sukses -->
    <?php if($register_success): ?>
        <div class="alert alert-success alert-dismissible fade show text-center" role="alert" style="margin-bottom: 0;">
            <i class="bi bi-check-circle-fill me-2"></i>
            Pendaftaran berhasil! Silakan login untuk mulai pesan.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- HERO SECTION -->
    <section class="hero-section text-white d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center hero-content">
                    <h1 class="hero-title fw-bold">Makan enak? <span class="text-warning">UpnFood</span>-in aja</h1>
                    <p class="hero-subtitle">Pesen yang bikin perut nyaman langsung di sini, semudah di aplikasi. Sama cepetnya dan banyak pilihan restonya.</p>
                    
                    <div class="d-flex justify-content-center align-items-center gap-2 mt-4">
                        <div class="d-flex align-items-center text-dark bg-white rounded-pill px-3 shadow-sm">
                            <i class="bi bi-geo-alt-fill text-danger fs-5 me-2"></i>
                            <select class="form-select border-0 shadow-none fw-bold bg-transparent" style="width: auto;">
                                <option selected>Jakarta</option>
                                <option>Surabaya</option>
                                <option>Yogyakarta</option>
                            </select>
                        </div>
                        
                        <button class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm">
                            <i class="bi bi-search me-2"></i>Eksplor
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- SECTION IDE -->
    <section class="container py-5 category-section">
        <div class="scroll-from-left">
            <h2 class="text-center fw-bold mb-5">Belom ada ide? Mulai dari sini aja dulu</h2>
        </div>
        <div class="row g-4 text-center">
            <?php 
            $ide = [
                ['name' => 'Pasti Ada Promo', 'img' => 'assets/promo.png', 'badge' => 'HOT'],
                ['name' => 'Terdekat', 'img' => 'assets/map.png', 'badge' => ''],
                ['name' => 'Terlaris', 'img' => 'assets/bintang.png', 'badge' => 'Best'],
                ['name' => 'Menu hemat', 'img' => 'assets/hemat.png', 'badge' => ''],
                ['name' => 'Terfavorit', 'img' => 'assets/favorit.png', 'badge' => ''],
                ['name' => '24 jam', 'img' => 'assets/24 jam.png', 'badge' => '24/7']
            ];
            foreach ($ide as $index => $item) : ?>
                <div class="col-6 col-md-2 scroll-from-left" data-delay="<?= $index * 0.1 ?>">
                    <div class="category-card">
                        <div class="category-icon-wrapper">
                            <img src="<?= $item['img'] ?>" alt="<?= $item['name'] ?>" class="category-icon">
                        </div>
                        <p class="category-name mb-0"><?= $item['name'] ?></p>
                        <?php if($item['badge']): ?>
                            <span class="category-badge"><?= $item['badge'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- SECTION KULINER -->
    <section class="container py-5">
        <div class="scroll-from-right">
            <h2 class="text-center fw-bold mb-5">Aneka kuliner menarik</h2>
        </div>
        <div class="row g-4 justify-content-center text-center">
            <?php 
            $kuliner = [
                ['name' => 'Martabak', 'img' => 'assets/martabak.png', 'count' => '128 restoran'],
                ['name' => 'Bakso & soto', 'img' => 'assets/bakso.png', 'count' => '95 restoran'],
                ['name' => 'Roti', 'img' => 'assets/roti.png', 'count' => '67 restoran'],
                ['name' => 'Chinese', 'img' => 'assets/chinese.png', 'count' => '156 restoran'],
                ['name' => 'Barat', 'img' => 'assets/barat.png', 'count' => '203 restoran'],
                ['name' => 'Cepat saji', 'img' => 'assets/cepatsaji.png', 'count' => '312 restoran']
            ];
            foreach ($kuliner as $index => $k) : ?>
                <div class="col-4 col-md-2 scroll-from-right" data-delay="<?= $index * 0.1 ?>">
                    <div class="food-category-wrapper">
                        <div class="food-category-circle" data-category="<?= $index + 1 ?>">
                            <div class="food-category-inner">
                                <img src="<?= $k['img'] ?>" alt="<?= $k['name'] ?>">
                            </div>
                        </div>
                        <p class="food-category-name mb-0"><?= $k['name'] ?></p>
                        <small class="food-category-count"><?= $k['count'] ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4 scroll-from-right">
            <button class="btn btn-outline-success rounded-pill px-4 fw-bold">
                <i class="bi bi-grid-3x3-gap-fill me-2"></i>Tampilkan kuliner lainnya
            </button>
        </div>
    </section>

    <!-- SECTION WHY UPNFOOD -->
    <section class="why-upnfood-section py-5">
        <div class="container">
            <div class="text-center mb-5 scroll-fade-up">
                <span class="section-badge">WHY UPNFOOD?</span>
                <h2 class="section-title fw-bold">Kenapa beli pakai <span class="text-danger">UpnFood</span>?</h2>
                <p class="section-subtitle text-muted">Nikmati pengalaman memesan makanan terbaik hanya di UpnFood</p>
            </div>
            
            <div class="row g-4">
                <?php 
                $features = [
                    ['icon' => 'bi-chat-dots-fill', 'title' => '20,000+ ulasan baru', 'desc' => 'Setiap menit ada ribuan ulasan dari pelanggan yang puas dengan layanan kami', 'stat' => '20k+', 'stat_label' => 'Ulasan/menit'],
                    ['icon' => 'bi-truck', 'title' => 'Delivery atau ambil sendiri', 'desc' => 'Bebas pilih mau diantar atau ambil sendiri, sesuai keinginan Anda', 'stat' => '24/7', 'stat_label' => 'Layanan'],
                    ['icon' => 'bi-tag-fill', 'title' => 'Makan apa aja, promo ada', 'desc' => 'Berbagai promo menarik setiap hari untuk semua jenis makanan favorit', 'stat' => '50+', 'stat_label' => 'Promo aktif'],
                    ['icon' => 'bi-shield-check', 'title' => 'Diantar aman & cepat', 'desc' => 'Driver profesional dengan sistem tracking real-time yang akurat', 'stat' => '15-30 min', 'stat_label' => 'Estimasi']
                ];
                foreach ($features as $index => $feature) : ?>
                    <div class="col-md-6 col-lg-3 scroll-fade-up" data-delay="<?= $index * 0.1 ?>">
                        <div class="feature-card-modern">
                            <div class="feature-icon-wrapper">
                                <div class="feature-icon">
                                    <i class="<?= $feature['icon'] ?>"></i>
                                </div>
                                <div class="feature-number">0<?= $index + 1 ?></div>
                            </div>
                            <h5 class="feature-title"><?= $feature['title'] ?></h5>
                            <p class="feature-desc"><?= $feature['desc'] ?></p>
                            <div class="feature-stats">
                                <span class="stat-value"><?= $feature['stat'] ?></span>
                                <span class="stat-label"><?= $feature['stat_label'] ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Additional Stats Bar animasi zoom -->
            <div class="stats-bar mt-5 scroll-zoom">
                <div class="row text-center g-4">
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <h3 class="stat-number">500+</h3>
                            <p class="stat-text">Partner Restoran</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <h3 class="stat-number">50k+</h3>
                            <p class="stat-text">Pelanggan Aktif</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <h3 class="stat-number">100+</h3>
                            <p class="stat-text">Kota Tersedia</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <h3 class="stat-number">4.8</h3>
                            <p class="stat-text">Rating Pengguna</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    
    <!-- SCROLL ANIMATION JAVASCRIPT -->
    <script>
        (function() {
            // Tambahkan CSS tambahan untuk redesign elements
            const style = document.createElement('style');
            style.textContent = `
                /* Additional styles for new redesign elements */
                .section-badge {
                    display: inline-block;
                    background: linear-gradient(135deg, #ed2739, #ff6b6b);
                    color: white;
                    padding: 5px 15px;
                    border-radius: 50px;
                    font-size: 0.8rem;
                    font-weight: 600;
                    margin-bottom: 15px;
                }
                
                .section-title {
                    font-size: 2.5rem;
                    margin-bottom: 1rem;
                }
                
                .feature-icon-wrapper {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    margin-bottom: 20px;
                }
                
                .feature-number {
                    font-size: 2rem;
                    font-weight: 800;
                    color: rgba(237, 39, 57, 0.1);
                }
                
                .feature-title {
                    font-size: 1.2rem;
                    font-weight: 700;
                    margin-bottom: 12px;
                    color: #333;
                }
                
                .feature-desc {
                    color: #666;
                    font-size: 0.9rem;
                    line-height: 1.5;
                    margin-bottom: 15px;
                }
                
                .feature-stats {
                    border-top: 1px solid #eee;
                    padding-top: 12px;
                    margin-top: auto;
                }
                
                .stat-value {
                    font-size: 1.1rem;
                    font-weight: 800;
                    color: #ed2739;
                    display: block;
                }
                
                .stat-label {
                    font-size: 0.75rem;
                    color: #999;
                }
                
                .stats-bar {
                    background: white;
                    border-radius: 30px;
                    padding: 30px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
                }
                
                .stat-number {
                    font-size: 2rem;
                    font-weight: 800;
                    color: #ed2739;
                    margin-bottom: 5px;
                }
                
                .stat-text {
                    color: #666;
                    font-size: 0.9rem;
                    margin-bottom: 0;
                }
                
                /* Hero section animation */
                .hero-section {
                    animation: fadeInZoom 0.8s ease-out;
                }
                
                @keyframes fadeInZoom {
                    from {
                        opacity: 0;
                        transform: scale(0.95);
                    }
                    to {
                        opacity: 1;
                        transform: scale(1);
                    }
                }
                
                /* Navbar scroll effect */
                #mainNavbar {
                    transition: all 0.3s ease;
                }
                
                #mainNavbar.scrolled {
                    background: rgba(255, 255, 255, 0.98) !important;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                    padding: 8px 0;
                }
                
                /* Delay untuk efek berurutan */
                [data-delay] {
                    transition-delay: attr(data-delay s);
                }
            `;
            document.head.appendChild(style);
            
            // Fungsi untuk mengecek elemen visible
            function checkVisibility() {
                const elements = document.querySelectorAll(
                    '.scroll-from-left, .scroll-from-right, .scroll-fade-up, .scroll-zoom, .scroll-fade'
                );
                
                const windowHeight = window.innerHeight;
                
                elements.forEach(element => {
                    const rect = element.getBoundingClientRect();
                    if (rect.top < windowHeight - 100 && rect.bottom > 100) {
                        element.classList.add('visible');
                    }
                });
            }
            
            // Fungsi untuk menambahkan delay dinamis
            function applyDelays() {
                document.querySelectorAll('[data-delay]').forEach(el => {
                    const delay = el.getAttribute('data-delay');
                    el.style.transitionDelay = delay + 's';
                });
            }
            
            // Navbar scroll effect
            function checkNavbar() {
                const navbar = document.getElementById('mainNavbar');
                if (navbar) {
                    if (window.scrollY > 50) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                }
            }
            
            // Jalankan saat load
            window.addEventListener('load', function() {
                applyDelays();
                checkVisibility();
                checkNavbar();
            });
            
            // Jalankan saat scroll
            window.addEventListener('scroll', function() {
                checkVisibility();
                checkNavbar();
            });
            
            // Jalankan saat resize
            window.addEventListener('resize', function() {
                checkVisibility();
            });
        })();
    </script>
</body>
</html>