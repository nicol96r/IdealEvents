<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../models/LoginModel.php";
require_once __DIR__ . "/../models/Conexion.php";

class LoginController
{
    private $email;
    private $password;

    public function __construct($email = '', $password = '') {
        $this->email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $this->password = trim($password);
    }

    public function login()
    {
        try {
            if (empty($this->email) || empty($this->password)) {
                throw new Exception("Por favor complete todos los campos");
            }

            $model = new LoginModel();
            $usuario = $model->validarCredenciales($this->email, $this->password);

            if ($usuario) {
                // Establecer sesión
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['rol'] = $usuario['rol'];
                $_SESSION['nombre'] = $usuario['nombre'];

                // Redirección según rol (usando rutas relativas al documento raíz)
                // En tu método login() dentro de LoginController.php
                $redirect = ($usuario['rol'] === 'admin') ? '/IdealEventsx/views/admin/dashboard.php' : '/IdealEventsx/views/cliente/dashboard.php';

                header("Location: $redirect");
                exit();
            } else {
                // Redirigir de vuelta al login con mensaje de error
                $_SESSION['error'] = "Credenciales incorrectas";
                header("Location: /IdealEventsx/views/login.php");
                exit();
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header("Location: /IdealEventsx/views/login.php");
            exit();
        }
    }

    // Método para mostrar página de login
    public function index() {
        header("Location: /IdealEventsx/views/login.php");
        exit();
    }
}

// Este bloque lo dejamos, pero normalmente no se ejecutará si usamos el router en index.php
if (basename($_SERVER['PHP_SELF']) === 'LoginController.php') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['email']) && !empty($_POST['password'])) {
            $loginController = new LoginController($_POST['email'], $_POST['password']);
            $loginController->login();
        } else {
            $_SESSION['error'] = "Por favor complete todos los campos";
            header("Location: /IdealEventsx/views/login.php");
            exit();
        }
    } else {
        header("Location: /IdealEventsx/views/login.php");
        exit();
    }
}