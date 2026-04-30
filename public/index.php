<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load konfigurasi environment
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

// MANUAL REQUIRE - URUTAN HARUS BENAR
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Request.php';
require_once __DIR__ . '/../app/Core/Controller.php';
require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/models/UserModel.php';
require_once __DIR__ . '/../app/models/ProductModel.php';
require_once __DIR__ . '/../app/models/CategoryModel.php';
require_once __DIR__ . '/../app/models/OrderModel.php';
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/ProductController.php';
require_once __DIR__ . '/../app/controllers/CartController.php';
require_once __DIR__ . '/../app/controllers/OrderController.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// URI yang diminta
$request_uri = $_SERVER['REQUEST_URI'];
$request_uri = strtok($request_uri, '?');
$request_uri = '/' . ltrim($request_uri, '/');

// Buat router dan register routes
$router = new App\Core\Router();

// =========== REGISTER ALL ROUTES ===========
$router->add('GET', '/', 'HomeController@index');

// Auth routes
$router->add('GET', '/register', 'AuthController@registerForm');
$router->add('POST', '/register', 'AuthController@register');
$router->add('GET', '/login', 'AuthController@loginForm');
$router->add('POST', '/login', 'AuthController@login');
$router->add('GET', '/logout', 'AuthController@logout');
$router->add('GET', '/forgot-password', 'AuthController@forgotPasswordForm');
$router->add('POST', '/forgot-password', 'AuthController@forgotPassword');

// Admin routes
$router->add('GET', '/admin', 'AdminController@dashboard');
$router->add('GET', '/admin/products', 'AdminController@products');
$router->add('POST', '/admin/products/add', 'AdminController@addProduct');
$router->add('GET', '/admin/products/edit/{id}', 'AdminController@editProduct');
$router->add('POST', '/admin/products/edit/{id}', 'AdminController@editProduct');
$router->add('GET', '/admin/products/delete/{id}', 'AdminController@deleteProduct');
$router->add('GET', '/admin/users', 'AdminController@users');
$router->add('GET', '/admin/users/delete/{id}', 'AdminController@deleteUser');
$router->add('GET', '/admin/orders', 'AdminController@orders');
$router->add('POST', '/admin/orders/status/{id}', 'AdminController@updateOrderStatus');

// Product routes
$router->add('GET', '/products', 'ProductController@index');
$router->add('GET', '/product/{id}', 'ProductController@show');

// Cart routes
$router->add('GET', '/cart', 'CartController@index');
$router->add('POST', '/cart/add', 'CartController@add');
$router->add('POST', '/cart/update', 'CartController@update');
$router->add('GET', '/cart/remove/{id}', 'CartController@remove');
$router->add('GET', '/checkout', 'CartController@checkout');
$router->add('POST', '/order/place', 'CartController@placeOrder');

// Order routes
$router->add('GET', '/orders', 'OrderController@history');
$router->add('GET', '/order/{id}', 'OrderController@detail');
// ===========================================

// Dispatch - HANYA SEKALI!
$router->dispatch($_SERVER['REQUEST_METHOD'], $request_uri);
