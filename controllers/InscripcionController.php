<?php
// controllers/InscripcionController.php
require_once __DIR__ . '/../models/InscripcionModel.php';

class InscripcionController {

    public function index() {
        // Manejar diferentes acciones basadas en el parámetro GET
        $action = isset($_GET['action']) ? $_GET['action'] : 'listar';

        switch ($action) {
            case 'inscribir':
                $this->inscribir();
                break;
            case 'cancelar':
                $this->cancelar();
                break;
            default:
                // Si no hay acción específica, redirigir
                header("Location: ../views/cliente/dashboard.php");
                exit();
        }
    }

    public function inscribir() {
        // Verificar si la solicitud es AJAX
        $es_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        session_start();

        if (!isset($_SESSION['id_usuario'])) {
            $respuesta = ['status' => 'error', 'message' => 'Debes iniciar sesión para inscribirte'];
            $this->responderJSON($respuesta, $es_ajax);
            return;
        }

        // Obtener el ID del evento
        $id_evento = isset($_POST['id_evento']) ? intval($_POST['id_evento']) : 0;

        if ($id_evento <= 0) {
            $respuesta = ['status' => 'error', 'message' => 'ID de evento inválido'];
            $this->responderJSON($respuesta, $es_ajax);
            return;
        }

        $id_usuario = $_SESSION['id_usuario'];

        // Verificar si ya está inscrito
        if (InscripcionModel::mdlVerificarInscripcion($id_usuario, $id_evento)) {
            $respuesta = ['status' => 'error', 'message' => 'Ya estás inscrito en este evento'];
            $this->responderJSON($respuesta, $es_ajax);
            return;
        }

        // Intentar inscribir al usuario
        $resultado = InscripcionModel::mdlCrearInscripcion($id_usuario, $id_evento);

        if ($resultado) {
            $respuesta = ['status' => 'success', 'message' => '¡Te has inscrito correctamente al evento!'];
        } else {
            $respuesta = ['status' => 'error', 'message' => 'Error al inscribirte en el evento. Intenta nuevamente.'];
        }

        $this->responderJSON($respuesta, $es_ajax);
    }

    public function cancelar() {
        // Verificar si la solicitud es AJAX
        $es_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        session_start();

        if (!isset($_SESSION['id_usuario'])) {
            $respuesta = ['status' => 'error', 'message' => 'Debes iniciar sesión para cancelar tu inscripción'];
            $this->responderJSON($respuesta, $es_ajax);
            return;
        }

        // Obtener el ID del evento o inscripción
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'evento';

        if ($id <= 0) {
            $respuesta = ['status' => 'error', 'message' => 'ID inválido'];
            $this->responderJSON($respuesta, $es_ajax);
            return;
        }

        $id_usuario = $_SESSION['id_usuario'];
        $resultado = false;

        // Cancelar según el tipo (evento o inscripción)
        if ($tipo === 'inscripcion') {
            // Verificar que la inscripción pertenezca al usuario actual
            $inscripcion = InscripcionModel::mdlObtenerInscripcionPorId($id);

            if ($inscripcion && $inscripcion['id_usuario'] == $id_usuario) {
                $resultado = InscripcionModel::mdlEliminarInscripcion($id);
            } else {
                $respuesta = ['status' => 'error', 'message' => 'No tienes permiso para cancelar esta inscripción'];
                $this->responderJSON($respuesta, $es_ajax);
                return;
            }
        } else {
            // Cancelar por id_evento
            $resultado = InscripcionModel::mdlEliminarInscripcionPorEvento($id_usuario, $id);
        }

        if ($resultado) {
            $respuesta = ['status' => 'success', 'message' => 'Inscripción cancelada correctamente'];
        } else {
            $respuesta = ['status' => 'error', 'message' => 'Error al cancelar la inscripción. Intenta nuevamente.'];
        }

        $this->responderJSON($respuesta, $es_ajax);
    }

    // Método auxiliar para responder en formato JSON o redireccionar según corresponda
    private function responderJSON($datos, $es_ajax = false) {
        if ($es_ajax) {
            // Responder con JSON para solicitudes AJAX
            header('Content-Type: application/json');
            echo json_encode($datos);
            exit();
        } else {
            // Redireccionar con parámetros en la URL para solicitudes normales
            $_SESSION[$datos['status']] = $datos['message'];
            header("Location: ../views/cliente/dashboard.php");
            exit();
        }
    }
}

// Ejecutar el controlador si este archivo se accede directamente
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new InscripcionController();
    $controller->index();
}