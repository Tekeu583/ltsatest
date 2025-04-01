<?php
// Configuration de la base de données
$db_config = [
    'host' => 'localhost',
    'db_name' => 'ltsa',
    'username' => 'root',
    'password' => ''
];

// Configuration JWT
$jwt_config = [
    'secret_key' => bin2hex(random_bytes(32)), // À remplacer par une vraie clé secrète
    'issuer' => 'http://localhost/ltsa', // URL de votre application
    'expiration_time' => 3600 // 1 heure en secondes
];

// Configuration du mailer
$mail_config = [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'arsenetekeu@gmail.com',
    'password' => 'nubq iewq xbsv rxop',
    'encryption' => 'tls',
    'from_email' => 'arsenetekeu@gmail.com',
    'from_name' => 'Système d\'administration'
];