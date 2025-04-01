<?php
namespace Config;

require_once __DIR__ . '/config.php';

use PDO;
use PDOException;

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    
    public function __construct() {
        global $db_config;
        $this->host = $db_config['host'];
        $this->db_name = $db_config['db_name'];
        $this->username = $db_config['username'];
        $this->password = $db_config['password'];
    }
    
    // Établir la connexion à la base de données
    public function connect() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name,
                $this->username,
                $this->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Erreur de connexion: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}