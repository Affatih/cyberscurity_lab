<?php
namespace App\Core;

class Router
{
    private $routes = [];
    private $params = [];
    
    public function add($method, $route, $handler)
    {
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
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method && $route['method'] !== 'ANY') {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $this->params[$key] = $value;
                    }
                }
                
                return $this->callHandler($route['handler']);
            }
        }
        
        // 404
        header("HTTP/1.0 404 Not Found");
        echo "404 - Halaman tidak ditemukan";
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
            die("Method {$methodName} tidak ditemukan");
        }
        
        return call_user_func_array([$controller, $methodName], array_values($this->params));
    }
}
