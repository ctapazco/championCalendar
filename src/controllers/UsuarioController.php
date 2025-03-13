<?php

require_once __DIR__ . '/../models/Usuarios.php';
require_once __DIR__ . '/../config/view.php';

use \Firebase\JWT\JWT;

class UsuarioController {

    private $key = "Ctapasco290692"; 

    // Método para manejar el inicio de sesión y generar un token
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
    
            // Validar los datos del formulario
            if (empty($data['usuario']) || empty($data['password'])) {
                echo json_encode(["error" => "Todos los campos son obligatorios"]);
                return;
            }
    
            // Llamar al modelo para validar el usuario
            $response = Usuario::login($data['usuario'], $data['password']);
            
            if ($response === null) {
                // Si las credenciales son incorrectas, pasamos el error a la vista
                renderizarVista('loginUsuario.twig', ['error' => 'Usuario o contraseña incorrectos']);
                return;
            }
    
            // Decodificar la respuesta
            $usuarioData = json_decode($response, true);
    
            // El login es exitoso, generar el token JWT
            $issuedAt = time();
            $expirationTime = $issuedAt + 3600;  // El token será válido por 1 hora
            $payload = array(
                "usuario" => $data['usuario'],
                "iat" => $issuedAt,
                "exp" => $expirationTime
            );

            // Aquí agregamos el tercer argumento 'HS256'
            $jwt = JWT::encode($payload, $this->key, 'HS256');

            // Guardamos los datos del usuario en la sesión
            $_SESSION['usuario'] = $usuarioData; // Guardamos la información relevante del usuario en la sesión

            // Redirigir al usuario a la página de equipos
            header("Location: /equipos");
            exit;
        } else {
            renderizarVista('loginUsuario.twig');
        }
    }

    // Método para registrar un nuevo usuario
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;

            // Validar los datos del formulario
            if (empty($data['usuario']) || empty($data['password']) || empty($data['email'])) {
                echo json_encode(["error" => "Todos los campos son obligatorios"]);
                return;
            }

            // Registrar al usuario
            $response = Usuario::registrar($data);

            if ($response) {
                // Si el registro fue exitoso, redirigir al login
                echo json_encode(["mensaje" => "Usuario registrado con éxito"]);
                header("Location: /usuarios/login"); // Redirigir al login
                exit;
            } else {
                // Si no se pudo registrar, mostrar un error
                echo json_encode(["error" => "No se pudo registrar el usuario"]);
            }
        } else {
            // Mostrar la vista de registro
            renderizarVista('registrarUsuario.twig');
        }
    }
}
