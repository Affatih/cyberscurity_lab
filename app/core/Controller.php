<?php
// app/core/Controller.php

namespace App\Core;

class Controller
{
    protected $request;
    
    public function __construct()
    {
        $this->request = new Request();
    }
    
    protected function view($view, $data = [])
    {
        // Extract data to variables
        extract($data);
        
        // Build view path
        $viewFile = __DIR__ . '/../views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            die("View tidak ditemukan: " . $viewFile);
        }
        
        // Start output buffering
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
        // Include layout if exists
        $layoutFile = __DIR__ . '/../views/layouts/main.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }
    
    protected function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url)
    {
        $baseUrl = $this->getBaseUrl();
        header("Location: {$baseUrl}/" . ltrim($url, '/'));
        exit;
    }
    
    protected function getBaseUrl()
    {
        $config = require __DIR__ . '/../config/app.php';
        return $config['url'];
    }
    
    protected function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
    
    protected function isAdmin()
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    protected function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Silakan login terlebih dahulu';
            $this->redirect('login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        }
    }
    
    protected function requireAdmin()
    {
        $this->requireLogin();
        if (!$this->isAdmin()) {
            $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini';
            $this->redirect('');
        }
    }
}