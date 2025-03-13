<?php

require_once __DIR__ . '/../config/database.php';

class Equipo {

    public static function obtenerTodos() {
        global $pdo;
    
        if (!$pdo) {
            return json_encode(["error" => "No hay conexión con la base de datos"]);
        }
    
        $stmt = $pdo->query("SELECT * FROM equipos");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Método para crear un nuevo equipo
    public static function crear($data) {
        global $pdo;
    
        // Comprobar si la conexión está activa
        if (!$pdo) {
            return json_encode(["error" => "No hay conexión con la base de datos"]);
        }
    
        // Preparar la consulta para insertar un nuevo equipo
        $stmt = $pdo->prepare("INSERT INTO equipos (nombre, estadio, pais, url_imagen) VALUES (?, ?, ?, ?)");
    
        // Ejecutar la consulta
        if ($stmt->execute([
            $data['nombre'],
            $data['estadio'],
            $data['pais'],
            $data['url_imagen']
        ])) {
            return json_encode(["mensaje" => "Equipo creado con éxito", "id" => $pdo->lastInsertId()]);
        } else {
            return json_encode(["error" => "No se pudo crear el equipo"]);
        }
    }

    // Método para actualizar un equipo
    public static function actualizar($id, $data) {
        global $pdo;

        if (!$pdo) {
            return json_encode(["error" => "No hay conexión con la base de datos"]);
        }

        // Prepara la consulta de actualización
        $stmt = $pdo->prepare("
            UPDATE equipos 
            SET nombre = ?, pais = ?, estadio = ?, url_imagen = ? 
            WHERE id = ?
        ");

        // Ejecuta la consulta de actualización
        if ($stmt->execute([
            $data['nombre'],
            $data['pais'],
            $data['estadio'],
            $data['url_imagen'],
            $id
        ])) {
            return json_encode(["mensaje" => "Equipo actualizado con éxito"]);
        } else {
            return json_encode(["error" => "No se pudo actualizar el equipo"]);
        }
    }

    // Método para eliminar un equipo y sus encuentros asociados
    public static function eliminar($id) {
        global $pdo;

        if (!$pdo) {
            return json_encode(["error" => "No hay conexión con la base de datos"]);
        }

        // Primero, elimina los encuentros donde el equipo sea local o visitante
        $stmt = $pdo->prepare("DELETE FROM encuentros WHERE equipo_local_id = ? OR equipo_visitante_id = ?");
        $stmt->execute([$id, $id]);

        // Luego, elimina el equipo de la base de datos
        $stmt = $pdo->prepare("DELETE FROM equipos WHERE id = ?");
        if ($stmt->execute([$id])) {
            return json_encode(["mensaje" => "Equipo y sus encuentros eliminados con éxito"]);
        } else {
            return json_encode(["error" => "No se pudo eliminar el equipo"]);
        }
    }

    // Método para buscar equipos por nombre
    public static function buscarEquipos($buscar) {
        global $pdo;

        if (!$pdo) {
            return json_encode(["error" => "No hay conexión con la base de datos"]);
        }

        $stmt = $pdo->prepare("SELECT * FROM equipos WHERE nombre LIKE ?");
        $stmt->execute(['%' . $buscar . '%']);
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para contar el total de equipos
    public static function contarEquipos($buscar = '') {
        global $pdo;

        if (!$pdo) {
            return json_encode(["error" => "No hay conexión con la base de datos"]);
        }

        if ($buscar) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipos WHERE nombre LIKE ?");
            $stmt->execute(['%' . $buscar . '%']);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipos");
            $stmt->execute();
        }

        return $stmt->fetchColumn();
    }

    // Método para obtener un equipo por su ID
    public static function obtenerPorId($id) {
        global $pdo;

        if (!$pdo) {
            return json_encode(["error" => "No hay conexión con la base de datos"]);
        }

        $stmt = $pdo->prepare("SELECT * FROM equipos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
