    <?php

    require_once __DIR__ . '/../models/Encuentros.php';

    class EncuentroController {

        public function listarEncuentros() {
            $encuentros = Encuentro::obtenerTodos();
            renderizarVista('encuentros.twig', ['encuentros' => $encuentros]);
        }

        public function obtenerEncuentro($id) {
            $encuentro = Encuentro::obtenerPorId($id);

            header('Content-Type: application/json');

            if ($encuentro) {
                echo json_encode($encuentro);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Encuentro no encontrado"]);
            }
        }

        public function crearEncuentro() {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!isset($data['local']) || !isset($data['visitante']) || !isset($data['fecha']) || !isset($data['hora']) || !isset($data['equipo_local_id']) || !isset($data['equipo_visitante_id'])) {
                http_response_code(400);
                echo json_encode(["error" => "Faltan datos requeridos"]);
                return;
            }

            $resultado = Encuentro::crear($data);
            
            header('Content-Type: application/json');
            echo $resultado;
        }
    }
