<?php

require_once __DIR__ . '/../models/Jugadores.php';
require_once __DIR__ . '/../config/view.php';
require_once __DIR__ . '/../config/database.php';

class JugadorController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ✅ Listar jugadores (Web - HTML)
    public function listarJugadoresWeb() {
        $jugadores = Jugador::obtenerTodos();
        renderizarVista('jugadores.twig', ['jugadores' => $jugadores]);
    }

    // ✅ Listar jugadores (API - JSON)
    public function listarJugadoresApi() {
        header('Content-Type: application/json');
        try {
            $jugadores = Jugador::obtenerTodos();
            echo json_encode(["jugadores" => $jugadores], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error interno del servidor"]);
        }
    }

    // ✅ Obtener un jugador (API - JSON)
    public function obtenerJugadorApi($id) {
        header('Content-Type: application/json');
        $jugador = Jugador::obtenerPorId($id);

        if ($jugador) {
            echo json_encode($jugador);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Jugador no encontrado"]);
        }
    }

    // ✅ Crear un jugador (API - JSON)
    public function crearJugadorApi() {
        verificarToken(); // Solo usuarios autenticados pueden crear jugadores
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['nombre']) || !isset($data['posicion']) || !isset($data['equipo_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos requeridos"]);
            return;
        }

        $resultado = Jugador::crear($data);
        echo json_encode(["mensaje" => "Jugador creado con éxito", "id" => $resultado]);
    }
}
