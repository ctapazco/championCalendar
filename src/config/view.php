<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Configurar Twig
$loader = new FilesystemLoader(__DIR__ . '/../views');
$twig = new Environment($loader);

function renderizarVista($template, $datos = []) {
    global $twig;
    echo $twig->render($template, $datos);
}
