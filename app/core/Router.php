<?php
// app/core/Router.php

namespace App\Core;

class Router
{
    private $routes = [];
    private $params = [];
    
    public function add($method, $route, $handler)
    {
        // Convert route to regex
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-zA-Z0-9-]+)', $route);
        $route = '/^' . $route . '$/i';
        
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $route,
            'handler' => $handler
        ];
    }
    
    public function dispatch($method, $uri)
    {
        $method = strtoupper($method);
        
        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Remove trailing slash
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method && $route['method'] !== 'ANY') {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named params
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $this->params[$key] = $value;
                    }
                }
                
                return $this->callHandler($route['handler']);
            }
        }
        
        // 404 Not Found
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 - Halaman Tidak Ditemukan</h1>";
        echo "<p>URL: " . htmlspecialchars($uri) . "</p>";
        exit;
    }
    
    private function callHandler($handler)
    {
        list($controllerName, $methodName) = explode('@', $handler);
        $controllerClass = "App\\Controllers\\{$controllerName}";
        
        if (!class_exists($controllerClass)) {
            die("Controller {$controllerClass} tidak ditemukan");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $methodName)) {
            die("Method {$methodName} tidak ditemukan di controller {$controllerClass}");
        }
        
        // Call controller method with params
        return call_user_func_array([$controller, $methodName], $this->params);
    }
    
    public function getRoutes()
    {
        return $this->routes;
    }
}