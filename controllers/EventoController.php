<?php
// Use absolute path with __DIR__ to ensure reliability
require_once __DIR__ . '/../models/EventoModel.php';
require_once __DIR__ . '/../models/ImageHandler.php';

class EventoController {

    // Handle different actions based on GET parameter
    public function index() {
        // Check if there's an action parameter
        $action = isset($_GET['action']) ? $_GET['action'] : 'listar';
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        // Verificar si ya hay una sesión iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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
            case 'obtener_datos':
                $this->obtenerDatos($id);
                break;
            default:
                $this->listar();
                break;
        }
    }

    // Helper method to check if the user has admin permissions
    private function checkAdminPermissions() {
        // Verificar si ya hay una sesión iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            $_SESSION['error'] = "No tienes permisos para realizar esta acción";

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                // Si es una petición AJAX, retornar error en JSON
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'No tienes permisos para realizar esta acción']);
                exit();
            } else {
                // Redirigir normalmente
                header("Location: /IdealEventsx/views/cliente/dashboard.php");
                exit();
            }
        }
    }

    // Método para listar todos los eventos
    public function listar() {
        // Verificar si ya hay una sesión iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $eventos = EventoModel::mdlListarEventos();

        // Redirect back to the view page with data in session if needed
        $_SESSION['eventos'] = $eventos;

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // Si es una petición AJAX, retornar como JSON
            header('Content-Type: application/json');
            echo json_encode($eventos);
            exit();
        } else {
            // Si el usuario es admin
            if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
                header("Location: /IdealEventsx/views/admin/eventos.php");
            } else {
                header("Location: /IdealEventsx/views/cliente/ver_evento.php");
            }
            exit();
        }
    }

    // Método para mostrar un evento específico
    public function ver($id) {
        // Verificar si ya hay una sesión iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $evento = EventoModel::mdlObtenerEventoPorId($id);

        if (!$evento) {
            $_SESSION['error'] = "Evento no encontrado";

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                // Si es una petición AJAX, retornar error en JSON
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Evento no encontrado']);
                exit();
            } else {
                // Redirigir según rol
                if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
                    header("Location: /IdealEventsx/views/admin/eventos.php");
                } else {
                    header("Location: /IdealEventsx/views/cliente/ver_evento.php");
                }
                exit();
            }
        }

        // Store event in session and return to view
        $_SESSION['evento_detalle'] = $evento;

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // Si es una petición AJAX, retornar como JSON
            header('Content-Type: application/json');
            echo json_encode($evento);
            exit();
        } else {
            header("Location: /IdealEventsx/views/cliente/detalle_evento.php?id=" . $id);
            exit();
        }
    }

    // Método para obtener datos de un evento (para AJAX)
    public function obtenerDatos($id) {
        $evento = EventoModel::mdlObtenerEventoPorId($id);

        header('Content-Type: application/json');
        if ($evento) {
            echo json_encode(['status' => 'success', 'data' => $evento]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Evento no encontrado']);
        }
        exit();
    }

    // Método para guardar un nuevo evento
    public function guardar() {
        // Verificar si ya hay una sesión iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validar datos del formulario
            $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
            $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
            $fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';
            $hora = isset($_POST['hora']) ? trim($_POST['hora']) : '';
            $ubicacion = isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : '';
            $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
            $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0.00;
            $destacado = isset($_POST['destacado']) ? 1 : 0;

            // Verificar si se seleccionó una nueva categoría
            if ($categoria === 'nueva' && isset($_POST['nuevaCategoria']) && !empty($_POST['nuevaCategoria'])) {
                $categoria = trim($_POST['nuevaCategoria']);
            }

            // Validaciones básicas
            if (empty($titulo) || empty($descripcion) || empty($fecha) || empty($hora) || empty($ubicacion) || empty($categoria)) {
                $_SESSION['error'] = "Todos los campos marcados con * son obligatorios";

                $this->redirectBack();
                return;
            }

            // Obtener el ID del usuario que crea el evento (administrador actual)
            $creado_por = $_SESSION['id_usuario'];

            // Handle image upload if provided
            $imagen = null;
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                try {
                    $imageHandler = new ImageHandler();
                    $resultadoImagen = $imageHandler->uploadImage($_FILES['imagen'], 'eventos');
                    $imagen = $resultadoImagen['ruta']; // Aquí guardamos la ruta relativa
                } catch (Exception $e) {
                    $_SESSION['error'] = "Error al subir la imagen: " . $e->getMessage();
                    $this->redirectBack();
                    return;
                }
            }

            // Guardar en la base de datos - usamos try/catch para capturar errores
            try {
                $resultado = EventoModel::mdlAgregarEvento($titulo, $descripcion, $fecha, $hora, $ubicacion, $categoria, $precio, $imagen, $creado_por, $destacado);

                if ($resultado) {
                    $_SESSION['success'] = "Evento creado correctamente";
                } else {
                    $_SESSION['error'] = "Error al crear el evento";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Error al crear el evento: " . $e->getMessage();
            }

            $this->redirectBack();
        } else {
            $_SESSION['error'] = "Método de solicitud no válido";
            $this->redirectBack();
        }
    }

    // Método para actualizar un evento
    public function actualizar() {
        // Verificar si ya hay una sesión iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_evento = isset($_POST['id_evento']) ? intval($_POST['id_evento']) : 0;
            $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
            $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
            $fecha = isset($_POST['fecha']) ? trim($_POST['fecha']) : '';
            $hora = isset($_POST['hora']) ? trim($_POST['hora']) : '';
            $ubicacion = isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : '';
            $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
            $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : 0.00;
            $destacado = isset($_POST['destacado']) ? 1 : 0;

            // Verificar si se seleccionó una nueva categoría
            if ($categoria === 'nueva' && isset($_POST['nuevaCategoria']) && !empty($_POST['nuevaCategoria'])) {
                $categoria = trim($_POST['nuevaCategoria']);
            }

            // Validaciones
            if (empty($titulo) || empty($descripcion) || empty($fecha) || empty($hora) || empty($ubicacion) || empty($categoria)) {
                $_SESSION['error'] = "Todos los campos marcados con * son obligatorios";
                $this->redirectBack();
                return;
            }

            // Verificar que el evento exista
            $evento = EventoModel::mdlObtenerEventoPorId($id_evento);
            if (!$evento) {
                $_SESSION['error'] = "El evento que intentas editar no existe";
                $this->redirectBack();
                return;
            }

            // Handle image upload if provided
            $imagen = null;
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                try {
                    $imageHandler = new ImageHandler();
                    $resultadoImagen = $imageHandler->uploadImage($_FILES['imagen'], 'eventos');

                    // Usar la ruta relativa que se devuelve (eventos/año/mes/nombre.jpg)
                    $imagen = $resultadoImagen['ruta'];

                    // Verificar y mostrar información para depuración
                    error_log("Imagen subida: " . $imagen);
                } catch (Exception $e) {
                    $_SESSION['error'] = "Error al subir la imagen: " . $e->getMessage();
                    $this->redirectBack();
                    return;
                }
            }

            try {
                $resultado = EventoModel::mdlEditarEvento($id_evento, $titulo, $descripcion, $fecha, $hora, $ubicacion, $categoria, $precio, $imagen, $destacado);

                if ($resultado) {
                    $_SESSION['success'] = "Evento actualizado correctamente";
                } else {
                    $_SESSION['error'] = "Error al actualizar el evento";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Error al actualizar el evento: " . $e->getMessage();
            }

            $this->redirectBack();
        } else {
            $_SESSION['error'] = "Método de solicitud no válido";
            $this->redirectBack();
        }
    }

    // Método para eliminar un evento
    // Método para eliminar un evento - versión modificada que no verifica inscripciones/pagos
    public function eliminar($id) {
        // Verificar si ya hay una sesión iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($id <= 0) {
            $_SESSION['error'] = "ID de evento inválido";
            $this->redirectBack();
            return;
        }

        // Verificar que el evento exista
        $evento = EventoModel::mdlObtenerEventoPorId($id);
        if (!$evento) {
            $_SESSION['error'] = "El evento que intentas eliminar no existe";
            $this->redirectBack();
            return;
        }

        /* COMENTAMOS ESTA VERIFICACIÓN PARA PERMITIR ELIMINAR EVENTOS CON INSCRIPCIONES/PAGOS
        // Comprobar si hay inscripciones o pagos asociados
        $tieneInscripciones = EventoModel::mdlTieneInscripciones($id);
        $tienePagos = EventoModel::mdlTienePagos($id);

        if ($tieneInscripciones || $tienePagos) {
            $_SESSION['error'] = "No se puede eliminar el evento porque hay inscripciones o pagos asociados";
            $this->redirectBack();
            return;
        }
        */

        // Eliminar la imagen asociada si existe
        if (!empty($evento['imagen_nombre'])) {
            $rutaImagen = __DIR__ . '/../public/img/eventos/' . $evento['imagen_nombre'];
            if (file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }
        }

        try {
            // Primero eliminamos las inscripciones asociadas al evento
            $inscripcionesBorradas = EventoModel::mdlEliminarInscripcionesPorEvento($id);

            // Luego eliminamos los pagos asociados al evento
            $pagosBorrados = EventoModel::mdlEliminarPagosPorEvento($id);

            // Finalmente eliminamos el evento
            $resultado = EventoModel::mdlEliminarEvento($id);

            if ($resultado) {
                $_SESSION['success'] = "Evento eliminado correctamente";
            } else {
                $_SESSION['error'] = "Error al eliminar el evento";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error al eliminar el evento: " . $e->getMessage();
        }

        $this->redirectBack();
    }

    // Helper method to redirect back to the appropriate page
    private function redirectBack() {
        // Verificar si ya hay una sesión iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // Si es una petición AJAX, retornar respuesta JSON
            header('Content-Type: application/json');
            if (isset($_SESSION['error'])) {
                echo json_encode(['status' => 'error', 'message' => $_SESSION['error']]);
                unset($_SESSION['error']);
            } else if (isset($_SESSION['success'])) {
                echo json_encode(['status' => 'success', 'message' => $_SESSION['success']]);
                unset($_SESSION['success']);
            } else {
                echo json_encode(['status' => 'redirect']);
            }
            exit();
        } else {
            // Si el usuario es admin
            if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
                header("Location: /IdealEventsx/views/admin/eventos.php");
            } else {
                header("Location: /IdealEventsx/views/cliente/ver_evento.php");
            }
            exit();
        }
    }
}

// Automatically handle the request if the file is accessed directly
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new EventoController();
    $controller->index();
}