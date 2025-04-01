<?php
namespace Middleware;

require_once __DIR__ . '/../services/JwtService.php';
require_once __DIR__ . '/../utils/Response.php';

use Services\JwtService;
use Utils\Response;

class AuthMiddleware {
    private $jwtService;
    
    public function __construct() {
        $this->jwtService = new JwtService();
    }
    
    // Vérifier l'authentification
    public function authenticate() {
        // Récupérer les headers
        $headers = getallheaders();
        
        // Vérifier si le header d'autorisation est présent
        if(!isset($headers['Authorization']) && !isset($headers['authorization'])) {
            Response::json(401, ['message' => 'Token non fourni']);
            exit();
        }
        
        // Récupérer le token
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
        $token = str_replace('Bearer ', '', $authHeader);
        
        // Valider le token
        $payload = $this->jwtService->validateToken($token);
        
        if(!$payload) {
            Response::json(401, ['message' => 'Token invalide ou expiré']);
            exit();
        }
        
        return $payload;
    }
}