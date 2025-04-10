<?php
// controllers/NewsletterController.php
session_start();
require_once __DIR__ . '/../models/Conexion.php';

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Asegúrate que estas rutas coincidan con tu instalación de Composer
require __DIR__ . '/../vendor/autoload.php';

class NewsletterController {

    // Configuración del servidor SMTP
    private $smtpHost = 'smtp.gmail.com';         // Cambia a tu servidor SMTP
    private $smtpUsername = 'eventsideal4@gmail.com'; // Cambia a tu correo
    private $smtpPassword = 'kjii fgul afsr pygw';   // Cambia a tu contraseña de aplicación
    private $smtpPort = 587;                       // Puerto SMTP (587 para TLS, 465 para SSL)
    private $smtpSecure = 'tls';                   // 'tls' o 'ssl'
    private $emailFrom = 'eventsideal4@gmail.com';    // Remitente
    private $nameFrom = 'Ideal Events';            // Nombre del remitente

    public function suscribir() {
        // Verificar si se recibió el correo electrónico
        if (!isset($_POST['email']) || empty($_POST['email'])) {
            $this->responderJSON(['status' => 'error', 'message' => 'Por favor, introduce un correo electrónico válido']);
            return;
        }

        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        // Validar formato de correo electrónico
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responderJSON(['status' => 'error', 'message' => 'El formato del correo electrónico no es válido']);
            return;
        }

        // Guardar la suscripción en un log (opcional)
        $logFile = __DIR__ . '/../logs/newsletter_subscriptions.log';
        $logDir = dirname($logFile);

        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "$timestamp - Suscripción: $email\n", FILE_APPEND);

        // Enviar correo de bienvenida
        try {
            $enviado = $this->enviarCorreoBienvenida($email);
            if ($enviado) {
                $this->responderJSON(['status' => 'success', 'message' => '¡Gracias por suscribirte a nuestro boletín!']);
            } else {
                $this->responderJSON(['status' => 'error', 'message' => 'Hubo un problema al enviar el correo. Por favor, inténtalo de nuevo más tarde.']);
            }
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $e->getMessage());
            $this->responderJSON(['status' => 'error', 'message' => 'Error al enviar correo: ' . $e->getMessage()]);
        }
    }

    private function enviarCorreoBienvenida($email) {
        // Crear una nueva instancia de PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor
            $mail->isSMTP();
            $mail->Host       = $this->smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->smtpUsername;
            $mail->Password   = $this->smtpPassword;
            $mail->SMTPSecure = $this->smtpSecure;
            $mail->Port       = $this->smtpPort;
            $mail->CharSet    = 'UTF-8';

            // Opción para debugging (descomenta para ver logs detallados)
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;

            // Remitentes y destinatarios
            $mail->setFrom($this->emailFrom, $this->nameFrom);
            $mail->addAddress($email);
            $mail->addReplyTo($this->emailFrom, $this->nameFrom);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = "Bienvenido al boletín de Ideal Events";

            // Cuerpo del mensaje en HTML
            $mail->Body = '
            <html>
            <head>
                <title>Bienvenido a Ideal Events</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #4e73df; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { font-size: 12px; text-align: center; color: #666; padding: 10px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>¡Bienvenido a Ideal Events!</h1>
                    </div>
                    <div class="content">
                        <p>Hola,</p>
                        <p>Gracias por suscribirte a nuestro boletín. Te mantendremos informado sobre los mejores eventos disponibles.</p>
                        <p><strong>Bienvenido a Ideal Events, el lugar donde encontrarás los mejores eventos.</strong></p>
                        <p>Saludos,<br>El equipo de Ideal Events</p>
                    </div>
                    <div class="footer">
                        <p>© ' . date('Y') . ' Ideal Events. Todos los derechos reservados.</p>
                        <p>Si no deseas recibir más correos, puedes <a href="#">darte de baja</a>.</p>
                    </div>
                </div>
            </body>
            </html>
            ';

            // Versión de texto plano (opcional)
            $mail->AltBody = "Bienvenido a Ideal Events\n\nGracias por suscribirte a nuestro boletín. Te mantendremos informado sobre los mejores eventos disponibles.\n\nBienvenido a Ideal Events, el lugar donde encontrarás los mejores eventos.\n\nSaludos,\nEl equipo de Ideal Events";

            // Enviar el correo
            return $mail->send();
        } catch (Exception $e) {
            error_log("Error PHPMailer: {$mail->ErrorInfo}");
            throw new Exception("Error al enviar correo: {$mail->ErrorInfo}");
        }
    }

    private function responderJSON($datos) {
        header('Content-Type: application/json');
        echo json_encode($datos);
        exit();
    }
}

// Si este archivo se accede directamente, procesar la solicitud
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new NewsletterController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->suscribir();
    } else {
        header("Location: ../views/cliente/dashboard.php");
        exit();
    }
}