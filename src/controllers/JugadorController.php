<?php

require_once __DIR__ . '/../models/Jugadores.php';
require_once __DIR__ . '/../config/view.php';

class JugadorController {

    public function listarJugadores() {
        $jugadores = Jugador::obtenerTodos();
        renderizarVista('jugadores.twig', ['jugadores' => $jugadores]);
    }

    public function obtenerJugador($id) {
        $jugador = Jugador::obtenerPorId($id);

        header('Content-Type: application/json');

        if ($jugador) {
            echo json_encode($jugador);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Jugador no encontrado"]);
        }
    }

    public function crearJugador() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            Jugador::crear($data);
        }
        header("Location: /jugadores");
        exit;
    }
}
