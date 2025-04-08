<?php
session_start();

// Definir constantes para rutas
define('BASE_PATH', __DIR__);
define('BASE_URL', '/IdealEventsx/');

// Carga de controladores
require_once BASE_PATH . '/controllers/LoginController.php';
require_once BASE_PATH . '/controllers/EventoController.php';
// Otros controladores según necesites

$action = $_GET['action'] ?? 'index';
$controller = $_GET['controller'] ?? 'Login';

// Verificar si el usuario está logueado para acceder a los dashboards
if (!isset($_SESSION['id_usuario']) && $controller !== 'Login') {
    header("Location: " . BASE_URL . "views/login.php");
    exit();
}

// Creación de instancia del controlador
$controllerClass = $controller . 'Controller';

if (class_exists($controllerClass)) {
    // Inicializar el controlador según sea necesario
    if ($controllerClass === 'LoginController' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $controllerInstance = new LoginController($email, $password);
    } else {
        $controllerInstance = new $controllerClass();
    }

    if (method_exists($controllerInstance, $action)) {
        $controllerInstance->$action();
    } else {
        header("HTTP/1.0 404 Not Found");
        include BASE_PATH . '/views/errors/404.php';
    }
} else {
    header("HTTP/1.0 404 Not Found");
    include BASE_PATH . '/views/errors/404.php';
}