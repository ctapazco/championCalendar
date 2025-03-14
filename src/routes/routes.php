<?php

// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/router.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/EquipoController.php';
require_once __DIR__ . '/../controllers/JugadorController.php';
require_once __DIR__ . '/../controllers/EncuentroController.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';

// Instanciar controladores con la conexión a la base de datos
$equipoController = new EquipoController($pdo);
$jugadorController = new JugadorController($pdo);
$encuentroController = new EncuentroController($pdo);
$usuarioController = new UsuarioController($pdo);

// ===================
// Función para verificar sesión activa
// ===================
function verificarSesion() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: /usuarios/login");
        exit;
    }
}

// ===================
// Función para verificar JWT (API)
// ===================
function verificarToken() {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

    if (!$token || !str_starts_with($token, "Bearer ")) {
        http_response_code(401);
        echo json_encode(["error" => "Acceso no autorizado"]);
        exit;
    }

    $token = str_replace("Bearer ", "", $token);

    try {
        $decoded = JWT::decode($token, new Key("Ctapasco290692", 'HS256'));
        $_SESSION['usuario'] = (array) $decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Token inválido o expirado"]);
        exit;
    }
}

// ===================
//  Rutas Web
// ===================

route('GET', '/', fn() => renderizarVista('index.twig'));

//  Equipos (Web)
route('GET', '/equipos', fn() => $equipoController->listarEquiposWeb());
route('GET', '/equipos/([0-9]+)', fn($id) => $equipoController->obtenerEquipo($id));
route('GET', '/equipos/crear', fn() => $equipoController->mostrarFormularioCrearEquipo());
route('POST', '/equipos/crear', fn() => $equipoController->crearEquipo());
route('GET', '/equipos/([0-9]+)/editar', fn($id) => $equipoController->editarEquipo($id));
route('POST', '/equipos/([0-9]+)/editar', fn($id) => $equipoController->editarEquipo($id));
route('GET', '/equipos/([0-9]+)/eliminar', fn($id) => $equipoController->eliminarEquipo($id));

//  Panel de Control (Requiere sesión)
route('GET', '/panelControl', function() {
    verificarSesion();
    return renderizarVista('panelControl.twig', ["usuario" => $_SESSION['usuario']]);
});

// 👤 Autenticación (Web)
route('GET', '/usuarios/login', function() use ($usuarioController) {
    if (isset($_SESSION['usuario'])) {
        header("Location: /equipos");
        exit;
    }
    return $usuarioController->login();
});

route('POST', '/usuarios/login', fn() => $usuarioController->login());
route('GET', '/usuarios/logout', fn() => session_destroy() && header("Location: /equipos"));
route('GET', '/usuarios/registrar', fn() => $usuarioController->registrar());
route('POST', '/usuarios/registrar', fn() => $usuarioController->registrar());

// ===================
//  Rutas API (JSON)
// ===================

//  Equipos (API)
route('GET', '/api/equipos', fn() => $equipoController->listarEquiposApi());
route('GET', '/api/equipos/([0-9]+)', fn($id) => $equipoController->obtenerEquipoApi($id));
route('GET', '/api/equipos/([0-9]+)/editar', fn($id) => $equipoController->obtenerEquipoParaEditarApi($id));
route('POST', '/api/equipos', fn() => verificarToken() && $equipoController->crearEquipoApi());
route('PUT', '/api/equipos/([0-9]+)', fn($id) => verificarToken() && $equipoController->editarEquipoApi($id));
route('DELETE', '/api/equipos/([0-9]+)', fn($id) => verificarToken() && $equipoController->eliminarEquipoApi($id));

//  Jugadores (API)
route('GET', '/api/jugadores', fn() => $jugadorController->listarJugadoresApi());
route('GET', '/api/jugadores/([0-9]+)', fn($id) => $jugadorController->obtenerJugadorApi($id));
route('POST', '/api/jugadores', fn() => verificarToken() && $jugadorController->crearJugadorApi());
route('PUT', '/api/jugadores/([0-9]+)', fn($id) => verificarToken() && $jugadorController->editarJugadorApi($id));
route('DELETE', '/api/jugadores/([0-9]+)', fn($id) => verificarToken() && $jugadorController->eliminarJugadorApi($id));

//  Encuentros (API)
route('GET', '/api/encuentros', fn() => $encuentroController->listarEncuentrosApi());
route('GET', '/api/encuentros/([0-9]+)', fn($id) => $encuentroController->obtenerEncuentroApi($id));
route('POST', '/api/encuentros', fn() => verificarToken() && $encuentroController->crearEncuentroApi());
route('PUT', '/api/encuentros/([0-9]+)', fn($id) => verificarToken() && $encuentroController->editarEncuentroApi($id));
route('DELETE', '/api/encuentros/([0-9]+)', fn($id) => verificarToken() && $encuentroController->eliminarEncuentroApi($id));

//  Autenticación API
route('POST', '/api/usuarios/login', fn() => $usuarioController->loginApi());

// ===================
//  Ejecutar la ruta solicitada
// ===================
dispatch($_SERVER['REQUEST_METHOD'], strtok($_SERVER["REQUEST_URI"], '?'));
