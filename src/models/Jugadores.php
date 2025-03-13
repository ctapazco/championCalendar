<?php

require_once __DIR__ . '/../config/database.php';

class Jugador {

    public static function obtenerTodos() {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM jugadores");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerPorId($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM jugadores WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function crear($data) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO jugadores (nombre, edad, dorsal, minutos_jugados, goles, asistencias, tarjetas_amarillas, tarjetas_rojas, equipo_id) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nombre'],
            $data['edad'],
            $data['dorsal'],
            $data['minutos_jugados'] ?? 0,
            $data['goles'] ?? 0,
            $data['asistencias'] ?? 0,
            $data['tarjetas_amarillas'] ?? 0,
            $data['tarjetas_rojas'] ?? 0,
            $data['equipo_id']
        ]);

        return json_encode(["mensaje" => "Jugador creado con éxito", "id" => $pdo->lastInsertId()]);
    }

    public static function obtenerJugadoresPorEquipo($id) {
        global $pdo;
    
        $stmt = $pdo->prepare("SELECT * FROM jugadores WHERE equipo_id = ?");
        $stmt->execute([$id]);
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}