<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\OrderModel;

class OrderController extends Controller
{
    private $orderModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new OrderModel();
    }
    
    public function history()
    {
        // Cek login
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Silakan login terlebih dahulu';
            header('Location: /login');
            exit;
        }
        
        // KERENTANAN: IDOR - User bisa lihat order user lain dengan manipulasi parameter
        $userId = $_GET['user_id'] ?? $_SESSION['user_id'];
        
        $orders = $this->orderModel->getUserOrders($userId);
        
        $this->renderHeader();
        $this->renderHistory($orders);
        $this->renderFooter();
    }
    
    public function detail($orderId)
    {
        // Cek login
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Silakan login terlebih dahulu';
            header('Location: /login');
            exit;
        }
        
        // KERENTANAN: IDOR - Bisa lihat order orang lain
        $order = $this->orderModel->getOrderWithItems($orderId);
        
        if (!$order) {
            $_SESSION['error'] = 'Pesanan tidak ditemukan';
            header('Location: /orders');
            exit;
        }
        
        $this->renderHeader();
        $this->renderDetail($order);
        $this->renderFooter();
    }
    
    private function renderHeader()
    {
        $cartCount = count($_SESSION['cart'] ?? []);
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Pesanan Saya - CobaEkspor</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                body { background: #f8f9fa; }
                .status-pending { color: #ffc107; }
                .status-paid { color: #17a2b8; }
                .status-processing { color: #007bff; }
                .status-shipped { color: #6f42c1; }
                .status-delivered { color: #28a745; }
                .status-cancelled { color: #dc3545; }
            </style>
        </head>
        <body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="/">CobaEkspor</a>
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/cart">
                                <i class="fas fa-shopping-cart"></i> Cart
                                <span class="badge bg-danger">' . $cartCount . '</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/logout">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container mt-4">';
    }
    
    private function renderHistory($orders)
    {
        echo '<h2 class="mb-4">Riwayat Pesanan</h2>';
        
        if (empty($orders)) {
            echo '<div class="alert alert-info">Belum ada pesanan</div>';
            echo '<a href="/products" class="btn btn-primary">Belanja Sekarang</a>';
            return;
        }
        
        echo '<table class="table table-bordered">';
        echo '<thead><tr><th>No. Order</th><th>Total</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($orders as $order) {
            $statusClass = 'status-' . $order['status'];
            echo '<tr>';
            echo '<td>' . $order['order_number'] . '</td>';
            echo '<td>Rp ' . number_format($order['total_amount'], 0, ',', '.') . '</td>';
            echo '<td class="' . $statusClass . '">' . ucfirst($order['status']) . '</td>';
            echo '<td>' . $order['created_at'] . '</td>';
            echo '<td><a href="/order/' . $order['id'] . '" class="btn btn-sm btn-info">Detail</a></td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        
        // KERENTANAN: XSS
        echo '<div class="alert alert-warning mt-3">';
        echo '<strong>Info Keamanan:</strong> Halaman ini rentan terhadap XSS. ';
        echo 'Coba inject parameter ?user_id=&lt;script&gt;alert(1)&lt;/script&gt;';
        echo '</div>';
    }
    
    private function renderDetail($order)
    {
        echo '<h2 class="mb-4">Detail Pesanan #' . $order['order_number'] . '</h2>';
        
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<div class="card mb-4">';
        echo '<div class="card-header">Informasi Pesanan</div>';
        echo '<div class="card-body">';
        echo '<p><strong>Status:</strong> <span class="status-' . $order['status'] . '">' . ucfirst($order['status']) . '</span></p>';
        echo '<p><strong>Total:</strong> Rp ' . number_format($order['total_amount'], 0, ',', '.') . '</p>';
        echo '<p><strong>Metode Pembayaran:</strong> ' . $order['payment_method'] . '</p>';
        echo '<p><strong>Tanggal:</strong> ' . $order['created_at'] . '</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="col-md-6">';
        echo '<div class="card mb-4">';
        echo '<div class="card-header">Alamat Pengiriman</div>';
        echo '<div class="card-body">';
        echo '<p>' . nl2br($order['shipping_address']) . '</p>';
        if ($order['notes']) {
            echo '<p><strong>Catatan:</strong> ' . $order['notes'] . '</p>';
        }
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<h4>Item Pesanan</h4>';
        echo '<table class="table table-bordered">';
        echo '<thead><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($order['items'] as $item) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($item['product_name']) . '</td>';
            echo '<td>Rp ' . number_format($item['price'], 0, ',', '.') . '</td>';
            echo '<td>' . $item['quantity'] . '</td>';
            echo '<td>Rp ' . number_format($item['subtotal'], 0, ',', '.') . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '<tfoot><tr><td colspan="3" class="text-end"><strong>Total:</strong></td>';
        echo '<td><strong>Rp ' . number_format($order['total_amount'], 0, ',', '.') . '</strong></td></tr>';
        echo '</tfoot>';
        echo '</table>';
        
        echo '<a href="/orders" class="btn btn-secondary">Kembali</a>';
    }
    
    private function renderFooter()
    {
        echo '</div>';
        echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>';
        echo '</body></html>';
    }
}
