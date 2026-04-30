<?php
namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\CategoryModel;

class HomeController
{
    private $productModel;
    private $categoryModel;
    
    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
    }
    
    public function index()
    {
        // Ambil data produk
        $products = $this->productModel->getLatest(8);
        $categories = $this->categoryModel->getAll();
        $featuredProducts = $this->productModel->getFeatured(4);
        
        $this->renderHeader();
        $this->renderHero();
        $this->renderCategories($categories);
        $this->renderFeaturedProducts($featuredProducts);
        $this->renderLatestProducts($products);
        $this->renderFooter();
    }
    
    private function renderHeader()
    {
        $isLoggedIn = isset($_SESSION['user_id']);
        $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
        $userName = $_SESSION['full_name'] ?? '';
        $cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
        
        echo '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>CobaEkspor - Marketplace Cybersecurity</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                }

                /* Navbar Styling */
                .navbar-modern {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
                    padding: 1rem 0;
                    position: sticky;
                    top: 0;
                    z-index: 1000;
                }

                .navbar-modern .navbar-brand {
                    font-size: 1.8rem;
                    font-weight: 800;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    letter-spacing: -0.5px;
                }

                .navbar-modern .nav-link {
                    color: #333;
                    font-weight: 500;
                    padding: 0.5rem 1rem !important;
                    margin: 0 0.2rem;
                    border-radius: 8px;
                    transition: all 0.3s ease;
                }

                .navbar-modern .nav-link:hover {
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white !important;
                    transform: translateY(-2px);
                }

                .cart-badge {
                    position: relative;
                }

                .cart-badge .badge {
                    position: absolute;
                    top: -8px;
                    right: -8px;
                    background: linear-gradient(135deg, #ff6b6b, #ee5253);
                    color: white;
                    border-radius: 50%;
                    padding: 0.2rem 0.5rem;
                    font-size: 0.7rem;
                    font-weight: 600;
                }

                .btn-gradient {
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    border: none;
                    padding: 0.5rem 1.5rem;
                    border-radius: 8px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }

                .btn-gradient:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
                    color: white;
                }

                .btn-outline-gradient {
                    background: transparent;
                    border: 2px solid;
                    border-image: linear-gradient(135deg, #667eea, #764ba2);
                    border-image-slice: 1;
                    color: #667eea;
                    font-weight: 600;
                    padding: 0.5rem 1.5rem;
                    border-radius: 8px;
                    transition: all 0.3s ease;
                }

                .btn-outline-gradient:hover {
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    transform: translateY(-2px);
                }

                /* Hero Section */
                .hero-section {
                    background: rgba(255, 255, 255, 0.95);
                    border-radius: 20px;
                    padding: 3rem;
                    margin-top: 2rem;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
                    backdrop-filter: blur(10px);
                    position: relative;
                    overflow: hidden;
                }

                .hero-section::before {
                    content: "";
                    position: absolute;
                    top: -50%;
                    right: -50%;
                    width: 200%;
                    height: 200%;
                    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
                    border-radius: 50%;
                    z-index: 0;
                }

                .hero-content {
                    position: relative;
                    z-index: 1;
                }

                .hero-title {
                    font-size: 3rem;
                    font-weight: 800;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    margin-bottom: 1rem;
                }

                .hero-subtitle {
                    font-size: 1.2rem;
                    color: #666;
                    margin-bottom: 2rem;
                }

                .search-box {
                    background: white;
                    border-radius: 50px;
                    padding: 0.5rem;
                    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
                    max-width: 500px;
                }

                .search-box input {
                    border: none;
                    padding: 0.8rem 1.5rem;
                    width: 100%;
                    border-radius: 50px;
                    outline: none;
                }

                .search-box button {
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    border: none;
                    color: white;
                    padding: 0.8rem 2rem;
                    border-radius: 50px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                }

                .search-box button:hover {
                    transform: translateX(5px);
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
                }

                /* Category Cards */
                .category-card {
                    background: white;
                    border-radius: 15px;
                    padding: 1.5rem;
                    text-align: center;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
                    height: 100%;
                }

                .category-card:hover {
                    transform: translateY(-10px);
                    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
                }

                .category-icon {
                    width: 60px;
                    height: 60px;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 1rem;
                }

                .category-icon i {
                    font-size: 1.8rem;
                    color: white;
                }

                .category-name {
                    font-weight: 600;
                    color: #333;
                    margin-bottom: 0.5rem;
                }

                .category-count {
                    color: #999;
                    font-size: 0.9rem;
                }

                /* Product Cards */
                .product-card {
                    background: white;
                    border-radius: 15px;
                    overflow: hidden;
                    transition: all 0.3s ease;
                    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
                    height: 100%;
                    position: relative;
                }

                .product-card:hover {
                    transform: translateY(-10px);
                    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
                }

                .product-badge {
                    position: absolute;
                    top: 10px;
                    left: 10px;
                    background: linear-gradient(135deg, #ff6b6b, #ee5253);
                    color: white;
                    padding: 0.3rem 1rem;
                    border-radius: 20px;
                    font-size: 0.8rem;
                    font-weight: 600;
                    z-index: 1;
                }

                .product-image {
                    height: 200px;
                    background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }

                .product-image i {
                    font-size: 4rem;
                    color: #999;
                }

                .product-content {
                    padding: 1.5rem;
                }

                .product-category {
                    color: #667eea;
                    font-size: 0.8rem;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    margin-bottom: 0.5rem;
                }

                .product-title {
                    font-weight: 600;
                    color: #333;
                    margin-bottom: 0.5rem;
                }

                .product-price {
                    font-size: 1.3rem;
                    font-weight: 700;
                    color: #764ba2;
                    margin-bottom: 1rem;
                }

                .product-stock {
                    color: #28a745;
                    font-size: 0.9rem;
                    margin-bottom: 1rem;
                }

                .product-stock.low {
                    color: #ff6b6b;
                }

                .btn-add-to-cart {
                    background: transparent;
                    border: 2px solid #667eea;
                    color: #667eea;
                    padding: 0.5rem 1rem;
                    border-radius: 8px;
                    font-weight: 600;
                    transition: all 0.3s ease;
                    width: 100%;
                }

                .btn-add-to-cart:hover {
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    border-color: transparent;
                    color: white;
                }

                /* Section Titles */
                .section-title {
                    font-size: 2rem;
                    font-weight: 700;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    margin-bottom: 1rem;
                    position: relative;
                    display: inline-block;
                }

                .section-title::after {
                    content: "";
                    position: absolute;
                    bottom: -10px;
                    left: 0;
                    width: 50%;
                    height: 3px;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    border-radius: 3px;
                }

                /* Footer */
                .footer-modern {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    padding: 3rem 0;
                    margin-top: 4rem;
                    box-shadow: 0 -2px 20px rgba(0, 0, 0, 0.1);
                }

                .footer-title {
                    font-size: 1.2rem;
                    font-weight: 600;
                    color: #333;
                    margin-bottom: 1.5rem;
                }

                .footer-links {
                    list-style: none;
                    padding: 0;
                }

                .footer-links li {
                    margin-bottom: 0.8rem;
                }

                .footer-links a {
                    color: #666;
                    text-decoration: none;
                    transition: all 0.3s ease;
                }

                .footer-links a:hover {
                    color: #667eea;
                    padding-left: 5px;
                }

                .social-links a {
                    display: inline-block;
                    width: 36px;
                    height: 36px;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    border-radius: 50%;
                    text-align: center;
                    line-height: 36px;
                    margin-right: 0.5rem;
                    transition: all 0.3s ease;
                }

                .social-links a:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
                }

                /* Alert */
                .alert-custom {
                    background: white;
                    border-left: 4px solid;
                    border-radius: 8px;
                    padding: 1rem;
                    margin-bottom: 1rem;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                }

                .alert-custom.success {
                    border-left-color: #28a745;
                }

                .alert-custom.error {
                    border-left-color: #dc3545;
                }

                .alert-custom i {
                    margin-right: 0.5rem;
                }
            </style>
        </head>
        <body>';
        
        // Navbar
        echo '<nav class="navbar-modern navbar navbar-expand-lg fixed-top">
            <div class="container">
                <a class="navbar-brand" href="/">CobaEkspor</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/"><i class="fas fa-home"></i> Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/products"><i class="fas fa-box"></i> Produk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/categories"><i class="fas fa-list"></i> Kategori</a>
                        </li>';
        
        // TAMBAH MENU ADMIN KHUSUS UNTUK ADMIN
        if ($isAdmin) {
            echo '<li class="nav-item">
                    <a class="nav-link text-danger fw-bold" href="/admin">
                        <i class="fas fa-shield-alt"></i> Admin Panel
                    </a>
                </li>';
        }
        
        echo '</ul>
            <div class="d-flex align-items-center">
                <a href="/cart" class="cart-badge me-3">
                    <i class="fas fa-shopping-cart fa-lg" style="color: #667eea;"></i>
                    <span class="badge">' . $cartCount . '</span>
                </a>';
        
        if ($isLoggedIn) {
            // TAMBAH BADGE ADMIN DI DROPDOWN
            $roleBadge = $isAdmin ? '<span class="badge bg-danger ms-2">Admin</span>' : '';
            
            echo '<div class="dropdown">
                <button class="btn btn-gradient dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="fas fa-user"></i> ' . $userName . $roleBadge . '
                </button>
                <ul class="dropdown-menu dropdown-menu-end">';
            
            // TAMBAH MENU ADMIN DI DROPDOWN
            if ($isAdmin) {
                echo '<li><a class="dropdown-item text-danger fw-bold" href="/admin">
                        <i class="fas fa-shield-alt"></i> Admin Dashboard
                    </a></li>
                    <li><hr class="dropdown-divider"></li>';
            }
            
            echo '<li><a class="dropdown-item" href="/profile"><i class="fas fa-user-circle"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="/orders"><i class="fas fa-shopping-bag"></i> Pesanan Saya</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>';
        } else {
            echo '<a href="/login" class="btn btn-outline-gradient me-2"><i class="fas fa-sign-in-alt"></i> Login</a>
                  <a href="/register" class="btn btn-gradient"><i class="fas fa-user-plus"></i> Register</a>';
        }
        
        echo '</div></div></div></nav>';
        
        // Spacer for fixed navbar
        echo '<div style="height: 80px;"></div>';
        
        // Container
        echo '<div class="container">';
        
        // Alerts
        if (isset($_SESSION['success'])) {
            echo '<div class="alert-custom success"><i class="fas fa-check-circle"></i> ' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        
        if (isset($_SESSION['error'])) {
            echo '<div class="alert-custom error"><i class="fas fa-exclamation-circle"></i> ' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
    }
    
    private function renderHero()
    {
        echo '
        <div class="hero-section">
            <div class="hero-content text-center">
                <h1 class="hero-title">Selamat Datang di CobaEkspor</h1>
                <p class="hero-subtitle">Tempat belanja aman untuk belajar cybersecurity</p>
                <div class="search-box mx-auto">
                    <form action="/products/search" method="GET" class="d-flex">
                        <input type="text" name="q" class="form-control" placeholder="Cari produk impianmu...">
                        <button type="submit"><i class="fas fa-search"></i> Cari</button>
                    </form>
                </div>
                <div class="mt-4">
                    <span class="badge bg-light text-dark me-2"><i class="fas fa-shield-alt text-primary"></i> Rentan SQLi</span>
                    <span class="badge bg-light text-dark me-2"><i class="fas fa-code text-danger"></i> Rentan XSS</span>
                    <span class="badge bg-light text-dark"><i class="fas fa-terminal text-success"></i> Rentan Command Injection</span>
                </div>
            </div>
        </div>';
    }
    
    private function renderCategories($categories)
    {
        echo '<h2 class="section-title mt-5">Kategori Populer</h2>
        <div class="row mt-4">';
        
        $icons = ['fa-mobile-alt', 'fa-tshirt', 'fa-utensils', 'fa-couch', 'fa-futbol', 'fa-laptop', 'fa-book', 'fa-car'];
        
        foreach ($categories as $index => $category) {
            $icon = $icons[$index % count($icons)];
            echo '
            <div class="col-md-3 col-6 mb-4">
                <a href="/products?category=' . $category['id'] . '" style="text-decoration: none;">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas ' . $icon . '"></i>
                        </div>
                        <div class="category-name">' . htmlspecialchars($category['name']) . '</div>
                        <div class="category-count">' . ($category['product_count'] ?? 0) . ' produk</div>
                    </div>
                </a>
            </div>';
        }
        
        echo '</div>';
    }
    
    private function renderFeaturedProducts($products)
    {
        echo '<h2 class="section-title mt-5">Produk Unggulan</h2>
        <div class="row mt-4">';
        
        foreach ($products as $product) {
            $this->renderProductCard($product, true);
        }
        
        echo '</div>';
    }
    
    private function renderLatestProducts($products)
    {
        echo '<h2 class="section-title mt-5">Produk Terbaru</h2>
        <div class="row mt-4">';
        
        foreach ($products as $product) {
            $this->renderProductCard($product);
        }
        
        echo '</div>';
    }
    
    private function renderProductCard($product, $featured = false)
    {
        $badge = $featured ? '<div class="product-badge"><i class="fas fa-crown"></i> UNGGULAN</div>' : '';
        $stockClass = $product['stock'] > 10 ? '' : 'low';
        
        echo '
        <div class="col-md-3 mb-4">
            <div class="product-card">
                ' . $badge . '
                <div class="product-image">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="product-content">
                    <div class="product-category">' . htmlspecialchars($product['category_name'] ?? 'Umum') . '</div>
                    <h5 class="product-title">' . htmlspecialchars($product['name']) . '</h5>
                    <div class="product-price">Rp ' . number_format($product['price'], 0, ',', '.') . '</div>
                    <div class="product-stock ' . $stockClass . '">
                        <i class="fas fa-box"></i> Stok: ' . $product['stock'] . '
                    </div>
                    <a href="/product/' . $product['id'] . '" class="btn-add-to-cart mb-2">
                        <i class="fas fa-info-circle"></i> Detail
                    </a>
                    <form action="/cart/add" method="POST" class="mt-2">
                        <input type="hidden" name="product_id" value="' . $product['id'] . '">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn-add-to-cart">
                            <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                        </button>
                    </form>
                </div>
            </div>
        </div>';
    }
    
    private function renderFooter()
    {
        echo '</div>'; // Close container
        
        echo '
        <footer class="footer-modern">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <h5 class="footer-title">CobaEkspor</h5>
                        <p>Marketplace untuk belajar cybersecurity dengan contoh kasus nyata. Website ini sengaja dibuat rentan untuk tujuan edukasi.</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                    <div class="col-md-2 mb-4">
                        <h5 class="footer-title">Menu</h5>
                        <ul class="footer-links">
                            <li><a href="/">Home</a></li>
                            <li><a href="/products">Produk</a></li>
                            <li><a href="/categories">Kategori</a></li>
                            <li><a href="/promo">Promo</a></li>
                        </ul>
                    </div>
                    <div class="col-md-2 mb-4">
                        <h5 class="footer-title">Bantuan</h5>
                        <ul class="footer-links">
                            <li><a href="/faq">FAQ</a></li>
                            <li><a href="/terms">Syarat & Ketentuan</a></li>
                            <li><a href="/privacy">Kebijakan Privasi</a></li>
                            <li><a href="/contact">Kontak</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4 mb-4">
                        <h5 class="footer-title">Newsletter</h5>
                        <p>Dapatkan info produk terbaru dan tips cybersecurity</p>
                        <form action="/newsletter" method="POST" class="mt-3">
                            <div class="input-group">
                                <input type="email" name="email" class="form-control" placeholder="Email kamu">
                                <button class="btn btn-gradient" type="submit">Subscribe</button>
                            </div>
                        </form>
                    </div>
                </div>
                <hr class="mt-4">
                <div class="text-center mt-4">
                    <p class="mb-0">&copy; 2026 CobaEkspor - Marketplace untuk Pelatihan Cybersecurity</p>
                    <small class="text-muted">*Website ini sengaja dibuat rentan, jangan gunakan data asli!*</small>
                </div>
            </div>
        </footer>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        </body>
        </html>';
    }
}
