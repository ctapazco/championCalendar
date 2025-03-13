<?php

require_once __DIR__ . '/../models/Equipo.php';
require_once __DIR__ . '/../config/view.php';

class EquipoController {

    public function listarEquipos() {
        // Comprobar si hay un término de búsqueda
        $buscar = isset($_GET['buscar']) ? $_GET['buscar'] : '';
        
        if ($buscar) {
            $equipos = Equipo::buscarEquipos($buscar);
        } else {
            $equipos = Equipo::obtenerTodos();
        }
    
        // Pasar la sesión y los equipos a la vista
        renderizarVista('equipos.twig', [
            'equipos' => $equipos,
            'buscar' => $buscar,
            'session' => $_SESSION  // Pasar la sesión a la vista
        ]);
    }

    public function obtenerEquipo($id) {
        // Obtener los detalles del equipo
        $equipo = Equipo::obtenerPorId($id);
    
        // Obtener los encuentros del equipo
        $encuentros = Encuentro::obtenerPorEquipoId($id);
    
        // Obtener los jugadores del equipo
        $jugadores = Jugador::obtenerJugadoresPorEquipo($id);
    
        if ($equipo) {
            // Si el equipo es encontrado, mostrar los detalles junto con los encuentros y jugadores
            renderizarVista('detalleEquipo.twig', [
                'equipo' => $equipo,
                'encuentros' => $encuentros,
                'jugadores' => $jugadores
            ]);
        } else {
            // Si no se encuentra el equipo, mostrar un error 404
            http_response_code(404);
            echo "Equipo no encontrado";
        }
    }

    public function crearEquipo() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recoger los datos del formulario
            $data = $_POST;
            
        
            Equipo::crear($data);
    
            // Redirigir al listado de equipos
            header("Location: /equipos"); 
            exit;
        } else {
            // Si la solicitud es GET, mostrar el formulario
            renderizarVista('crearEquipo.twig');
        }
    }
    
    
    public function editarEquipo($id) {
        $equipo = Equipo::obtenerPorId($id);  // Obtener el equipo por ID
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Si el formulario es enviado, procesar la edición
            $data = $_POST;
            
            // Actualizar el equipo en la base de datos
            if (Equipo::actualizar($id, $data)) {
                // Redirigir a los detalles del equipo con un mensaje de éxito
                $_SESSION['mensaje'] = "Cambios realizados con éxito";
                header("Location: /equipos/{$id}");  // Redirigir a la página de detalles del equipo
                exit;
            } else {
                // Si no se pudo actualizar, mostrar error
                $_SESSION['mensaje_error'] = "Hubo un problema al guardar los cambios.";
                header("Location: /equipos/{$id}/editar");  // Redirigir de vuelta al formulario de edición
                exit;
            }
        }
    
        // Si el equipo existe, mostrar el formulario de edición
        if ($equipo) {
            renderizarVista('editarEquipo.twig', ['equipo' => $equipo]);
        } else {
            // Si no se encuentra el equipo, mostrar un error 404
            http_response_code(404);
            echo "Equipo no encontrado";
        }
    }

    public function eliminarEquipo($id) {
        $equipo = Equipo::obtenerPorId($id);
    
        if ($equipo) {
            Equipo::eliminar($id);  // Llamar al método para eliminar el equipo
            header("Location: /equipos");  // Redirigir a la lista de equipos
            exit;
        } else {
            http_response_code(404);
            echo "Equipo no encontrado";
        }
    }
}
