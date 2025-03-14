<?php

require_once __DIR__ . '/../config/database.php';

class Usuario {

    public static function registrar($data) {
        global $pdo;

        if (!$pdo) {
            return ["error" => "No hay conexión con la base de datos"];
        }

        // Verificar si el nombre de usuario ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->execute([$data['usuario']]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return ["error" => "El nombre de usuario ya está registrado"];
        }

        // Verificar si el correo electrónico ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return ["error" => "El correo electrónico ya está registrado"];
        }

        // Insertar el nuevo usuario
        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password, email) VALUES (?, ?, ?)");
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);  // Encriptar la contraseña
        if ($stmt->execute([$data['usuario'], $data['password'], $data['email']])) {
            return true;
        } else {
            return ["error" => "No se pudo registrar el usuario"];
        }
    }

    
    public static function obtenerPorId($id) {
        global $pdo;

        if (!$pdo) {
            return ["error" => "No hay conexión con la base de datos"];
        }

        // Buscar un usuario por su ID
        $stmt = $pdo->prepare("SELECT id, usuario, email FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ Método para iniciar sesión
    public static function login($usuario, $password) {
        global $pdo;
    
        if (!$pdo) {
            return ["error" => "No hay conexión con la base de datos"];
        }
    
        // Buscar el usuario en la base de datos
        $stmt = $pdo->prepare("SELECT id, usuario, password, email FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Si el usuario no existe o la contraseña no coincide
        if (!$usuarioData || !password_verify($password, $usuarioData['password'])) {
            return null; // Retorna `null` si las credenciales no son correctas
        }
    
        // Eliminar la contraseña antes de devolver los datos
        unset($usuarioData['password']);
    
        return $usuarioData;  // Devuelve los datos del usuario sin la contraseña
    }
}
