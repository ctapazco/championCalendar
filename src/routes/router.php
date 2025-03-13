<?php

$routes = [
    'GET' => [],
    'POST' => []
];

// Función para registrar rutas
function route($method, $path, $callback) {
    global $routes;
    $routes[$method][$path] = $callback;
}


function dispatch($method, $path) {
    global $routes;

    foreach ($routes[$method] as $route => $callback) {
        $pattern = '#^' . preg_replace('/\{[a-zA-Z0-9_]+\}/', '([0-9]+)', $route) . '$#';

        if (preg_match($pattern, $path, $matches)) {
            array_shift($matches);
            echo call_user_func_array($callback, $matches);
            return;
        }
    }

    http_response_code(404);
    echo "404 - Página no encontrada";
}

route('GET', '/panelControl', function() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: /usuarios/login");
        exit;
    }

    $equipos = Equipo::obtenerTodos();
    renderizarVista('panelControl.twig', ['equipos' => $equipos]);
});

route('GET', '/usuarios/logout', function() {
    session_unset();  // Eliminar todas las variables de sesión
    session_destroy();  // Destruir la sesión
    header("Location: /equipos");  // Redirigir al usuario a la lista de equipos
    exit;
});

