<?php

require_once __DIR__ . '/../models/Encuentros.php';
require_once __DIR__ . '/../config/database.php';

class EncuentroController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ✅ Listar encuentros (Web - HTML)
    public function listarEncuentrosWeb() {
        $encuentros = Encuentro::obtenerTodos();
        renderizarVista('encuentros.twig', ['encuentros' => $encuentros]);
    }

    // ✅ Listar encuentros (API - JSON)
    public function listarEncuentrosApi() {
        header('Content-Type: application/json');
        try {
            $encuentros = Encuentro::obtenerTodos();
            echo json_encode(["encuentros" => $encuentros], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error interno del servidor"]);
        }
    }

    // ✅ Obtener un encuentro (API - JSON)
    public function obtenerEncuentroApi($id) {
        header('Content-Type: application/json');
        $encuentro = Encuentro::obtenerPorId($id);

        if ($encuentro) {
            echo json_encode($encuentro);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Encuentro no encontrado"]);
        }
    }

    // ✅ Crear un encuentro (API - JSON)
    public function crearEncuentroApi() {
        verificarToken();  // Solo usuarios autenticados pueden crear encuentros
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['local']) || !isset($data['visitante']) || !isset($data['fecha']) || !isset($data['hora']) || !isset($data['equipo_local_id']) || !isset($data['equipo_visitante_id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Faltan datos requeridos"]);
            return;
        }

        $resultado = Encuentro::crear($data);
        echo json_encode(["mensaje" => "Encuentro creado con éxito", "id" => $resultado]);
    }
}
