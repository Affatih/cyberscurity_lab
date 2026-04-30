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
        extract($data);
        
        $viewFile = __DIR__ . '/../views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            die("View tidak ditemukan: " . $viewFile);
        }
        
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
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
}
