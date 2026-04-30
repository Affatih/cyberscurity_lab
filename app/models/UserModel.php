<?php
namespace App\Models;

use App\Core\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    // METHOD AMAN
    public function findByUsername($username)
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }
    
    // KERENTANAN: SQL Injection (hanya username)
    public function findByUsernameVulnerable($username)
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = '$username' LIMIT 1";
        $result = $this->db->query($sql);
        return $result->fetch();
    }
    
    // *** TAMBAHAN UNTUK LOGIN BYPASS ***
    // KERENTANAN: SQL Injection pada username dan password (tanpa prepared statement)
    public function findByCredentialsVulnerable($username, $password)
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = '$username' AND password = '$password'";
        $result = $this->db->query($sql);
        return $result->fetch();
    }
    // *********************************
    
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
    
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (username, email, password, full_name, role) 
                VALUES (:username, :email, :password, :full_name, :role)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
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
        $sql = "SELECT id, username, full_name, email, role, created_at 
                FROM {$this->table} 
                ORDER BY created_at DESC 
                LIMIT $limit";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
