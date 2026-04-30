<?php
namespace App\Models;

use App\Core\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    
    public function create($data)
    {
        $sql = "INSERT INTO orders (order_number, user_id, total_amount, status, payment_method, shipping_address, notes) 
                VALUES (:order_number, :user_id, :total_amount, :status, :payment_method, :shipping_address, :notes)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    public function addItem($data)
    {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                VALUES (:order_id, :product_id, :quantity, :price, :subtotal)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    public function getUserOrders($userId)
    {
        // KERENTANAN: SQL Injection di sini!
        $sql = "SELECT * FROM orders WHERE user_id = $userId ORDER BY created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }
    
    public function getOrderWithItems($orderId)
    {
        $sql = "SELECT o.*, u.username, u.full_name 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                WHERE o.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch();
        
        if ($order) {
            $sql = "SELECT oi.*, p.name as product_name 
                    FROM order_items oi 
                    LEFT JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = :order_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['order_id' => $orderId]);
            $order['items'] = $stmt->fetchAll();
        }
        
        return $order;
    }
    
    public function count()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    public function getRecent($limit)
    {
        $sql = "SELECT o.*, u.username 
                FROM {$this->table} o 
                LEFT JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC 
                LIMIT $limit";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
