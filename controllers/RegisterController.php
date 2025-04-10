<?php
session_start();
require_once __DIR__ . "/../models/RegisterModel.php";

class RegisterController
{
    public function ctrRegister($datos)
    {
        // Depuración - Guardar los datos recibidos en un log
        $logFile = __DIR__ . '/register_debug.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - POST data: " . print_r($datos, true) . "\n", FILE_APPEND);

        // Validar que existan todos los campos requeridos
        $camposRequeridos = ['tipo_documento', 'documento', 'nombre', 'apellido',
            'fecha_nacimiento', 'genero', 'email', 'password'];

        foreach ($camposRequeridos as $campo) {
            if (!isset($datos[$campo]) || empty($datos[$campo])) {
                $errorMsg = "Falta el campo: $campo";
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - ERROR: $errorMsg\n", FILE_APPEND);

                $_SESSION['error'] = $errorMsg;
                header("Location: /IdealEventsx/login.php");
                exit();
            }
        }

        // Validar email
        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "El formato del correo electrónico no es válido";
            header("Location: /IdealEventsx/login.php");
            exit();
        }

        // Validar contraseña (mínimo 6 caracteres)
        if (strlen($datos['password']) < 6) {
            $_SESSION['error'] = "La contraseña debe tener al menos 6 caracteres";
            header("Location: /IdealEventsx/login.php");
            exit();
        }

        try {
            $respuesta = RegisterModel::mdlRegister(
                $datos['tipo_documento'],
                $datos['documento'],
                $datos['nombre'],
                $datos['apellido'],
                $datos['fecha_nacimiento'],
                $datos['genero'],
                $datos['email'],
                $datos['password']
            );

            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Respuesta modelo: " . print_r($respuesta, true) . "\n", FILE_APPEND);

            if ($respuesta["codigo"] === 200) {
                $_SESSION['success'] = $respuesta["mensaje"];
                header("Location: /IdealEventsx/login.php?msg=" . urlencode($respuesta["mensaje"]) . "&status=success");
            } else {
                $_SESSION['error'] = $respuesta["mensaje"];
                header("Location: /IdealEventsx/login.php");
            }
        } catch (Exception $e) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            $_SESSION['error'] = "Error del sistema: " . $e->getMessage();
            header("Location: /IdealEventsx/login.php");
        }
        exit();
    }
}

// Verificar si se envió el formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new RegisterController();
    $controller->ctrRegister($_POST);
} else {
    $_SESSION['error'] = "Método de solicitud no válido";
    header("Location: /IdealEventsx/login.php");
    exit();
}
?>