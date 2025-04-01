<?php
namespace Models;

require_once __DIR__ . '/../config/Database.php';

use Config\Database;
use PDO;
use PDOException;

class Admin {
    // Propriétés de la base de données
    private $conn;
    private $table = 'admins';
    
    // Propriétés de l'administrateur
    public $id;
    public $nom;
    public $email;
    public $password;
    public $reset_token;
    public $reset_token_expiry;
    public $created_at;
    
    // Constructeur avec connexion à la base de données
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    // Créer un nouvel administrateur
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (nom, email, motDePasse, created_at) 
                  VALUES (:nom, :email, :motDePasse, NOW())";
        
        $stmt = $this->conn->prepare($query);
        
        // Nettoyer les données
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        
        // Lier les valeurs
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':motDePasse', $this->password);
        
        try {
            if($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
    // Trouver un administrateur par email
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        $email = htmlspecialchars(strip_tags($email));
        $stmt->bindParam(':email', $email);
        
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->nom = $row['nom'];
            $this->email = $row['email'];
            $this->password = $row['motDePasse'];
            $this->reset_token = $row['reset_token'] ?? null;
            $this->reset_token_expiry = $row['reset_token_expiry'] ?? null;
            $this->created_at = $row['created_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Trouver un administrateur par ID
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(':id', $id);
        
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->nom = $row['nom'];
            $this->email = $row['email'];
            $this->password = $row['motDePasse'];
            $this->reset_token = $row['reset_token'] ?? null;
            $this->reset_token_expiry = $row['reset_token_expiry'] ?? null;
            $this->created_at = $row['created_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Mettre à jour le mot de passe
    public function updatePassword($new_password) {
        $query = "UPDATE " . $this->table . " 
                  SET motDePasse = :motDePasse, reset_token = NULL, reset_token_expiry = NULL 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $new_password = password_hash($new_password, PASSWORD_BCRYPT);
        
        $stmt->bindParam(':motDePasse', $new_password);
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Stocker le token de réinitialisation
    public function storeResetToken($token) {
        $query = "UPDATE " . $this->table . " 
                  SET reset_token = :token, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Vérifier si le token de réinitialisation est valide
    public function isValidResetToken($token) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE reset_token = :token AND reset_token_expiry > NOW() 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':token', $token);
        
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->nom = $row['nom'];
            $this->email = $row['email'];
            $this->password = $row['motDePasse'];
            $this->reset_token = $row['reset_token'];
            $this->reset_token_expiry = $row['reset_token_expiry'];
            $this->created_at = $row['created_at'];
            
            return true;
        }
        
        return false;
    }
}
?>