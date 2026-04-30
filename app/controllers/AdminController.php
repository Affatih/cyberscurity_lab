<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\OrderModel;

class AdminController extends Controller
{
    private $userModel;
    private $productModel;
    private $categoryModel;
    private $orderModel;
    
    public function __construct()
    {
        parent::__construct();
        
        // Cek session admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'Akses ditolak! Hanya untuk admin.';
            header('Location: /login');
            exit;
        }
        
        $this->userModel = new UserModel();
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->orderModel = new OrderModel();
    }
    
    public function dashboard()
    {
        $data = [
            'totalUsers' => $this->userModel->count(),
            'totalProducts' => $this->productModel->count(),
            'totalOrders' => $this->orderModel->count(),
            'recentOrders' => $this->orderModel->getRecent(5),
            'recentUsers' => $this->userModel->getRecent(5),
            'lowStockProducts' => $this->productModel->getLowStock(5)
        ];
        
        $this->renderHeader();
        $this->renderSidebar();
        $this->renderDashboard($data);
        $this->renderFooter();
    }
    
    public function products()
    {
        $products = $this->productModel->getAllWithCategory();
        $categories = $this->categoryModel->getAll();
        
        $this->renderHeader();
        $this->renderSidebar();
        $this->renderProducts($products, $categories);
        $this->renderFooter();
    }
    
    public function addProduct()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/products');
            exit;
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'stock' => $_POST['stock'] ?? 0,
            'category_id' => $_POST['category_id'] ?? null,
            'image' => 'default.jpg'
        ];
        
        // KERENTANAN: SQL Injection di sini!
        if ($this->productModel->createVulnerable($data)) {
            $_SESSION['success'] = 'Produk berhasil ditambahkan';
        } else {
            $_SESSION['error'] = 'Gagal menambahkan produk';
        }
        
        header('Location: /admin/products');
        exit;
    }
    
    public function editProduct($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price' => $_POST['price'] ?? 0,
                'stock' => $_POST['stock'] ?? 0,
                'category_id' => $_POST['category_id'] ?? null
            ];
            
            // KERENTANAN: SQL Injection
            if ($this->productModel->updateVulnerable($id, $data)) {
                $_SESSION['success'] = 'Produk berhasil diupdate';
            } else {
                $_SESSION['error'] = 'Gagal mengupdate produk';
            }
            
            header('Location: /admin/products');
            exit;
        }
        
        $product = $this->productModel->find($id);
        $categories = $this->categoryModel->getAll();
        
        $this->renderHeader();
        $this->renderSidebar();
        $this->renderEditProduct($product, $categories);
        $this->renderFooter();
    }
    
    public function deleteProduct($id)
    {
        // KERENTANAN: IDOR (Insecure Direct Object Reference)
        // Bisa hapus produk dengan ID berapa aja tanpa cek kepemilikan
        if ($this->productModel->delete($id)) {
            $_SESSION['success'] = 'Produk berhasil dihapus';
        } else {
            $_SESSION['error'] = 'Gagal menghapus produk';
        }
        
        header('Location: /admin/products');
        exit;
    }
    
    public function users()
    {
        $users = $this->userModel->getAllUsers();
        
        $this->renderHeader();
        $this->renderSidebar();
        $this->renderUsers($users);
        $this->renderFooter();
    }
    
    public function deleteUser($id)
    {
        // KERENTANAN: IDOR - bisa hapus user lain
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Tidak bisa menghapus akun sendiri';
        } else {
            if ($this->userModel->delete($id)) {
                $_SESSION['success'] = 'User berhasil dihapus';
            } else {
                $_SESSION['error'] = 'Gagal menghapus user';
            }
        }
        
        header('Location: /admin/users');
        exit;
    }
    
    public function orders()
    {
        $orders = $this->orderModel->getAllWithDetails();
        
        $this->renderHeader();
        $this->renderSidebar();
        $this->renderOrders($orders);
        $this->renderFooter();
    }
    
    public function updateOrderStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/orders');
            exit;
        }
        
        $status = $_POST['status'] ?? '';
        
        // KERENTANAN: No CSRF protection
        if ($this->orderModel->updateStatus($id, $status)) {
            $_SESSION['success'] = 'Status pesanan berhasil diupdate';
        } else {
            $_SESSION['error'] = 'Gagal mengupdate status';
        }
        
        header('Location: /admin/orders');
        exit;
    }
    
    // =========== RENDER METHODS ===========
    
    private function renderHeader()
    {
        echo '<!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Dashboard - CobaEkspor</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                body {
                    background: #f4f6f9;
                    font-family: "Segoe UI", sans-serif;
                }
                
                .sidebar {
                    position: fixed;
                    top: 0;
                    left: 0;
                    height: 100vh;
                    width: 250px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 2rem 1rem;
                    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
                }
                
                .sidebar-brand {
                    font-size: 1.5rem;
                    font-weight: 700;
                    margin-bottom: 2rem;
                    padding-bottom: 1rem;
                    border-bottom: 1px solid rgba(255,255,255,0.2);
                }
                
                .sidebar-menu {
                    list-style: none;
                    padding: 0;
                }
                
                .sidebar-menu li {
                    margin-bottom: 0.5rem;
                }
                
                .sidebar-menu a {
                    color: white;
                    text-decoration: none;
                    padding: 0.8rem 1rem;
                    display: block;
                    border-radius: 8px;
                    transition: all 0.3s ease;
                }
                
                .sidebar-menu a:hover, .sidebar-menu a.active {
                    background: rgba(255,255,255,0.2);
                    transform: translateX(5px);
                }
                
                .sidebar-menu i {
                    width: 25px;
                    margin-right: 10px;
                }
                
                .main-content {
                    margin-left: 250px;
                    padding: 2rem;
                }
                
                .navbar-top {
                    background: white;
                    padding: 1rem 2rem;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    margin-bottom: 2rem;
                }
                
                .stat-card {
                    background: white;
                    border-radius: 10px;
                    padding: 1.5rem;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    transition: all 0.3s ease;
                }
                
                .stat-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
                }
                
                .stat-icon {
                    width: 60px;
                    height: 60px;
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 2rem;
                    margin-bottom: 1rem;
                }
                
                .stat-value {
                    font-size: 2rem;
                    font-weight: 700;
                    margin-bottom: 0.5rem;
                }
                
                .stat-label {
                    color: #666;
                    font-size: 0.9rem;
                }
                
                .table-card {
                    background: white;
                    border-radius: 10px;
                    padding: 1.5rem;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                
                .btn-gradient {
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    border: none;
                    padding: 0.5rem 1.5rem;
                    border-radius: 8px;
                    transition: all 0.3s ease;
                }
                
                .btn-gradient:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 5px 15px rgba(102,126,234,0.4);
                    color: white;
                }
                
                .alert-custom {
                    background: white;
                    border-left: 4px solid;
                    border-radius: 8px;
                    padding: 1rem;
                    margin-bottom: 1rem;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                
                .badge-status {
                    padding: 0.5rem 1rem;
                    border-radius: 20px;
                    font-size: 0.8rem;
                    font-weight: 600;
                }
                
                .status-pending { background: #fff3cd; color: #856404; }
                .status-paid { background: #d4edda; color: #155724; }
                .status-shipped { background: #cce5ff; color: #004085; }
                .status-delivered { background: #d1e7dd; color: #0f5132; }
                .status-cancelled { background: #f8d7da; color: #721c24; }
            </style>
        </head>
        <body>';
    }
    
    private function renderSidebar()
    {
        $currentUri = $_SERVER['REQUEST_URI'];
        
        echo '<div class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-shield-alt"></i> CobaEkspor Admin
            </div>
            <ul class="sidebar-menu">
                <li><a href="/admin" ' . ($currentUri == '/admin' ? 'class="active"' : '') . '>
                    <i class="fas fa-dashboard"></i> Dashboard
                </a></li>
                <li><a href="/admin/products" ' . (strpos($currentUri, '/admin/products') !== false ? 'class="active"' : '') . '>
                    <i class="fas fa-box"></i> Produk
                </a></li>
                <li><a href="/admin/users" ' . (strpos($currentUri, '/admin/users') !== false ? 'class="active"' : '') . '>
                    <i class="fas fa-users"></i> Users
                </a></li>
                <li><a href="/admin/orders" ' . (strpos($currentUri, '/admin/orders') !== false ? 'class="active"' : '') . '>
                    <i class="fas fa-shopping-cart"></i> Orders
                </a></li>
                <li><a href="/admin/categories" ' . (strpos($currentUri, '/admin/categories') !== false ? 'class="active"' : '') . '>
                    <i class="fas fa-list"></i> Kategori
                </a></li>
                <li><a href="/admin/reports" ' . (strpos($currentUri, '/admin/reports') !== false ? 'class="active"' : '') . '>
                    <i class="fas fa-chart-bar"></i> Laporan
                </a></li>
                <li><a href="/admin/settings" ' . (strpos($currentUri, '/admin/settings') !== false ? 'class="active"' : '') . '>
                    <i class="fas fa-cog"></i> Pengaturan
                </a></li>
                <hr style="border-color: rgba(255,255,255,0.2);">
                <li><a href="/">
                    <i class="fas fa-store"></i> Lihat Toko
                </a></li>
                <li><a href="/logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a></li>
            </ul>
        </div>';
        
        echo '<div class="main-content">';
        
        // Navbar top
        echo '<div class="navbar-top d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Halo, ' . $_SESSION['full_name'] . '!</h5>
            <div>
                <span class="badge bg-danger">Role: Admin</span>
                <span class="badge bg-warning ms-2">' . date('d M Y H:i') . '</span>
            </div>
        </div>';
        
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
    
    private function renderDashboard($data)
    {
        echo '
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #667eea20; color: #667eea;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value">' . $data['totalUsers'] . '</div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #764ba220; color: #764ba2;">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-value">' . $data['totalProducts'] . '</div>
                    <div class="stat-label">Total Produk</div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #28a74520; color: #28a745;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-value">' . $data['totalOrders'] . '</div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ffc10720; color: #ffc107;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">' . count($data['lowStockProducts']) . '</div>
                    <div class="stat-label">Stok Menipis</div>
                </div>
            </div>
        </div>';
        
        // Recent Orders
        echo '<div class="row mt-4">
            <div class="col-md-6 mb-4">
                <div class="table-card">
                    <h5 class="mb-3"><i class="fas fa-clock"></i> Pesanan Terbaru</h5>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>User</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>';
        
        foreach ($data['recentOrders'] as $order) {
            echo '<tr>
                <td><a href="/admin/order/' . $order['id'] . '">' . $order['order_number'] . '</a></td>
                <td>' . $order['username'] . '</td>
                <td>Rp ' . number_format($order['total_amount'], 0, ',', '.') . '</td>
                <td><span class="badge-status status-' . $order['status'] . '">' . $order['status'] . '</span></td>
            </tr>';
        }
        
        echo '</tbody></table></div></div>';
        
        // Low Stock Products
        echo '<div class="col-md-6 mb-4">
            <div class="table-card">
                <h5 class="mb-3"><i class="fas fa-exclamation-triangle text-warning"></i> Stok Menipis</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Stok</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($data['lowStockProducts'] as $product) {
            echo '<tr>
                <td>' . $product['name'] . '</td>
                <td><span class="badge bg-danger">' . $product['stock'] . '</span></td>
                <td>Rp ' . number_format($product['price'], 0, ',', '.') . '</td>
                <td><a href="/admin/products/edit/' . $product['id'] . '" class="btn btn-sm btn-warning">Restock</a></td>
            </tr>';
        }
        
        echo '</tbody></table></div></div></div>';
    }
    
    private function renderProducts($products, $categories)
    {
        echo '<div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fas fa-box"></i> Manajemen Produk</h4>
            <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fas fa-plus"></i> Tambah Produk
            </button>
        </div>';
        
        echo '<div class="table-card">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($products as $product) {
            echo '<tr>
                <td>' . $product['id'] . '</td>
                <td>' . $product['name'] . '</td>
                <td>' . $product['category_name'] . '</td>
                <td>Rp ' . number_format($product['price'], 0, ',', '.') . '</td>
                <td>' . $product['stock'] . '</td>
                <td>
                    <a href="/admin/products/edit/' . $product['id'] . '" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="/admin/products/delete/' . $product['id'] . '" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm(\'Yakin hapus?\')">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>';
        }
        
        echo '</tbody></table></div>';
        
        // Modal Tambah Produk
        echo '<div class="modal fade" id="addProductModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Produk Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="/admin/products/add">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Nama Produk</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Deskripsi</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label>Kategori</label>
                                <select name="category_id" class="form-control">';
        
        foreach ($categories as $cat) {
            echo '<option value="' . $cat['id'] . '">' . $cat['name'] . '</option>';
        }
        
        echo '          </select>
                            </div>
                            <div class="mb-3">
                                <label>Harga</label>
                                <input type="number" name="price" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Stok</label>
                                <input type="number" name="stock" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-gradient">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>';
    }
    
    private function renderUsers($users)
    {
        echo '<h4 class="mb-4"><i class="fas fa-users"></i> Manajemen Users</h4>';
        
        echo '<div class="table-card">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($users as $user) {
            echo '<tr>
                <td>' . $user['id'] . '</td>
                <td>' . $user['username'] . '</td>
                <td>' . $user['full_name'] . '</td>
                <td>' . $user['email'] . '</td>
                <td><span class="badge ' . ($user['role'] == 'admin' ? 'bg-danger' : 'bg-primary') . '">' . $user['role'] . '</span></td>
                <td>' . $user['created_at'] . '</td>
                <td>
                    <a href="/admin/users/edit/' . $user['id'] . '" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i>
                    </a>';
            
            if ($user['id'] != $_SESSION['user_id']) {
                echo '<a href="/admin/users/delete/' . $user['id'] . '" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm(\'Yakin hapus user ini?\')">
                        <i class="fas fa-trash"></i>
                    </a>';
            }
            
            echo '</td></tr>';
        }
        
        echo '</tbody></table></div>';
    }
    
    private function renderOrders($orders)
    {
        echo '<h4 class="mb-4"><i class="fas fa-shopping-cart"></i> Manajemen Orders</h4>';
        
        echo '<div class="table-card">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>User</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($orders as $order) {
            echo '<tr>
                <td>' . $order['order_number'] . '</td>
                <td>' . $order['username'] . '</td>
                <td>Rp ' . number_format($order['total_amount'], 0, ',', '.') . '</td>
                <td>
                    <form method="POST" action="/admin/orders/status/' . $order['id'] . '" class="d-flex">
                        <select name="status" class="form-select form-select-sm me-2" style="width: 120px;">
                            <option value="pending" ' . ($order['status'] == 'pending' ? 'selected' : '') . '>Pending</option>
                            <option value="paid" ' . ($order['status'] == 'paid' ? 'selected' : '') . '>Paid</option>
                            <option value="processing" ' . ($order['status'] == 'processing' ? 'selected' : '') . '>Processing</option>
                            <option value="shipped" ' . ($order['status'] == 'shipped' ? 'selected' : '') . '>Shipped</option>
                            <option value="delivered" ' . ($order['status'] == 'delivered' ? 'selected' : '') . '>Delivered</option>
                            <option value="cancelled" ' . ($order['status'] == 'cancelled' ? 'selected' : '') . '>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-save"></i>
                        </button>
                    </form>
                </td>
                <td>' . $order['created_at'] . '</td>
                <td>
                    <a href="/admin/order/' . $order['id'] . '" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>';
        }
        
        echo '</tbody></table></div>';
    }
    
    private function renderEditProduct($product, $categories)
    {
        echo '<div class="card">
            <div class="card-header">
                <h5>Edit Produk: ' . $product['name'] . '</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Nama Produk</label>
                        <input type="text" name="name" class="form-control" value="' . $product['name'] . '" required>
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3">' . $product['description'] . '</textarea>
                    </div>
                    <div class="mb-3">
                        <label>Kategori</label>
                        <select name="category_id" class="form-control">';
        
        foreach ($categories as $cat) {
            $selected = ($cat['id'] == $product['category_id']) ? 'selected' : '';
            echo '<option value="' . $cat['id'] . '" ' . $selected . '>' . $cat['name'] . '</option>';
        }
        
        echo '          </select>
                    </div>
                    <div class="mb-3">
                        <label>Harga</label>
                        <input type="number" name="price" class="form-control" value="' . $product['price'] . '" required>
                    </div>
                    <div class="mb-3">
                        <label>Stok</label>
                        <input type="number" name="stock" class="form-control" value="' . $product['stock'] . '" required>
                    </div>
                    <button type="submit" class="btn btn-gradient">Update</button>
                    <a href="/admin/products" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>';
    }
    
    private function renderFooter()
    {
        echo '</div></div>'; // Close main-content and sidebar
        
        echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        </body>
        </html>';
    }
}
