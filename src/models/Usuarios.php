<?php

require_once __DIR__ . '/../config/database.php';

class Usuario {

    public static function registrar($data) {
        global $pdo;

        if (!$pdo) {
            return json_encode(["error" => "No hay conexión con la base de datos"]);
        }

        // Verificar si el nombre de usuario ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->execute([$data['usuario']]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return json_encode(["error" => "El nombre de usuario ya está registrado"]);
        }

        // Verificar si el correo electrónico ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return json_encode(["error" => "El correo electrónico ya está registrado"]);
        }

        // Insertar el nuevo usuario
        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password, email) VALUES (?, ?, ?)");
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);  // Encriptar la contraseña
        if ($stmt->execute([$data['usuario'], $data['password'], $data['email']])) {
            return json_encode(["mensaje" => "Usuario registrado con éxito"]);
        } else {
            return json_encode(["error" => "No se pudo registrar el usuario"]);
        }
    }

    
    public static function obtenerPorId($id) {
        global $pdo;

        if (!$pdo) {
            return json_encode(["error" => "No hay conexión con la base de datos"]);
        }

        // Buscar un usuario por su ID
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Método para iniciar sesión
    public static function login($usuario, $password) {
        global $pdo;

    if (!$pdo) {
        return json_encode(["error" => "No hay conexión con la base de datos"]);
    }

    // Buscar el usuario en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si el usuario existe y la contraseña es correcta
    if ($usuarioData && password_verify($password, $usuarioData['password'])) {
        return json_encode($usuarioData);  // Retorna los datos del usuario en formato JSON
    }

    // Si las credenciales no son correctas
    return null;
}



}
