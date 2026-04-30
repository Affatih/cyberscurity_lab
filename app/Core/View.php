<?php
// app/core/View.php

namespace App\Core;

class View
{
    public static function render($view, $data = [])
    {
        extract($data);
        
        $viewFile = __DIR__ . '/../views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View tidak ditemukan: " . $viewFile);
        }
        
        require $viewFile;
    }
    
    public static function renderWithLayout($view, $layout = 'main', $data = [])
    {
        extract($data);
        
        $viewFile = __DIR__ . '/../views/' . str_replace('.', '/', $view) . '.php';
        $layoutFile = __DIR__ . '/../views/layouts/' . $layout . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View tidak ditemukan: " . $viewFile);
        }
        
        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout tidak ditemukan: " . $layoutFile);
        }
        
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        
        require $layoutFile;
    }
}
