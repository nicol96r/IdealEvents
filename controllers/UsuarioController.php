<?php
session_start();
require_once __DIR__ . "/../models/UsuarioModel.php";
require_once __DIR__ . "/../models/LoginModel.php";

class UsuarioController {

    /**
     * Método principal para manejar las solicitudes
     */
    public function handleRequest() {
        // Verificar si ya hay una sesión iniciada
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/views/login.php");
            exit();
        }

        $action = isset($_GET['action']) ? $_GET['action'] : '';

        switch ($action) {
            case 'crear':
                $this->crear();
                break;
            case 'actualizar':
                $this->actualizar();
                break;
            case 'eliminar':
                $this->eliminar();
                break;
            case 'cambiarRol':
                $this->cambiarRol();
                break;
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
                header("Location: /IdealEventsx/views/admin/usuarios.php");
                exit();
        }
    }

    /**
     * Crear un nuevo usuario (para admin)
     */
    private function crear() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            $_SESSION['error'] = "No tienes permisos para realizar esta acción";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Verificar que se recibieron los datos necesarios
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Método no permitido";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Obtener y validar datos del formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $tipo_documento = trim($_POST['tipo_documento'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
        $genero = trim($_POST['genero'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmar_password = trim($_POST['confirmar_password'] ?? '');
        $rol = trim($_POST['rol'] ?? 'cliente');

        // Validar campos obligatorios
        if (empty($nombre) || empty($apellido) || empty($tipo_documento) || empty($documento) ||
            empty($fecha_nacimiento) || empty($genero) || empty($email) || empty($password)) {
            $_SESSION['error'] = "Todos los campos son obligatorios";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Validar que las contraseñas coincidan
        if ($password !== $confirmar_password) {
            $_SESSION['error'] = "Las contraseñas no coinciden";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "El formato del correo electrónico no es válido";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Validar longitud de contraseña
        if (strlen($password) < 6) {
            $_SESSION['error'] = "La contraseña debe tener al menos 6 caracteres";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Validar rol
        if ($rol !== 'admin' && $rol !== 'cliente') {
            $_SESSION['error'] = "Rol inválido";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Crear usuario en la base de datos
        $datos = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'tipo_documento' => $tipo_documento,
            'documento' => $documento,
            'fecha_nacimiento' => $fecha_nacimiento,
            'genero' => $genero,
            'email' => $email,
            'password' => $password,
            'rol' => $rol
        ];

        $resultado = UsuarioModel::mdlCrearUsuario($datos);

        if ($resultado['exito']) {
            $_SESSION['success'] = $resultado['mensaje'];
        } else {
            $_SESSION['error'] = $resultado['mensaje'];
        }

        header("Location: /IdealEventsx/views/admin/usuarios.php");
        exit();
    }

    /**
     * Actualizar un usuario existente (para admin)
     */
    private function actualizar() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            $_SESSION['error'] = "No tienes permisos para realizar esta acción";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Verificar que se recibieron los datos necesarios
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Método no permitido";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Obtener ID del usuario a actualizar
        $id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
        if ($id_usuario <= 0) {
            $_SESSION['error'] = "ID de usuario inválido";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Obtener y validar datos del formulario
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $tipo_documento = trim($_POST['tipo_documento'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');
        $genero = trim($_POST['genero'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $rol = trim($_POST['rol'] ?? 'cliente');
        $cambiar_password = isset($_POST['cambiar_password']);
        $password = $cambiar_password ? trim($_POST['password'] ?? '') : '';
        $confirmar_password = $cambiar_password ? trim($_POST['confirmar_password'] ?? '') : '';

        // Validar campos obligatorios
        if (empty($nombre) || empty($apellido) || empty($tipo_documento) || empty($documento) ||
            empty($fecha_nacimiento) || empty($genero) || empty($email)) {
            $_SESSION['error'] = "Todos los campos son obligatorios";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "El formato del correo electrónico no es válido";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Validar rol
        if ($rol !== 'admin' && $rol !== 'cliente') {
            $_SESSION['error'] = "Rol inválido";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Si se va a cambiar la contraseña, validar
        if ($cambiar_password) {
            if (empty($password) || empty($confirmar_password)) {
                $_SESSION['error'] = "Debes proporcionar la nueva contraseña y su confirmación";
                header("Location: /IdealEventsx/views/admin/usuarios.php");
                exit();
            }

            if ($password !== $confirmar_password) {
                $_SESSION['error'] = "Las contraseñas no coinciden";
                header("Location: /IdealEventsx/views/admin/usuarios.php");
                exit();
            }

            if (strlen($password) < 6) {
                $_SESSION['error'] = "La contraseña debe tener al menos 6 caracteres";
                header("Location: /IdealEventsx/views/admin/usuarios.php");
                exit();
            }
        }

        // Actualizar usuario en la base de datos
        $datos = [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'tipo_documento' => $tipo_documento,
            'documento' => $documento,
            'fecha_nacimiento' => $fecha_nacimiento,
            'genero' => $genero,
            'email' => $email,
            'rol' => $rol
        ];

        // Si se va a cambiar la contraseña, agregarla a los datos
        if ($cambiar_password) {
            $datos['password'] = $password;
        }

        $resultado = UsuarioModel::mdlActualizarUsuario($id_usuario, $datos, $cambiar_password);

        if ($resultado['exito']) {
            $_SESSION['success'] = $resultado['mensaje'];
        } else {
            $_SESSION['error'] = $resultado['mensaje'];
        }

        header("Location: /IdealEventsx/views/admin/usuarios.php");
        exit();
    }

    /**
     * Eliminar un usuario (para admin)
     */
    private function eliminar() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            $_SESSION['error'] = "No tienes permisos para realizar esta acción";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Obtener ID del usuario a eliminar
        $id_usuario = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id_usuario <= 0) {
            $_SESSION['error'] = "ID de usuario inválido";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Verificar que no se esté intentando eliminar a sí mismo
        if ($id_usuario === $_SESSION['id_usuario']) {
            $_SESSION['error'] = "No puedes eliminarte a ti mismo";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Eliminar usuario
        $resultado = UsuarioModel::mdlEliminarUsuario($id_usuario);

        if ($resultado['exito']) {
            $_SESSION['success'] = $resultado['mensaje'];
        } else {
            $_SESSION['error'] = $resultado['mensaje'];
        }

        header("Location: /IdealEventsx/views/admin/usuarios.php");
        exit();
    }

    /**
     * Cambiar rol de un usuario (para admin)
     */
    private function cambiarRol() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            $_SESSION['error'] = "No tienes permisos para realizar esta acción";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Verificar que se recibieron los datos necesarios
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Método no permitido";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Obtener datos del formulario
        $id_usuario = isset($_POST['id_usuario']) ? intval($_POST['id_usuario']) : 0;
        $nuevo_rol = trim($_POST['nuevo_rol'] ?? '');

        if ($id_usuario <= 0) {
            $_SESSION['error'] = "ID de usuario inválido";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Verificar que no se esté intentando cambiar su propio rol
        if ($id_usuario === $_SESSION['id_usuario']) {
            $_SESSION['error'] = "No puedes cambiar tu propio rol";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Validar rol
        if ($nuevo_rol !== 'admin' && $nuevo_rol !== 'cliente') {
            $_SESSION['error'] = "Rol inválido";
            header("Location: /IdealEventsx/views/admin/usuarios.php");
            exit();
        }

        // Cambiar rol
        $resultado = UsuarioModel::mdlCambiarRol($id_usuario, $nuevo_rol);

        if ($resultado['exito']) {
            $_SESSION['success'] = $resultado['mensaje'];
        } else {
            $_SESSION['error'] = $resultado['mensaje'];
        }

        header("Location: /IdealEventsx/views/admin/usuarios.php");
        exit();
    }

    /**
     * Actualizar perfil del usuario
     */
    public function actualizarPerfil() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/views/login.php?error=" . urlencode("Debes iniciar sesión para actualizar tu perfil"));
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
            'email' => $email,
            'rol' => $_SESSION['rol'] // Mantener el rol actual
        ];

        $resultado = UsuarioModel::mdlActualizarUsuario($id_usuario, $datosUsuario);

        if ($resultado['exito']) {
            // Actualizar datos en la sesión
            $_SESSION['nombre'] = $nombre;
            $_SESSION['email'] = $email;

            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?success=" . urlencode("Perfil actualizado correctamente"));
        } else {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode($resultado['mensaje']));
        }
        exit();
    }

    /**
     * Cambiar contraseña del usuario
     */
    public function cambiarPassword() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/views/login.php?error=" . urlencode("Debes iniciar sesión para cambiar tu contraseña"));
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
        $usuario = UsuarioModel::mdlObtenerUsuarioPorId($id_usuario);

        if (!$usuario || !password_verify($password_actual, $usuario['password'])) {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("La contraseña actual es incorrecta"));
            exit();
        }

        // Actualizar contraseña
        $datos = [
            'password' => $password_nueva
        ];
        $resultado = UsuarioModel::mdlActualizarUsuario($id_usuario, $datos, true);

        if ($resultado['exito']) {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?success=" . urlencode("Contraseña actualizada correctamente"));
        } else {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode($resultado['mensaje']));
        }
        exit();
    }

    /**
     * Guardar preferencias del usuario
     */
    public function guardarPreferencias() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/views/login.php?error=" . urlencode("Debes iniciar sesión para guardar tus preferencias"));
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
            header("Location: /IdealEventsx/views/login.php?error=" . urlencode("Debes iniciar sesión para eliminar tu cuenta"));
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
        $usuario = UsuarioModel::mdlObtenerUsuarioPorId($id_usuario);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode("La contraseña es incorrecta"));
            exit();
        }

        // Eliminar cuenta
        $resultado = UsuarioModel::mdlEliminarUsuario($id_usuario);

        if ($resultado['exito']) {
            // Cerrar sesión
            session_unset();
            session_destroy();

            header("Location: /IdealEventsx/views/login.php?msg=" . urlencode("Tu cuenta ha sido eliminada correctamente") . "&status=success");
        } else {
            header("Location: /IdealEventsx/views/cliente/perfil_cliente.php?error=" . urlencode($resultado['mensaje']));
        }
        exit();
    }
}

// Procesar la solicitud si se llama directamente al controlador
if (basename($_SERVER['PHP_SELF']) === 'UsuarioController.php') {
    $controller = new UsuarioController();
    $controller->handleRequest();
}
?>