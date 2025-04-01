<?php
namespace Controllers;

require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../utils/Response.php';

use Models\Admin;
use Utils\Response;

class AdminController {
    private $adminModel;
    
    public function __construct() {
        $this->adminModel = new Admin();
    }
    
    // Obtenir le profil de l'administrateur
    public function getProfile($id) {
        if(!$this->adminModel->findById($id)) {
            return Response::json(404, ['message' => 'Administrateur non trouvé']);
        }
        
        return Response::json(200, [
            'id' => $this->adminModel->id,
            'nom' => $this->adminModel->nom,
            'email' => $this->adminModel->email,
            'created_at' => $this->adminModel->created_at
        ]);
    }
    
    // Mettre à jour le mot de passe
    public function updatePassword($id) {
        // Récupérer les données POST
        $data = json_decode(file_get_contents("php://input"));
        
        // Vérifier si toutes les données requises sont présentes
        if(!isset($data->current_password) || !isset($data->new_password)) {
            return Response::json(400, ['message' => 'Mot de passe actuel et nouveau mot de passe requis']);
        }
        
        // Vérifier si l'administrateur existe
        if(!$this->adminModel->findById($id)) {
            return Response::json(404, ['message' => 'Administrateur non trouvé']);
        }
        
        // Vérifier le mot de passe actuel
        if(!password_verify($data->current_password, $this->adminModel->password)) {
            return Response::json(401, ['message' => 'Mot de passe actuel incorrect']);
        }
        
        // Mettre à jour le mot de passe
        if($this->adminModel->updatePassword($data->new_password)) {
            return Response::json(200, ['message' => 'Mot de passe mis à jour avec succès']);
        } else {
            return Response::json(500, ['message' => 'Erreur lors de la mise à jour du mot de passe']);
        }
    }
}
?>