<?php
namespace App\Models;

use App\Core\Model;

class CategoryModel extends Model
{
    protected $table = 'categories';
    
    public function getAll()
    {
        $sql = "SELECT c.*, COUNT(p.id) as product_count 
                FROM {$this->table} c 
                LEFT JOIN products p ON c.id = p.category_id 
                GROUP BY c.id";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
