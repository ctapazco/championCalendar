<?php
use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config/database.php';

$app = AppFactory::create();

// Middleware para sesiones
session_start();

// Rutas principales
$app->get('/', function ($request, $response, $args) {
    require_once __DIR__ . '/../views/home/index.twig';
    return $response;
});

$app->post('/register', function ($request, $response) {
    $authController = new AuthController($GLOBALS['pdo']);
    return $authController->register($request, $response);
});

$app->post('/login', function ($request, $response) {
    $authController = new AuthController($GLOBALS['pdo']);
    return $authController->login($request, $response);
});


$app->get('/login', function ($request, $response) {
    require __DIR__ . '/../public/login.php';
    return $response;
});

// Iniciar la app
$app->run();
