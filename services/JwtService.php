<?php
namespace Services;

require_once __DIR__ . '/../config/config.php';

class JwtService {
    private $secretKey;
    private $issuer;
    private $expirationTime;
    
    public function __construct() {
        global $jwt_config;
        $this->secretKey = $jwt_config['secret_key'];
        $this->issuer = $jwt_config['issuer'];
        $this->expirationTime = $jwt_config['expiration_time']; // en secondes
    }
    
    // Générer un token JWT
    public function generateToken($payload) {
        // Entête du token
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);
        
        // Données du token
        $payload['iss'] = $this->issuer;
        $payload['iat'] = time();
        $payload['exp'] = time() + $this->expirationTime;
        $payload = json_encode($payload);
        
        // Encoder en Base64Url
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);
        
        // Générer la signature
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secretKey, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        // Créer le token
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        return $jwt;
    }
    
    // Vérifier un token JWT
    public function validateToken($token) {
        // Diviser le token
        $tokenParts = explode(".", $token);
        
        // Vérifier si le token a 3 parties
        if(count($tokenParts) != 3) {
            return false;
        }
        
        // Récupérer les parties
        $header = $this->base64UrlDecode($tokenParts[0]);
        $payload = $this->base64UrlDecode($tokenParts[1]);
        $signatureProvided = $tokenParts[2];
        
        // Vérifier la signature
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secretKey, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        if($base64UrlSignature !== $signatureProvided) {
            return false;
        }
        
        // Vérifier l'expiration
        $payloadObj = json_decode($payload);
        if(isset($payloadObj->exp) && $payloadObj->exp < time()) {
            return false;
        }
        
        return json_decode($payload, true);
    }
    
    // Encoder en Base64Url
    private function base64UrlEncode($data) {
        $b64 = base64_encode($data);
        $url = strtr($b64, '+/', '-_');
        return rtrim($url, '=');
    }
    
    // Décoder de Base64Url
    private function base64UrlDecode($data) {
        $b64 = strtr($data, '-_', '+/');
        $b64 = str_pad($b64, strlen($b64) % 4, '=', STR_PAD_RIGHT);
        return base64_decode($b64);
    }
}