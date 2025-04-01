<?php
namespace Controllers;

require_once __DIR__ . '/../models/Admin.php';
require_once __DIR__ . '/../services/JwtService.php';
require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../utils/Response.php';

use Models\Admin;
use Services\JwtService;
use Services\MailService;
use Utils\Response;

class AuthController {
    private $adminModel;
    private $jwtService;
    private $mailService;
    
    public function __construct() {
        $this->adminModel = new Admin();
        $this->jwtService = new JwtService();
        $this->mailService = new MailService();
    }
    
    // Méthode d'inscription d'un administrateur
    public function register() {
        // Récupérer les données POST
        $data = json_decode(file_get_contents("php://input"));
        
        // Vérifier si toutes les données requises sont présentes
        if(!isset($data->nom) || !isset($data->email) || !isset($data->password)) {
            return Response::json(400, ['message' => 'Données incomplètes']);
        }
        
        // Vérifier si l'email est déjà utilisé
        if($this->adminModel->findByEmail($data->email)) {
            return Response::json(409, ['message' => 'Cet email est déjà utilisé']);
        }
        
        // Affecter les données au modèle
        $this->adminModel->nom = $data->nom;
        $this->adminModel->email = $data->email;
        $this->adminModel->password = $data->password;
        
        // Créer l'administrateur
        if($this->adminModel->create()) {
            // Générer le token JWT
            $token = $this->jwtService->generateToken([
                'id' => $this->adminModel->id,
                'email' => $this->adminModel->email
            ]);
            
            return Response::json(201, [
                'message' => 'Administrateur créé avec succès',
                'admin' => [
                    'id' => $this->adminModel->id,
                    'nom' => $this->adminModel->nom,
                    'email' => $this->adminModel->email
                ],
                'token' => $token
            ]);
        } else {
            return Response::json(500, ['message' => 'Erreur lors de la création de l\'administrateur']);
        }
    }
    
    // Méthode de connexion
    public function login() {
        // Récupérer les données POST
        $data = json_decode(file_get_contents("php://input"));
        
        // Vérifier si toutes les données requises sont présentes
        if(!isset($data->email) || !isset($data->password)) {
            return Response::json(400, ['message' => 'Email et mot de passe requis']);
        }
        
        // Vérifier si l'administrateur existe
        if(!$this->adminModel->findByEmail($data->email)) {
            return Response::json(404, ['message' => 'Administrateur non trouvé']);
        }
        
        // Vérifier le mot de passe
        if(password_verify($data->password, $this->adminModel->password)) {
            // Générer le token JWT
            $token = $this->jwtService->generateToken([
                'id' => $this->adminModel->id,
                'email' => $this->adminModel->email
            ]);
            
            return Response::json(200, [
                'message' => 'Connexion réussie',
                'admin' => [
                    'id' => $this->adminModel->id,
                    'nom' => $this->adminModel->nom,
                    'email' => $this->adminModel->email
                ],
                'token' => $token
            ]);
        } else {
            return Response::json(401, ['message' => 'Mot de passe incorrect']);
        }
    }
    
    // Méthode de demande de réinitialisation de mot de passe
    public function forgotPassword() {
        // Récupérer les données POST
        $data = json_decode(file_get_contents("php://input"));
        
        // Vérifier si l'email est présent
        if(!isset($data->email)) {
            return Response::json(400, ['message' => 'Email requis']);
        }
        
        // Vérifier si l'administrateur existe
        if(!$this->adminModel->findByEmail($data->email)) {
            return Response::json(404, ['message' => 'Administrateur non trouvé']);
        }
        
        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        
        // Stocker le token dans la base de données
        if($this->adminModel->storeResetToken($token)) {
            // URL de réinitialisation
            $resetUrl = $_SERVER['HTTP_ORIGIN'] . '/reset-password?token=' . $token;
            
            // Envoyer l'email
            $subject = 'Réinitialisation de votre mot de passe';
            $body = "Bonjour {$this->adminModel->nom},<br><br>";
            $body .= "Vous avez demandé la réinitialisation de votre mot de passe. ";
            $body .= "Veuillez cliquer sur le lien ci-dessous pour réinitialiser votre mot de passe :<br><br>";
            $body .= "<a href=\"{$resetUrl}\">{$resetUrl}</a><br><br>";
            $body .= "Ce lien expirera dans 1 heure.<br><br>";
            $body .= "Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.";
            
            if($this->mailService->sendEmail($this->adminModel->email, $subject, $body)) {
                return Response::json(200, ['message' => 'Email de réinitialisation envoyé']);
            } else {
                return Response::json(500, ['message' => 'Erreur lors de l\'envoi de l\'email']);
            }
        } else {
            return Response::json(500, ['message' => 'Erreur lors de la génération du token']);
        }
    }
    
    // Méthode de réinitialisation de mot de passe
    public function resetPassword() {
        // Récupérer les données POST
        $data = json_decode(file_get_contents("php://input"));
        
        // Vérifier si toutes les données requises sont présentes
        if(!isset($data->token) || !isset($data->password)) {
            return Response::json(400, ['message' => 'Token et nouveau mot de passe requis']);
        }
        
        // Vérifier si le token est valide
        if(!$this->adminModel->isValidResetToken($data->token)) {
            return Response::json(400, ['message' => 'Token invalide ou expiré']);
        }
        
        // Mettre à jour le mot de passe
        if($this->adminModel->updatePassword($data->password)) {
            return Response::json(200, ['message' => 'Mot de passe réinitialisé avec succès']);
        } else {
            return Response::json(500, ['message' => 'Erreur lors de la réinitialisation du mot de passe']);
        }
    }
}