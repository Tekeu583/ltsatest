<?php
// Importer les contrôleurs
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use Controllers\AuthController;
use Controllers\AdminController;
use Middleware\AuthMiddleware;

// Instancier les contrôleurs
$authController = new AuthController();
$adminController = new AdminController();
$authMiddleware = new AuthMiddleware();

// Récupérer la méthode HTTP et l'URI
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Routes publiques (sans authentification)
if($requestMethod === 'POST' && $uri === '/api/register') {
    $authController->register();
    exit();
}

if($requestMethod === 'POST' && $uri === '/api/login') {
    $authController->login();
    exit();
}

if($requestMethod === 'POST' && $uri === '/api/forgot-password') {
    $authController->forgotPassword();
    exit();
}

if($requestMethod === 'POST' && $uri === '/api/reset-password') {
    $authController->resetPassword();
    exit();
}

// Routes protégées (avec authentification)
// Vérifier l'authentification
$payload = $authMiddleware->authenticate();
$userId = $payload['id'];

if($requestMethod === 'GET' && $uri === '/api/profile') {
    $adminController->getProfile($userId);
    exit();
}

if($requestMethod === 'PUT' && $uri === '/api/update-password') {
    $adminController->updatePassword($userId);
    exit();
}

// Route non trouvée
http_response_code(404);
echo json_encode(['message' => 'Route non trouvée']);