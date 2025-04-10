<?php
session_start();
require_once "../models/PagoModel.php";
require_once "../models/EventoModel.php";
require_once "../models/InscripcionModel.php";

class PagoController {

    /**
     * Procesar el pago de un evento
     */
    public function procesar() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/views/login.php?error=" . urlencode("Debes iniciar sesión para realizar un pago"));
            exit();
        }

        // Verificar que se hayan recibido los datos necesarios
        if (!isset($_POST['id_evento']) || empty($_POST['id_evento']) ||
            !isset($_POST['monto']) || !is_numeric($_POST['monto'])) {
            header("Location: /IdealEventsx/views/cliente/dashboard.php?error=" . urlencode("Datos de pago incompletos o inválidos"));
            exit();
        }

        $id_evento = intval($_POST['id_evento']);
        $id_usuario = $_SESSION['id_usuario'];
        $monto = floatval($_POST['monto']);

        // Verificar que el evento exista
        $evento = EventoModel::mdlObtenerEventoPorId($id_evento);
        if (!$evento) {
            header("Location: /IdealEventsx/views/cliente/dashboard.php?error=" . urlencode("El evento no existe"));
            exit();
        }

        // Verificar que el usuario esté inscrito al evento
        if (!InscripcionModel::mdlVerificarInscripcion($id_usuario, $id_evento)) {
            // Si no está inscrito, inscribirlo automáticamente
            InscripcionModel::mdlCrearInscripcion($id_usuario, $id_evento);
        }

        // Registrar el pago (en un sistema real se integraría con una pasarela de pagos)
        $estado = 'completado'; // En un sistema real, esto dependería de la respuesta de la pasarela de pagos
        $resultado = PagoModel::mdlRegistrarPago($id_usuario, $id_evento, $monto, $estado);

        if ($resultado['exito']) {
            header("Location: /IdealEventsx/views/cliente/mis_pagos.php?success=" . urlencode("Pago procesado correctamente. ¡Tu inscripción ha sido confirmada!"));
        } else {
            header("Location: /IdealEventsx/views/cliente/realizar_pago.php?id=" . $id_evento . "&error=" . urlencode($resultado['mensaje']));
        }
        exit();
    }

    /**
     * Obtener detalles de un pago específico
     */
    public function detalle() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/views/login.php?error=" . urlencode("Debes iniciar sesión para ver los detalles del pago"));
            exit();
        }

        // Verificar que se haya recibido el ID del pago
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: /IdealEventsx/views/cliente/mis_pagos.php?error=" . urlencode("ID de pago no válido"));
            exit();
        }

        $id_pago = intval($_GET['id']);
        $id_usuario = $_SESSION['id_usuario'];

        // Obtener detalles del pago
        $pago = PagoModel::mdlObtenerPagoPorId($id_pago);

        // Verificar que el pago exista y pertenezca al usuario
        if (!$pago || $pago['id_usuario'] != $id_usuario) {
            header("Location: /IdealEventsx/views/cliente/mis_pagos.php?error=" . urlencode("El pago no existe o no tienes permiso para verlo"));
            exit();
        }

        // En un sistema real, cargaríamos una vista con los detalles del pago
        // Por simplicidad, redirigimos a la página de pagos
        header("Location: /IdealEventsx/views/cliente/mis_pagos.php");
        exit();
    }

    /**
     * Cancelar un pago pendiente
     */
    public function cancelar() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/views/login.php?error=" . urlencode("Debes iniciar sesión para cancelar un pago"));
            exit();
        }

        // Verificar que se haya recibido el ID del pago
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: /IdealEventsx/views/cliente/mis_pagos.php?error=" . urlencode("ID de pago no válido"));
            exit();
        }

        $id_pago = intval($_GET['id']);
        $id_usuario = $_SESSION['id_usuario'];

        // En un sistema real, implementaríamos la lógica para cancelar pagos pendientes
        // Por ahora, simplemente redirigimos con un mensaje de éxito simulado
        header("Location: /IdealEventsx/views/cliente/mis_pagos.php?success=" . urlencode("Pago cancelado correctamente"));
        exit();
    }
}

// Procesar la solicitud si se llama directamente al controlador
if (basename($_SERVER['PHP_SELF']) === 'PagoController.php') {
    $controller = new PagoController();

    $action = isset($_GET['action']) ? $_GET['action'] : '';

    switch ($action) {
        case 'procesar':
            $controller->procesar();
            break;
        case 'detalle':
            $controller->detalle();
            break;
        case 'cancelar':
            $controller->cancelar();
            break;
        default:
            header("Location: /IdealEventsx/views/cliente/dashboard.php");
            exit();
    }
}
?>