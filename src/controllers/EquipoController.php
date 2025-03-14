<?php

require_once __DIR__ . '/../models/Equipo.php';
require_once __DIR__ . '/../config/view.php';
require_once __DIR__ . '/../config/database.php';

class EquipoController {

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function listarEquiposApi() {
        header("Content-Type: application/json");
        try {
            echo json_encode(["equipos" => $this->obtenerEquiposDesdeDB()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error interno del servidor"]);
        }
        exit;
    }

    private function obtenerEquiposDesdeDB() {
        try {
            $stmt = $this->pdo->query("SELECT id, nombre FROM equipos");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(["error" => "Error al obtener los equipos: " . $e->getMessage()]));
        }
    }

    // ✅ Obtener detalles de un equipo (Web)
    public function obtenerEquipo($id) {
        $equipo = Equipo::obtenerPorId($id);
        $encuentros = Encuentro::obtenerPorEquipoId($id);
        $jugadores = Jugador::obtenerJugadoresPorEquipo($id);

        if ($equipo) {
            renderizarVista('detalleEquipo.twig', [
                'equipo' => $equipo,
                'encuentros' => $encuentros,
                'jugadores' => $jugadores
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Equipo no encontrado"]);
        }
    }

    // ✅ Obtener un equipo (API)
    public function obtenerEquipoApi($id) {
        header('Content-Type: application/json');
        $equipo = $this->obtenerEquipoDesdeDB($id);
    
        if ($equipo) {
            echo json_encode($equipo, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Equipo no encontrado"]);
        }
    }

    private function obtenerEquipoDesdeDB($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, nombre FROM equipos WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error al obtener el equipo: " . $e->getMessage()]);
            return null;
        }
    }

    // ✅ Crear un equipo (Web)
    public function crearEquipo() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;

            try {
                Equipo::crear($data);
                header("Location: /equipos"); 
                exit;
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(["error" => "No se pudo crear el equipo"]);
            }
        } else {
            renderizarVista('crearEquipo.twig');
        }
    }

    // ✅ Crear un equipo (API)
    public function crearEquipoApi() {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['nombre']) || empty($data['estadio']) || empty($data['pais'])) {
            http_response_code(400);
            echo json_encode(["error" => "Todos los campos son obligatorios"]);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("INSERT INTO equipos (nombre, estadio, pais, url_imagen) VALUES (?, ?, ?, ?)");
            $stmt->execute([$data['nombre'], $data['estadio'], $data['pais'], $data['url_imagen'] ?? null]);

            http_response_code(201);
            echo json_encode(["mensaje" => "Equipo creado con éxito", "equipo" => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo crear el equipo: " . $e->getMessage()]);
        }
    }

    // ✅ Editar un equipo (Web)
    public function editarEquipo($id) {
        $equipo = Equipo::obtenerPorId($id);
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;

            if (Equipo::actualizar($id, $data)) {
                $_SESSION['mensaje'] = "Cambios guardados con éxito";
                header("Location: /equipos/{$id}");
                exit;
            } else {
                $_SESSION['mensaje_error'] = "Error al actualizar equipo.";
                header("Location: /equipos/{$id}/editar");
                exit;
            }
        }

        if ($equipo) {
            renderizarVista('editarEquipo.twig', ['equipo' => $equipo]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Equipo no encontrado"]);
        }
    }

    // ✅ Editar un equipo (API)
    public function editarEquipoApi($id) {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['nombre']) || empty($data['estadio']) || empty($data['pais'])) {
            http_response_code(400);
            echo json_encode(["error" => "Todos los campos son obligatorios"]);
            return;
        }

        if (Equipo::actualizar($id, $data)) {
            echo json_encode(["mensaje" => "Equipo actualizado con éxito"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo actualizar el equipo"]);
        }
    }

    // ✅ Eliminar un equipo (Web)
    public function eliminarEquipo($id) {
        if (!isset($_SESSION['usuario'])) {
            header("Location: /usuarios/login");
            exit;
        }

        $equipo = Equipo::obtenerPorId($id);
        if (!$equipo) {
            http_response_code(404);
            echo json_encode(["error" => "Equipo no encontrado"]);
            exit;
        }

        if (Equipo::eliminar($id)) {
            $_SESSION['mensaje'] = "Equipo eliminado correctamente.";
            header("Location: /equipos");
            exit;
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo eliminar el equipo"]);
        }
    }

    // ✅ Eliminar un equipo (API)
    public function eliminarEquipoApi($id) {
        header('Content-Type: application/json');

        $equipo = Equipo::obtenerPorId($id);
        if (!$equipo) {
            http_response_code(404);
            echo json_encode(["error" => "Equipo no encontrado"]);
            return;
        }

        if (Equipo::eliminar($id)) {
            echo json_encode(["mensaje" => "Equipo eliminado con éxito"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo eliminar el equipo"]);
        }
    }

    public function listarEquiposWeb() {
        global $twig;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $equipos = $this->obtenerEquiposDesdeDB();
        
        echo $twig->render('equipos.twig', [
            'equipos' => $equipos,
            'usuario' => $_SESSION['usuario'] ?? null
        ]);
    }

    public function mostrarFormularioCrearEquipo() {
        renderizarVista('crearEquipo.twig');  
    }
    
    public function obtenerEquipoParaEditarApi($id) {
        header('Content-Type: application/json');
    
        // Obtener el equipo por ID
        $equipo = $this->obtenerEquipoDesdeDB($id);
    
        if ($equipo) {
            echo json_encode(["equipo" => $equipo], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Equipo no encontrado"]);
        }
    }
}
