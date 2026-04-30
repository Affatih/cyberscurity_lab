<?php
// app/models/ProductModel.php

namespace App\Models;

use App\Core\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getLatest($limit = 10)
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

public function getFeatured($limit = 4)
{
    $sql = "SELECT p.*, c.name as category_name 
            FROM {$this->table} p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.stock > 0 
            ORDER BY RAND() 
            LIMIT :limit";
    
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
    
    public function getAllWithCategory()
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.id DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function search($keyword)
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.name LIKE :keyword 
                OR p.description LIKE :keyword
                ORDER BY p.name";
        
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%{$keyword}%";
        $stmt->bindValue(':keyword', $searchTerm);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    // METHOD RENTAN - SQL Injection
    public function searchVulnerable($keyword)
    {
        // JANGAN GUNAKAN INI DI PRODUKSI!
        // Hanya untuk demonstrasi SQL Injection
        
        $sql = "SELECT p.*, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.name LIKE '%{$keyword}%' 
                OR p.description LIKE '%{$keyword}%'
                ORDER BY p.name";
        
        return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function count()
{
    $sql = "SELECT COUNT(*) as total FROM {$this->table}";
    $stmt = $this->db->query($sql);
    $result = $stmt->fetch();
    return $result['total'];
}

public function getLowStock($limit)
{
    $sql = "SELECT * FROM {$this->table} 
            WHERE stock < 10 
            ORDER BY stock ASC 
            LIMIT $limit";
    $stmt = $this->db->query($sql);
    return $stmt->fetchAll();
}

public function createVulnerable($data)
{
    // RENTAN SQL INJECTION
    $sql = "INSERT INTO {$this->table} (name, description, price, stock, category_id, image) 
            VALUES ('{$data['name']}', '{$data['description']}', {$data['price']}, {$data['stock']}, {$data['category_id']}, '{$data['image']}')";
    return $this->db->exec($sql);
}

public function updateVulnerable($id, $data)
{
    // RENTAN SQL INJECTION
    $sql = "UPDATE {$this->table} 
            SET name = '{$data['name']}', 
                description = '{$data['description']}', 
                price = {$data['price']}, 
                stock = {$data['stock']}, 
                category_id = {$data['category_id']} 
            WHERE id = $id";
    return $this->db->exec($sql);
}
}
