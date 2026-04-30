<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\ProductModel;
use App\Models\CategoryModel;

class ProductController extends Controller
{
    private $productModel;
    private $categoryModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
    }
    
    public function index()
    {
        $products = $this->productModel->getAllWithCategory();
        $categories = $this->categoryModel->getAll();
        
        $this->renderHeader();
        $this->renderProducts($products, $categories);
        $this->renderFooter();
    }
    
    public function show($id)
    {
        $product = $this->productModel->find($id);
        
        if (!$product) {
            $_SESSION['error'] = 'Produk tidak ditemukan';
            header('Location: /products');
            exit;
        }
        
        $this->renderHeader();
        $this->renderProductDetail($product);
        $this->renderFooter();
    }
    
    private function renderHeader()
    {
        $cartCount = count($_SESSION['cart'] ?? []);
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Produk - CobaEkspor</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
        <nav class="navbar navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="/">CobaEkspor</a>
                <div>
                    <a href="/cart" class="btn btn-outline-light">
                        Cart <span class="badge bg-danger">' . $cartCount . '</span>
                    </a>
                </div>
            </div>
        </nav>
        <div class="container mt-4">';
    }
    
    private function renderProducts($products, $categories)
    {
        echo '<h2>Semua Produk</h2>';
        echo '<div class="row">';
        foreach ($products as $product) {
            echo '<div class="col-md-3 mb-4">';
            echo '<div class="card">';
            echo '<div class="card-body">';
            echo '<h5>' . $product['name'] . '</h5>';
            echo '<p>Rp ' . number_format($product['price'], 0, ',', '.') . '</p>';
            echo '<a href="/product/' . $product['id'] . '" class="btn btn-primary">Detail</a>';
            echo '</div></div></div>';
        }
        echo '</div>';
    }
    
    private function renderProductDetail($product)
    {
        echo '<h2>' . $product['name'] . '</h2>';
        echo '<p>Harga: Rp ' . number_format($product['price'], 0, ',', '.') . '</p>';
        echo '<p>Stok: ' . $product['stock'] . '</p>';
        echo '<p>' . $product['description'] . '</p>';
        echo '<form action="/cart/add" method="POST">';
        echo '<input type="hidden" name="product_id" value="' . $product['id'] . '">';
        echo '<input type="number" name="quantity" value="1" min="1" max="' . $product['stock'] . '">';
        echo '<button type="submit" class="btn btn-success">Tambah ke Cart</button>';
        echo '</form>';
    }
    
    private function renderFooter()
    {
        echo '</div></body></html>';
    }
}
