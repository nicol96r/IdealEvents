<?php
// Use absolute path with __DIR__ to ensure reliability
require_once __DIR__ . '/../models/EventoModel.php';

class EventoController {

    // Handle different actions based on GET parameter
    public function index() {
        // Check if there's an action parameter
        $action = isset($_GET['action']) ? $_GET['action'] : 'listar';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        switch ($action) {
            case 'ver':
                $this->ver($id);
                break;
            case 'guardar':
                // Check permissions before allowing this action
                $this->checkAdminPermissions();
                $this->guardar();
                break;
            case 'actualizar':
                // Check permissions before allowing this action
                $this->checkAdminPermissions();
                $this->actualizar();
                break;
            case 'eliminar':
                // Check permissions before allowing this action
                $this->checkAdminPermissions();
                $this->eliminar($id);
                break;
            default:
                $this->listar();
                break;
        }
    }

    // Helper method to check if the user has admin permissions
    private function checkAdminPermissions() {
        session_start();
        $rol_usuario = isset($_SESSION['rol_usuario']) ? $_SESSION['rol_usuario'] : 'cliente';

        if ($rol_usuario !== 'admin' && $rol_usuario !== 'organizador') {
            $_SESSION['error'] = "No tienes permisos para realizar esta acción";
            header("Location: ../views/cliente/ver_evento.php");
            exit();
        }
    }

    // Método para listar todos los eventos
    public function listar() {
        $eventos = EventoModel::mdlListarEventos();

        // Redirect back to the view page with data in session if needed
        $_SESSION['eventos'] = $eventos;
        header("Location: ../views/cliente/ver_evento.php");
        exit();
    }

    // Método para mostrar un evento específico
    public function ver($id) {
        $evento = EventoModel::mdlObtenerEventoPorId($id);

        if (!$evento) {
            $_SESSION['error'] = "Evento no encontrado";
            header("Location: ../views/cliente/ver_evento.php");
            exit();
        }

        // Store event in session and return to view
        $_SESSION['evento_detalle'] = $evento;
        header("Location: ../views/cliente/detalle_evento.php?id=" . $id);
        exit();
    }

    // Método para guardar un nuevo evento
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar datos del formulario
            $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
            $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
            $fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';
            $hora = isset($_POST['hora']) ? trim($_POST['hora']) : '';
            $ubicacion = isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : '';
            $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
            $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0.00;

            // Validaciones básicas
            if (empty($titulo) || empty($descripcion) || empty($fecha)) {
                $_SESSION['error'] = "Los campos título, descripción y fecha son obligatorios";
                header("Location: ../views/cliente/ver_evento.php");
                exit();
            }

            // Handle image upload if provided
            $imagen = null;
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $nombreArchivo = time() . '_' . $_FILES['imagen']['name'];
                $rutaDestino = __DIR__ . '/../public/img/eventos/' . $nombreArchivo;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
                    $imagen = $nombreArchivo;
                }
            }

            // Guardar en la base de datos
            $resultado = EventoModel::mdlAgregarEvento($titulo, $descripcion, $fecha, $hora, $ubicacion, $categoria, $precio, $imagen);

            if ($resultado) {
                $_SESSION['success'] = "Evento creado correctamente";
            } else {
                $_SESSION['error'] = "Error al crear el evento";
            }

            header("Location: ../views/cliente/ver_evento.php");
            exit();
        }

        // Si no es POST, redirigir
        header("Location: ../views/cliente/ver_evento.php");
        exit();
    }

    // Método para actualizar un evento
    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_evento = isset($_POST['id_evento']) ? intval($_POST['id_evento']) : 0;
            $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
            $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
            $fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';
            $hora = isset($_POST['hora']) ? trim($_POST['hora']) : '';
            $ubicacion = isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : '';
            $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
            $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0.00;

            // Validaciones
            if (empty($titulo) || empty($descripcion) || empty($fecha)) {
                $_SESSION['error'] = "Los campos título, descripción y fecha son obligatorios";
                header("Location: ../views/cliente/ver_evento.php");
                exit();
            }

            // Handle image upload if provided
            $imagen = null;
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $nombreArchivo = time() . '_' . $_FILES['imagen']['name'];
                $rutaDestino = __DIR__ . '/../public/img/eventos/' . $nombreArchivo;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
                    $imagen = $nombreArchivo;
                }
            }

            $resultado = EventoModel::mdlEditarEvento($id_evento, $titulo, $descripcion, $fecha, $hora, $ubicacion, $categoria, $precio, $imagen);

            if ($resultado) {
                $_SESSION['success'] = "Evento actualizado correctamente";
            } else {
                $_SESSION['error'] = "Error al actualizar el evento";
            }

            header("Location: ../views/cliente/ver_evento.php");
            exit();
        }

        // Si no es POST, redirigir
        header("Location: ../views/cliente/ver_evento.php");
        exit();
    }

    // Método para eliminar un evento
    public function eliminar($id) {
        if ($id <= 0) {
            $_SESSION['error'] = "ID de evento inválido";
            header("Location: ../views/cliente/ver_evento.php");
            exit();
        }

        $resultado = EventoModel::mdlEliminarEvento($id);

        if ($resultado) {
            $_SESSION['success'] = "Evento eliminado correctamente";
        } else {
            $_SESSION['error'] = "Error al eliminar el evento";
        }

        header("Location: ../views/cliente/ver_evento.php");
        exit();
    }
}

// Automatically handle the request if the file is accessed directly
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new EventoController();
    $controller->index();
}