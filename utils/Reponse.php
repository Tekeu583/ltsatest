<?php
namespace Utils;

class Response {
    // Envoyer une réponse JSON
    public static function json($statusCode, $data) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}