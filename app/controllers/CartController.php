<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ProductModel;
use App\Models\OrderModel;

class CartController extends Controller
{
    private $productModel;
    private $orderModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->productModel = new ProductModel();
        $this->orderModel = new OrderModel();
        
        // Inisialisasi cart di session jika belum ada
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }
    
    public function index()
    {
        $cart = $_SESSION['cart'];
        $total = 0;
        $items = [];
        
        foreach ($cart as $productId => $quantity) {
            $product = $this->productModel->find($productId);
            if ($product) {
                $product['quantity'] = $quantity;
                $product['subtotal'] = $product['price'] * $quantity;
                $total += $product['subtotal'];
                $items[] = $product;
            }
        }
        
        $this->renderHeader();
        $this->renderCart($items, $total);
        $this->renderFooter();
    }
    
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /products');
            exit;
        }
        
        $productId = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        
        // KERENTANAN: IDOR - Bisa nambah produk dengan ID berapa aja
        $product = $this->productModel->find($productId);
        
        if ($product) {
            // KERENTANAN: XSS di session cart
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] += $quantity;
            } else {
                $_SESSION['cart'][$productId] = $quantity;
            }
            
            $_SESSION['success'] = 'Produk berhasil ditambahkan ke keranjang';
        } else {
            $_SESSION['error'] = 'Produk tidak ditemukan';
        }
        
        // KERENTANAN: Open redirect
        $redirect = $_GET['redirect'] ?? '/cart';
        header('Location: ' . $redirect);
        exit;
    }
    
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cart');
            exit;
        }
        
        $productId = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 0;
        
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
        
        header('Location: /cart');
        exit;
    }
    
    public function remove($productId)
    {
        unset($_SESSION['cart'][$productId]);
        header('Location: /cart');
        exit;
    }
    
    public function checkout()
    {
        // Cek login
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Silakan login terlebih dahulu';
            header('Location: /login?redirect=' . urlencode('/checkout'));
            exit;
        }
        
        $cart = $_SESSION['cart'];
        if (empty($cart)) {
            $_SESSION['error'] = 'Keranjang belanja kosong';
            header('Location: /products');
            exit;
        }
        
        $this->renderHeader();
        $this->renderCheckoutForm();
        $this->renderFooter();
    }
    
    public function placeOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /cart');
            exit;
        }
        
        // Cek login
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Silakan login terlebih dahulu';
            header('Location: /login');
            exit;
        }
        
        $cart = $_SESSION['cart'];
        if (empty($cart)) {
            $_SESSION['error'] = 'Keranjang belanja kosong';
            header('Location: /products');
            exit;
        }
        
        // Ambil data dari form
        $shipping_address = $_POST['address'] ?? '';
        $payment_method = $_POST['payment_method'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // KERENTANAN: XSS di notes
        // KERENTANAN: SQL Injection di shipping_address
        
        // Hitung total
        $total = 0;
        $items = [];
        foreach ($cart as $productId => $quantity) {
            $product = $this->productModel->find($productId);
            if ($product) {
                $subtotal = $product['price'] * $quantity;
                $total += $subtotal;
                $items[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product['price'],
                    'subtotal' => $subtotal
                ];
            }
        }
        
        // Generate order number
        $orderNumber = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
        
        // Simpan order
        $orderData = [
            'order_number' => $orderNumber,
            'user_id' => $_SESSION['user_id'],
            'total_amount' => $total,
            'status' => 'pending',
            'payment_method' => $payment_method,
            'shipping_address' => $shipping_address,
            'notes' => $notes
        ];
        
        $orderId = $this->orderModel->create($orderData);
        
        if ($orderId) {
            // Simpan order items
            foreach ($items as $item) {
                $item['order_id'] = $orderId;
                $this->orderModel->addItem($item);
            }
            
            // Kosongkan cart
            $_SESSION['cart'] = [];
            
            $_SESSION['success'] = 'Pesanan berhasil dibuat! Nomor order: ' . $orderNumber;
            header('Location: /order/' . $orderId);
            exit;
        } else {
            $_SESSION['error'] = 'Gagal membuat pesanan';
            header('Location: /checkout');
            exit;
        }
    }
    
    private function renderHeader()
    {
        $isLoggedIn = isset($_SESSION['user_id']);
        $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
        $userName = $_SESSION['full_name'] ?? '';
        $cartCount = count($_SESSION['cart'] ?? []);
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Keranjang - CobaEkspor</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                body { background: #f8f9fa; }
                .navbar-modern { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .cart-badge { position: relative; }
                .cart-badge .badge {
                    position: absolute; top: -8px; right: -8px;
                    background: #dc3545; color: white;
                    border-radius: 50%; padding: 2px 6px; font-size: 12px;
                }
                .table-cart img { width: 80px; height: 80px; object-fit: cover; }
                .total-price { font-size: 24px; font-weight: bold; color: #28a745; }
                .btn-checkout { background: #28a745; color: white; padding: 12px 30px; font-size: 18px; }
                .btn-checkout:hover { background: #218838; }
            </style>
        </head>
        <body>
        <nav class="navbar-modern navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="/">CobaEkspor</a>
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="/products">Produk</a></li>
                    </ul>
                    <div class="d-flex">
                        <a href="/cart" class="cart-badge me-3">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                            <span class="badge">' . $cartCount . '</span>
                        </a>';
        
        if ($isLoggedIn) {
            echo '<div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-user"></i> ' . $userName . '
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="/orders">Pesanan Saya</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/logout">Logout</a></li>
                </ul>
            </div>';
        } else {
            echo '<a href="/login" class="btn btn-primary">Login</a>';
        }
        
        echo '</div></div></div></nav><div class="container mt-4">';
        
        // Alerts
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
    }
    
    private function renderCart($items, $total)
    {
        echo '<h2 class="mb-4">Keranjang Belanja</h2>';
        
        if (empty($items)) {
            echo '<div class="alert alert-info">Keranjang belanja kosong</div>';
            echo '<a href="/products" class="btn btn-primary">Belanja Sekarang</a>';
            return;
        }
        
        echo '<form method="POST" action="/cart/update" id="cartForm">';
        echo '<table class="table table-bordered">';
        echo '<thead><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th>Aksi</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($items as $item) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($item['name']) . '</td>';
            echo '<td>Rp ' . number_format($item['price'], 0, ',', '.') . '</td>';
            echo '<td>';
            echo '<input type="number" name="quantity[' . $item['id'] . ']" value="' . $item['quantity'] . '" min="0" class="form-control" style="width: 80px;">';
            echo '</td>';
            echo '<td>Rp ' . number_format($item['subtotal'], 0, ',', '.') . '</td>';
            echo '<td>';
            echo '<a href="/cart/remove/' . $item['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Hapus item ini?\')">Hapus</a>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '<tfoot>';
        echo '<tr><td colspan="3" class="text-end"><strong>Total:</strong></td>';
        echo '<td colspan="2" class="total-price">Rp ' . number_format($total, 0, ',', '.') . '</td></tr>';
        echo '</tfoot>';
        echo '</table>';
        
        echo '<div class="d-flex justify-content-between">';
        echo '<button type="submit" class="btn btn-warning">Update Keranjang</button>';
        echo '<a href="/checkout" class="btn btn-success btn-checkout">Checkout</a>';
        echo '</div>';
        echo '</form>';
    }
    
    private function renderCheckoutForm()
    {
        $cart = $_SESSION['cart'];
        $total = 0;
        $items = [];
        
        foreach ($cart as $productId => $quantity) {
            $product = $this->productModel->find($productId);
            if ($product) {
                $product['quantity'] = $quantity;
                $product['subtotal'] = $product['price'] * $quantity;
                $total += $product['subtotal'];
                $items[] = $product;
            }
        }
        
        echo '<h2 class="mb-4">Checkout</h2>';
        
        echo '<div class="row">';
        echo '<div class="col-md-8">';
        echo '<div class="card mb-4">';
        echo '<div class="card-header">Form Pengiriman</div>';
        echo '<div class="card-body">';
        
        // KERENTANAN: No CSRF token
        echo '<form method="POST" action="/order/place">';
        
        echo '<div class="mb-3">';
        echo '<label>Alamat Lengkap</label>';
        echo '<textarea name="address" class="form-control" rows="3" required></textarea>';
        echo '<small class="text-muted">KERENTANAN: SQL Injection di sini!</small>';
        echo '</div>';
        
        echo '<div class="mb-3">';
        echo '<label>Metode Pembayaran</label>';
        echo '<select name="payment_method" class="form-control">';
        echo '<option value="transfer">Transfer Bank</option>';
        echo '<option value="cod">COD</option>';
        echo '<option value="credit_card">Kartu Kredit</option>';
        echo '</select>';
        echo '</div>';
        
        echo '<div class="mb-3">';
        echo '<label>Catatan (opsional)</label>';
        echo '<textarea name="notes" class="form-control" rows="2"></textarea>';
        echo '<small class="text-muted">KERENTANAN: XSS di sini! Coba inject &lt;script&gt;alert(1)&lt;/script&gt;</small>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="col-md-4">';
        echo '<div class="card">';
        echo '<div class="card-header">Ringkasan Belanja</div>';
        echo '<div class="card-body">';
        
        foreach ($items as $item) {
            echo '<div class="d-flex justify-content-between mb-2">';
            echo '<span>' . htmlspecialchars($item['name']) . ' x ' . $item['quantity'] . '</span>';
            echo '<span>Rp ' . number_format($item['subtotal'], 0, ',', '.') . '</span>';
            echo '</div>';
        }
        
        echo '<hr>';
        echo '<div class="d-flex justify-content-between fw-bold">';
        echo '<span>Total</span>';
        echo '<span>Rp ' . number_format($total, 0, ',', '.') . '</span>';
        echo '</div>';
        echo '<hr>';
        echo '<button type="submit" class="btn btn-success w-100">Buat Pesanan</button>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    private function renderFooter()
    {
        echo '</div>';
        echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>';
        echo '</body></html>';
    }
}
