<?php
session_start();
require_once "../models/LoginModel.php";

class UsuarioController {

    /**
     * Actualizar perfil del usuario
     */
    public function actualizarPerfil() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/login.php?error=" . urlencode("Debes iniciar sesión para actualizar tu perfil"));
            exit();
        }

        // Verificar que se recibieron los datos necesarios
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("Método no permitido"));
            exit();
        }

        $id_usuario = $_SESSION['id_usuario'];

        // Obtener y validar datos del formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $tipo_documento = trim($_POST['tipo_documento'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
        $genero = trim($_POST['genero'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (empty($nombre) || empty($apellido) || empty($tipo_documento) ||
            empty($documento) || empty($fecha_nacimiento) || empty($genero) || empty($email)) {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("Todos los campos son obligatorios"));
            exit();
        }

        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("El formato del correo electrónico no es válido"));
            exit();
        }

        // Actualizar datos en la base de datos
        $datosUsuario = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'tipo_documento' => $tipo_documento,
            'documento' => $documento,
            'fecha_nacimiento' => $fecha_nacimiento,
            'genero' => $genero,
            'email' => $email
        ];

        $resultado = LoginModel::mdlActualizarUsuario($id_usuario, $datosUsuario);

        if ($resultado) {
            // Actualizar datos en la sesión
            $_SESSION['nombre'] = $nombre;
            $_SESSION['email'] = $email;

            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?success=" . urlencode("Perfil actualizado correctamente"));
        } else {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("Error al actualizar el perfil"));
        }
        exit();
    }

    /**
     * Cambiar contraseña del usuario
     */
    public function cambiarPassword() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/login.php?error=" . urlencode("Debes iniciar sesión para cambiar tu contraseña"));
            exit();
        }

        // Verificar que se recibieron los datos necesarios
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("Método no permitido"));
            exit();
        }

        $id_usuario = $_SESSION['id_usuario'];

        // Obtener y validar datos del formulario
        $password_actual = trim($_POST['password_actual'] ?? '');
        $password_nueva = trim($_POST['password_nueva'] ?? '');
        $password_confirmar = trim($_POST['password_confirmar'] ?? '');

        if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("Todos los campos son obligatorios"));
            exit();
        }

        // Verificar que las contraseñas coincidan
        if ($password_nueva !== $password_confirmar) {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("Las contraseñas no coinciden"));
            exit();
        }

        // Verificar contraseña actual
        $usuario = LoginModel::mdlObtenerUsuarioPorId($id_usuario);

        if (!$usuario || !password_verify($password_actual, $usuario['password'])) {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("La contraseña actual es incorrecta"));
            exit();
        }

        // Actualizar contraseña
        $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
        $resultado = LoginModel::mdlActualizarPassword($id_usuario, $password_hash);

        if ($resultado) {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?success=" . urlencode("Contraseña actualizada correctamente"));
        } else {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("Error al actualizar la contraseña"));
        }
        exit();
    }

    /**
     * Guardar preferencias del usuario
     */
    public function guardarPreferencias() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/login.php?error=" . urlencode("Debes iniciar sesión para guardar tus preferencias"));
            exit();
        }

        // Verificar que se recibieron los datos necesarios
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("Método no permitido"));
            exit();
        }

        $id_usuario = $_SESSION['id_usuario'];

        // Obtener datos del formulario
        $notif_email = isset($_POST['notif_email']) ? 1 : 0;
        $notif_nuevos = isset($_POST['notif_nuevos']) ? 1 : 0;
        $notif_recordatorios = isset($_POST['notif_recordatorios']) ? 1 : 0;
        $categorias = $_POST['categorias'] ?? [];

        // Para este ejemplo, solo mostraremos un mensaje de éxito
        // En un sistema real, guardaríamos estas preferencias en la base de datos

        header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?success=" . urlencode("Preferencias guardadas correctamente"));
        exit();
    }

    /**
     * Eliminar cuenta de usuario
     */
    public function eliminarCuenta() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/login.php?error=" . urlencode("Debes iniciar sesión para eliminar tu cuenta"));
            exit();
        }

        // Verificar que se recibieron los datos necesarios
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("Método no permitido"));
            exit();
        }

        $id_usuario = $_SESSION['id_usuario'];

        // Obtener y validar datos del formulario
        $password = trim($_POST['password'] ?? '');
        $confirmar = isset($_POST['confirmar']) && $_POST['confirmar'] === 'on';

        if (empty($password) || !$confirmar) {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("Debes completar todos los campos para eliminar tu cuenta"));
            exit();
        }

        // Verificar contraseña
        $usuario = LoginModel::mdlObtenerUsuarioPorId($id_usuario);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("La contraseña es incorrecta"));
            exit();
        }

        // Eliminar cuenta
        $resultado = LoginModel::mdlEliminarUsuario($id_usuario);

        if ($resultado) {
            // Cerrar sesión
            session_unset();
            session_destroy();

            header("Location: /IdealEventsx/login.php?msg=" . urlencode("Tu cuenta ha sido eliminada correctamente") . "&status=success");
        } else {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("Error al eliminar la cuenta"));
        }
        exit();
    }

    /**
     * Método principal para manejar las solicitudes
     */
    public function handleRequest() {
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        switch ($action) {
            case 'actualizarPerfil':
                $this->actualizarPerfil();
                break;
            case 'cambiarPassword':
                $this->cambiarPassword();
                break;
            case 'guardarPreferencias':
                $this->guardarPreferencias();
                break;
            case 'eliminarCuenta':
                $this->eliminarCuenta();
                break;
            default:
                header("Location: /IdealEventsx/views/cliente/dashboard.php");
                exit();
        }
    }
}

// Procesar la solicitud si se llama directamente al controlador
if (basename($_SERVER['PHP_SELF']) === 'UsuarioController.php') {
    $controller = new UsuarioController();
    $controller->handleRequest();
}
?>