<?php

// Al principio del archivo
session_start();

require_once __DIR__ . '/router.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/EquipoController.php';
require_once __DIR__ . '/../controllers/JugadorController.php';
require_once __DIR__ . '/../controllers/EncuentroController.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';

// Función para verificar JWT (esto sigue siendo necesario si usas JWT)
function verificarToken() {
    $headers = getallheaders();

    // Verificar si el header Authorization está presente
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["error" => "Acceso no autorizado"]);
        exit;
    }

    // Obtener el token del header
    $token = str_replace("Bearer ", "", $headers['Authorization']);

    try {
        // Decodificar el token usando la clave secreta
        $decoded = JWT::decode($token, "Ctapasco290692", array('HS256'));
        $_SESSION['usuario'] = $decoded;  // Guardar los datos decodificados en la sesión
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Token inválido o expirado"]);
        exit;
    }
}

// Ruta para la página principal (index)
route('GET', '/', function() {
    return renderizarVista('index.twig');  
});

// Rutas de equipos (acceso público)
route('GET', '/equipos', function() {
    return (new EquipoController())->listarEquipos();
});

// Ruta para mostrar el equipo específico
route('GET', '/equipos/([0-9]+)', function($id) {
    return (new EquipoController())->obtenerEquipo($id);
});

// Rutas de Usuarios (para login, logout y registro)
route('GET', '/usuarios/login', function() {
    if (isset($_SESSION['usuario'])) {
        // Si el usuario ya está logueado, redirigir a la página de equipos
        header("Location: /equipos");
        exit;
    }
    return (new UsuarioController())->login();  // Mostrar formulario de login
});

route('POST', '/usuarios/login', function() {
    return (new UsuarioController())->login();  // Procesar el login
});

route('GET', '/usuarios/logout', function() {
    session_start();  // Asegúrate de llamar a session_start() aquí
    session_unset();  // Eliminar todas las variables de sesión
    session_destroy();  // Destruir la sesión
    header("Location: /equipos");  // Redirigir al usuario a la lista de equipos
    exit;
});

// Ruta para mostrar el formulario de registro
route('GET', '/usuarios/registrar', function() {
    return (new UsuarioController())->registrar();  // Mostrar formulario de registro
});

route('POST', '/usuarios/registrar', function() {
    return (new UsuarioController())->registrar();  // Procesar el registro
});

// Rutas protegidas (requieren autenticación)
route('POST', '/equipos', function() {
    verificarToken();  // Verificar que el usuario esté autenticado
    return (new EquipoController())->crearEquipo();
});

// Ruta del panel de control
route('GET', '/panelControl', function() {
    if (!isset($_SESSION['usuario'])) {
        // Si no está logueado, redirigir al login
        header("Location: /usuarios/login");
        exit;
    }
    return renderizarVista('panelControl.twig');
});

// Ruta para editar equipo (GET y POST)
route('GET', '/equipos/([0-9]+)/editar', function($id) {
    if (!isset($_SESSION['usuario'])) {
        // Si no está logueado, redirigir al login
        header("Location: /usuarios/login");
        exit;
    }
    return (new EquipoController())->editarEquipo($id);  // Mostrar formulario de edición del equipo
});

route('POST', '/equipos/([0-9]+)/editar', function($id) {
    if (!isset($_SESSION['usuario'])) {
        // Si no está logueado, redirigir al login
        header("Location: /usuarios/login");
        exit;
    }
    return (new EquipoController())->editarEquipo($id);  // Procesar la edición del equipo
});
// Ruta para eliminar un equipo
route('GET', '/equipos/([0-9]+)/eliminar', function($id) {
    if (!isset($_SESSION['usuario'])) {
        header("Location: /usuarios/login");
        exit;
    }
    return (new EquipoController())->eliminarEquipo($id);  // Procesar la eliminación del equipo
});

// Ruta para crear equipo
route('GET', '/equipos/crear', function() {
    return (new EquipoController())->crearEquipo();  // Mostrar formulario para crear equipo
});

route('POST', '/equipos/crear', function() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: /usuarios/login");
        exit;
    }
    return (new EquipoController())->crearEquipo();  // Procesar la creación del equipo
});

// Ejecutar la ruta solicitada
dispatch($_SERVER['REQUEST_METHOD'], strtok($_SERVER["REQUEST_URI"], '?'));
