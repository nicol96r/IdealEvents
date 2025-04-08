<?php
session_start();
require_once "../models/InscripcionModel.php";
require_once "../models/EventoModel.php";

class InscripcionController {

    /**
     * Inscribir usuario a un evento
     */
    public function inscribir() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/views/login.php?error=" . urlencode("Debes iniciar sesión para inscribirte"));
            exit();
        }

        // Verificar que se haya recibido el ID del evento
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: /IdealEventsx/views/cliente/dashboard.php?error=" . urlencode("ID de evento no válido"));
            exit();
        }

        $id_evento = intval($_GET['id']);
        $id_usuario = $_SESSION['id_usuario'];

        // Verificar que el evento exista
        $evento = EventoModel::mdlObtenerEventoPorId($id_evento);
        if (!$evento) {
            header("Location: /IdealEventsx/views/cliente/dashboard.php?error=" . urlencode("El evento no existe"));
            exit();
        }

        // Inscribir al usuario
        $resultado = InscripcionModel::mdlInscribirEvento($id_usuario, $id_evento);

        if ($resultado['exito']) {
            // Redireccionar al detalle del evento o a la página de pago
            header("Location: /IdealEventsx/views/cliente/realizar_pago.php?id=" . $resultado['id_inscripcion'] . "&success=" . urlencode($resultado['mensaje']));
        } else {
            header("Location: /IdealEventsx/views/cliente/ver_evento.php?id=" . $id_evento . "&error=" . urlencode($resultado['mensaje']));
        }
        exit();
    }

    /**
     * Cancelar inscripción a un evento
     */
    public function cancelar() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/views/login.php?error=" . urlencode("Debes iniciar sesión para cancelar tu inscripción"));
            exit();
        }

        // Verificar que se haya recibido el ID de la inscripción
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: /IdealEventsx/views/cliente/dashboard.php?error=" . urlencode("ID de inscripción no válido"));
            exit();
        }

        $id_inscripcion = intval($_GET['id']);
        $id_usuario = $_SESSION['id_usuario'];

        // Cancelar la inscripción
        $resultado = InscripcionModel::mdlCancelarInscripcion($id_inscripcion, $id_usuario);

        if ($resultado['exito']) {
            header("Location: /IdealEventsx/views/cliente/dashboard.php?success=" . urlencode($resultado['mensaje']));
        } else {
            header("Location: /IdealEventsx/views/cliente/dashboard.php?error=" . urlencode($resultado['mensaje']));
        }
        exit();
    }

    /**
     * Listar todas las inscripciones del usuario
     */
    public function listar() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/views/login.php?error=" . urlencode("Debes iniciar sesión para ver tus inscripciones"));
            exit();
        }

        $id_usuario = $_SESSION['id_usuario'];

        // Obtener inscripciones
        $inscripciones = InscripcionModel::mdlObtenerInscripcionesPorUsuario($id_usuario);

        // Cargar la vista
        require_once "../views/cliente/mis_inscripciones.php";
    }
}

// Procesar la solicitud si se llama directamente al controlador
if (basename($_SERVER['PHP_SELF']) === 'InscripcionController.php') {
    $controller = new InscripcionController();

    $action = isset($_GET['action']) ? $_GET['action'] : '';

    switch ($action) {
        case 'inscribir':
            $controller->inscribir();
            break;
        case 'cancelar':
            $controller->cancelar();
            break;
        case 'listar':
            $controller->listar();
            break;
        default:
            header("Location: /IdealEventsx/views/cliente/dashboard.php");
            exit();
    }
}
?>