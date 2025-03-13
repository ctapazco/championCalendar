<?php

namespace Controllers;

use Firebase\JWT\JWT;
use DateTime;

class AuthController
{
    private $pdo;
    private $secretKey = 'secreto';  // Cambia esto por una clave secreta más segura

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function mostrarLogin()
    {
        echo $twig->render('login.html.twig');
    }

    public function mostrarRegistro()
    {
        echo $twig->render('registro.html.twig');
    }

    public function autenticarUsuario($email, $password)
    {
        // Verificar si las credenciales son correctas
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Crear un token JWT
            $issuedAt = time();
            $expirationTime = $issuedAt + 3600;  // Token expira en 1 hora
            $payload = [
                'sub' => $usuario['id'],
                'iat' => $issuedAt,
                'exp' => $expirationTime
            ];

            $jwt = JWT::encode($payload, $this->secretKey);
            setcookie('auth_token', $jwt, $expirationTime, '/');  // Guardar el JWT en una cookie

            return true;
        }

        return false;
    }

    public function estaLogueado()
    {
        if (isset($_COOKIE['auth_token'])) {
            try {
                $decoded = JWT::decode($_COOKIE['auth_token'], $this->secretKey, ['HS256']);
                return true;
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }
}
