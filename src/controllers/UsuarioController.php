<?php

require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../config/view.php';
require_once __DIR__ . '/../config/database.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UsuarioController {
    private $pdo;
    private $key = "Ctapasco290692"; // Clave secreta para firmar JWT

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * ✅ Función auxiliar para respuestas en JSON
     * @param mixed $data - Datos a enviar en JSON
     * @param int $statusCode - Código de respuesta HTTP (default 200)
     */
    private function responseJSON($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * ✅ API: Iniciar sesión y devolver JWT (JSON)
     */
    public function loginApi() {
        // Obtener datos de la solicitud
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar campos obligatorios
        if (empty($data['usuario']) || empty($data['password'])) {
            return $this->responseJSON(["error" => "Usuario y contraseña son obligatorios"], 400);
        }

        // Validar credenciales en la base de datos
        $usuario = Usuario::login($data['usuario'], $data['password']);

        if (!$usuario) {
            return $this->responseJSON(["error" => "Credenciales incorrectas"], 401);
        }

        // Generar JWT con tiempo de expiración de 1 hora
        $jwt = JWT::encode([
            "usuario" => $usuario["usuario"],
            "iat" => time(),
            "exp" => time() + 3600
        ], $this->key, 'HS256');

        // Responder con JSON incluyendo el token
        return $this->responseJSON([
            "mensaje" => "Login exitoso",
            "token" => $jwt,
            "usuario" => [
                "id" => $usuario["id"],
                "usuario" => $usuario["usuario"],
                "email" => $usuario["email"]
            ]
        ]);
    }

    /**
     * ✅ API: Registrar usuario y devolver mensaje en JSON
     */
    public function registrarApi() {
        // Obtener datos de la solicitud
        $data = json_decode(file_get_contents("php://input"), true);

        // Validar campos obligatorios
        if (empty($data['usuario']) || empty($data['password']) || empty($data['email'])) {
            return $this->responseJSON(["error" => "Todos los campos son obligatorios"], 400);
        }

        // Intentar registrar usuario
        $resultado = Usuario::registrar($data);

        // Responder según el resultado
        return is_array($resultado) && isset($resultado["error"]) 
            ? $this->responseJSON($resultado, 400) // Error al registrar
            : $this->responseJSON(["mensaje" => "Usuario registrado con éxito"], 201);
    }

    /**
     * ✅ Web: Iniciar sesión y redirigir a la vista web
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;

            // Validar campos obligatorios
            if (empty($data['usuario']) || empty($data['password'])) {
                return renderizarVista('loginUsuario.twig', ['error' => 'Todos los campos son obligatorios']);
            }

            // Validar credenciales en la base de datos
            $usuario = Usuario::login($data['usuario'], $data['password']);

            if (!$usuario) {
                return renderizarVista('loginUsuario.twig', ['error' => 'Usuario o contraseña incorrectos']);
            }

            // Generar JWT con tiempo de expiración de 1 hora
            $jwt = JWT::encode([
                "usuario" => $usuario["usuario"],
                "iat" => time(),
                "exp" => time() + 3600
            ], $this->key, 'HS256');

            // Iniciar sesión y redirigir al usuario a la vista de equipos
            $_SESSION['usuario'] = $usuario;
            header("Location: /equipos");
            exit;
        } 
        
        // Mostrar vista de login
        renderizarVista('loginUsuario.twig');
    }

    /**
     * ✅ Web: Registrar usuario y mostrar formulario
     */
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;

            if (empty($data['usuario']) || empty($data['password']) || empty($data['email'])) {
                return renderizarVista('registroUsuario.twig', ['error' => 'Todos los campos son obligatorios']);
            }

            $resultado = Usuario::registrar($data);

            if (is_array($resultado) && isset($resultado["error"])) {
                return renderizarVista('registroUsuario.twig', ['error' => $resultado["error"]]);
            }

            $_SESSION['mensaje'] = "Registro exitoso. Ahora puedes iniciar sesión.";
            header("Location: /usuarios/login");
            exit;
        }

        renderizarVista('registrarUsuario.twig');
    }

}
