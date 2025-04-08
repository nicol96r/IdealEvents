<?php
// Iniciar la sesión si no está iniciada
session_start();

// Guardar el rol del usuario antes de destruir la sesión
$wasAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';

// Eliminar todas las variables de sesión
$_SESSION = array();

// Si se está usando un cookie de sesión, eliminar la cookie también
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redirigir a la página de login adecuada
// Usamos una URL absoluta para evitar problemas con rutas relativas
header("Location: /IdealEventsx/views/login.php?msg=" . urlencode("Sesión cerrada correctamente") . "&status=success");
exit();
?>