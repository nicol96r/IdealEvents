<?php
// controllers/ReciboController.php
session_start();
require_once __DIR__ . '/../models/PagoModel.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Para cargar TCPDF

use TCPDF as TCPDF;

class ReciboController {

    public function generarRecibo() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            die("Debes iniciar sesión para acceder a esta función.");
        }

        // Verificar que exista el ID del pago
        if (!isset($_GET['id_pago']) || empty($_GET['id_pago'])) {
            die("ID de pago no válido.");
        }

        $id_pago = intval($_GET['id_pago']);
        $id_usuario = $_SESSION['id_usuario'];

        // Obtener datos del pago
        $pago = PagoModel::mdlObtenerPagoPorId($id_pago);

        if (!$pago) {
            die("El pago no existe o no tienes permisos para acceder a él.");
        }

        // Verificar que el pago pertenezca al usuario logueado
        if ($pago['id_usuario'] != $id_usuario) {
            die("No tienes permisos para acceder a este recibo.");
        }

        // Solo generar recibo para pagos completados
        if ($pago['estado_pago'] !== 'completado') {
            die("Solo puedes descargar recibos de pagos completados.");
        }

        // Crear nuevo documento PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configurar documento
        $pdf->SetCreator('Ideal Events');
        $pdf->SetAuthor('Ideal Events');
        $pdf->SetTitle('Recibo de Pago - Ideal Events');
        $pdf->SetSubject('Recibo de Pago');
        $pdf->SetKeywords('Recibo, Pago, Eventos, Factura');

        // Eliminar cabecera y pie de página predeterminados
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Establecer márgenes
        $pdf->SetMargins(15, 15, 15);

        // Agregar página
        $pdf->AddPage();

        // Logo y cabecera
        $pdf->Image(__DIR__ . '/../public/logo/logo.png', 15, 10, 30, 0, 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Cell(0, 20, 'RECIBO DE PAGO', 0, 1, 'R');

        // Información del recibo
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Información del Recibo', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        // Crear tabla de información del recibo
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(40, 7, 'Nº de Recibo:', 0, 0, 'L', 1);
        $pdf->Cell(60, 7, '#' . $pago['id_pago'], 0, 0, 'L');
        $pdf->Cell(40, 7, 'Fecha de Pago:', 0, 0, 'L', 1);
        $pdf->Cell(50, 7, date('d/m/Y H:i', strtotime($pago['fecha_pago'])), 0, 1, 'L');

        $pdf->Cell(40, 7, 'Estado:', 0, 0, 'L', 1);
        $pdf->Cell(60, 7, 'PAGADO', 0, 0, 'L');
        $pdf->Cell(40, 7, 'Método de Pago:', 0, 0, 'L', 1);
        $pdf->Cell(50, 7, 'Tarjeta de Crédito', 0, 1, 'L');

        // Información del cliente
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Información del Cliente', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        $pdf->Cell(40, 7, 'Nombre:', 0, 0, 'L', 1);
        $pdf->Cell(150, 7, $pago['usuario_nombre'] . ' ' . $pago['usuario_apellido'], 0, 1, 'L');

        $pdf->Cell(40, 7, 'Documento:', 0, 0, 'L', 1);
        $pdf->Cell(150, 7, $pago['usuario_documento'], 0, 1, 'L');

        $pdf->Cell(40, 7, 'Email:', 0, 0, 'L', 1);
        $pdf->Cell(150, 7, $pago['usuario_email'], 0, 1, 'L');

        // Información del evento
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Información del Evento', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        $pdf->Cell(40, 7, 'Evento:', 0, 0, 'L', 1);
        $pdf->Cell(150, 7, $pago['evento_titulo'], 0, 1, 'L');

        $pdf->Cell(40, 7, 'Fecha:', 0, 0, 'L', 1);
        $pdf->Cell(60, 7, date('d/m/Y', strtotime($pago['evento_fecha'])), 0, 0, 'L');
        $pdf->Cell(40, 7, 'Hora:', 0, 0, 'L', 1);
        $pdf->Cell(50, 7, $pago['evento_hora'], 0, 1, 'L');

        $pdf->Cell(40, 7, 'Ubicación:', 0, 0, 'L', 1);
        $pdf->Cell(150, 7, $pago['evento_ubicacion'], 0, 1, 'L');

        $pdf->Cell(40, 7, 'Categoría:', 0, 0, 'L', 1);
        $pdf->Cell(150, 7, $pago['evento_categoria'], 0, 1, 'L');

        // Detalle del pago
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Detalle del Pago', 0, 1, 'L');

        // Encabezado de tabla
        $pdf->SetFillColor(41, 128, 185); // Color azul
        $pdf->SetTextColor(255, 255, 255); // Texto blanco
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(100, 8, 'Descripción', 1, 0, 'C', 1);
        $pdf->Cell(30, 8, 'Cantidad', 1, 0, 'C', 1);
        $pdf->Cell(30, 8, 'Precio', 1, 0, 'C', 1);
        $pdf->Cell(30, 8, 'Total', 1, 1, 'C', 1);

        // Restablecer color de texto
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);

        // Fila de item
        $pdf->Cell(100, 7, 'Entrada para: ' . $pago['evento_titulo'], 1, 0, 'L');
        $pdf->Cell(30, 7, '1', 1, 0, 'C');
        $pdf->Cell(30, 7, '$' . number_format($pago['monto'], 2), 1, 0, 'R');
        $pdf->Cell(30, 7, '$' . number_format($pago['monto'], 2), 1, 1, 'R');

        // Total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(160, 8, 'TOTAL', 1, 0, 'R');
        $pdf->Cell(30, 8, '$' . number_format($pago['monto'], 2), 1, 1, 'R');

        // Nota de pie
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 9);
        $pdf->MultiCell(0, 5, 'Este recibo es una confirmación de pago. Por favor, preséntelo (digital o impreso) el día del evento.', 0, 'L');

        $pdf->Ln(5);
        $pdf->MultiCell(0, 5, 'Para cualquier consulta, contáctenos en: soporte@idealevents.com', 0, 'L');

        // Pie con información y fecha de emisión
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 5, 'Ideal Events - Todos los derechos reservados © ' . date('Y'), 0, 1, 'C');
        $pdf->Cell(0, 5, 'Documento generado el ' . date('d/m/Y H:i:s'), 0, 1, 'C');

        // Salida del PDF
        $nombreArchivo = 'Recibo_Ideal_Events_' . $id_pago . '.pdf';
        $pdf->Output($nombreArchivo, 'I'); // 'I' para visualizar en navegador
        exit;
    }
}

// Si este archivo se accede directamente
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new ReciboController();
    $controller->generarRecibo();
}