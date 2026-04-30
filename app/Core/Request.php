<?php
namespace App\Core;

class Request
{
    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    public function isPost()
    {
        return $this->getMethod() === 'POST';
    }
    
    public function isGet()
    {
        return $this->getMethod() === 'GET';
    }
    
    public function get($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
    
    public function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }
    
    public function files($key = null)
    {
        if ($key === null) {
            return $_FILES;
        }
        return $_FILES[$key] ?? null;
    }
    
    public function getBody()
    {
        return file_get_contents('php://input');
    }
}
