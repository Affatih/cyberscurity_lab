<?php
// app/core/Model.php

namespace App\Core;

use PDO;
use PDOException;

class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    // AMAN: Menggunakan prepared statements
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // RENTAN: SQL Injection (untuk pelatihan)
    public function findVulnerable($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = {$id}";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($data);
    }
    
    public function update($id, $data)
    {
        $set = '';
        foreach (array_keys($data) as $key) {
            $set .= "{$key} = :{$key}, ";
        }
        $set = rtrim($set, ', ');
        
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        
        $data['id'] = $id;
        return $stmt->execute($data);
    }
    
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function rawQuery($sql)
    {
        return $this->db->query($sql);
    }
    
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }
    
    public function commit()
    {
        return $this->db->commit();
    }
    
    public function rollback()
    {
        return $this->db->rollback();
    }
}