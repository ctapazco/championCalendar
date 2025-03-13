<?php

require_once __DIR__ . '/../config/database.php';

class Encuentro {

    public static function obtenerTodos() {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM encuentros");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerEncuentrosPorEquipo($id) {
        global $pdo;
    
        $stmt = $pdo->prepare("SELECT * FROM encuentros WHERE equipo_local_id = ? OR equipo_visitante_id = ?");
        $stmt->execute([$id, $id]);
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerEncuentro($id) {
        // Obtener el encuentro con los nombres de los equipos
        $encuentro = Encuentro::obtenerPorEquipoId($id);
    
        if ($encuentro) {
            renderizarVista('detalleEncuentro.twig', [
                'encuentro' => $encuentro
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Encuentro no encontrado"]);
        }
    }


    public static function obtenerPorEquipoId($equipoId) {
        global $pdo;
        
        // Modificar la consulta para obtener los nombres de los equipos locales y visitantes
        $stmt = $pdo->prepare("
            SELECT enc.*, 
                   local.nombre AS equipo_local, 
                   visitante.nombre AS equipo_visitante
            FROM encuentros enc
            LEFT JOIN equipos local ON local.id = enc.equipo_local_id
            LEFT JOIN equipos visitante ON visitante.id = enc.equipo_visitante_id
            WHERE enc.equipo_local_id = :equipoId OR enc.equipo_visitante_id = :equipoId
        ");
        $stmt->execute(['equipoId' => $equipoId]);
        
        return $stmt->fetchAll();
    }



    public static function crear($data) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO encuentros (local, visitante, fecha, hora, resultado_local, resultado_visitante, equipo_local_id, equipo_visitante_id) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['local'],
            $data['visitante'],
            $data['fecha'],
            $data['hora'],
            $data['resultado_local'] ?? null,
            $data['resultado_visitante'] ?? null,
            $data['equipo_local_id'],
            $data['equipo_visitante_id']
        ]);

        return json_encode(["mensaje" => "Encuentro creado con éxito", "id" => $pdo->lastInsertId()]);
    }
}
