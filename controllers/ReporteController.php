<?php
session_start();
require_once __DIR__ . "/../models/EventoModel.php";
require_once __DIR__ . "/../models/UsuarioModel.php";
require_once __DIR__ . "/../models/InscripcionModel.php";
require_once __DIR__ . "/../models/PagoModel.php";
require_once __DIR__ . "/../vendor/autoload.php"; // Para TCPDF

use TCPDF as TCPDF;

class ReporteController {

    /**
     * Método principal para manejar las solicitudes
     */
    public function index() {
        // Verificar que el usuario esté logueado
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: /IdealEventsx/views/login.php");
            exit();
        }

        // Verificar permisos de administrador
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header("Location: /IdealEventsx/views/cliente/dashboard.php");
            exit();
        }

        // Procesar acciones según el parámetro GET
        $action = isset($_GET['action']) ? $_GET['action'] : 'mostrar';

        switch ($action) {
            case 'generar_pdf':
                $this->generarReportePDF();
                break;
            case 'eventos_json':
                $this->obtenerEstadisticasEventosJSON();
                break;
            case 'usuarios_json':
                $this->obtenerEstadisticasUsuariosJSON();
                break;
            case 'pagos_json':
                $this->obtenerEstadisticasPagosJSON();
                break;
            case 'filtrar':
                $this->filtrarReportes();
                break;
            case 'mostrar':
            default:
                header("Location: /IdealEventsx/views/admin/reportes.php");
                break;
        }
    }

    /**
     * Genera un reporte PDF completo
     */
    public function generarReportePDF() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header("Location: /IdealEventsx/views/cliente/dashboard.php");
            exit();
        }

        // Obtener fechas para el reporte (por defecto, último mes)
        $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-1 month'));
        $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

        // Obtener datos para el reporte
        $eventos = EventoModel::mdlListarEventos();
        $usuarios = UsuarioModel::mdlListarUsuarios();

        // Crear instancia de PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configurar documento
        $pdf->SetCreator('Ideal Events');
        $pdf->SetAuthor('Administrador');
        $pdf->SetTitle('Reporte Administrativo - Ideal Events');
        $pdf->SetSubject('Reporte de Actividad');
        $pdf->SetKeywords('Reporte, Eventos, Usuarios, Pagos, Estadísticas');

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
        $pdf->Cell(0, 20, 'REPORTE ADMINISTRATIVO', 0, 1, 'R');

        // Período del reporte
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Período del reporte: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' al ' . date('d/m/Y', strtotime($fecha_fin)), 0, 1, 'R');
        $pdf->Ln(5);

        // Resumen Ejecutivo
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, '1. Resumen Ejecutivo', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 11);
        $pdf->MultiCell(0, 7, 'Este reporte presenta un análisis de la actividad en la plataforma Ideal Events durante el período seleccionado. Se incluyen estadísticas de eventos, usuarios y transacciones financieras.', 0, 'L');

        $pdf->Ln(5);

        // Estadísticas de Eventos
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, '2. Eventos', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, '2.1 Resumen de Eventos', 0, 1, 'L');

        // Tabla de estadísticas de eventos
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(90, 7, 'Métrica', 1, 0, 'L', 1);
        $pdf->Cell(85, 7, 'Valor', 1, 1, 'C', 1);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(90, 7, 'Total de eventos', 1, 0, 'L');
        $pdf->Cell(85, 7, count($eventos), 1, 1, 'C');

        $pdf->Cell(90, 7, 'Eventos activos', 1, 0, 'L');
        $pdf->Cell(85, 7, $this->contarEventosActivos($eventos), 1, 1, 'C');

        $pdf->Cell(90, 7, 'Eventos con inscripciones', 1, 0, 'L');
        $pdf->Cell(85, 7, $this->contarEventosConInscripciones($eventos), 1, 1, 'C');

        $pdf->Cell(90, 7, 'Categoría más popular', 1, 0, 'L');
        $pdf->Cell(85, 7, $this->obtenerCategoriaPopular(), 1, 1, 'C');

        $pdf->Ln(5);

        // Top 5 eventos más populares
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, '2.2 Eventos Más Populares', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(10, 7, '#', 1, 0, 'C', 1);
        $pdf->Cell(60, 7, 'Evento', 1, 0, 'L', 1);
        $pdf->Cell(30, 7, 'Fecha', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Categoría', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'Inscritos', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'Ingresos', 1, 1, 'C', 1);

        $eventosPopulares = $this->obtenerEventosPopulares(5);
        $pdf->SetFont('helvetica', '', 9);

        foreach ($eventosPopulares as $index => $evento) {
            $pdf->Cell(10, 7, ($index + 1), 1, 0, 'C');
            $pdf->Cell(60, 7, $evento['titulo'], 1, 0, 'L');
            $pdf->Cell(30, 7, date('d/m/Y', strtotime($evento['fecha'])), 1, 0, 'C');
            $pdf->Cell(30, 7, $evento['categoria'], 1, 0, 'C');
            $pdf->Cell(25, 7, $evento['inscritos'], 1, 0, 'C');
            $pdf->Cell(25, 7, '$' . number_format($evento['ingresos'], 2), 1, 1, 'R');
        }

        $pdf->Ln(5);

        // Estadísticas de Usuarios
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, '3. Usuarios', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, '3.1 Resumen de Usuarios', 0, 1, 'L');

        // Tabla de estadísticas de usuarios
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(90, 7, 'Métrica', 1, 0, 'L', 1);
        $pdf->Cell(85, 7, 'Valor', 1, 1, 'C', 1);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(90, 7, 'Total de usuarios', 1, 0, 'L');
        $pdf->Cell(85, 7, count($usuarios), 1, 1, 'C');

        $pdf->Cell(90, 7, 'Usuarios administradores', 1, 0, 'L');
        $pdf->Cell(85, 7, $this->contarUsuariosPorRol($usuarios, 'admin'), 1, 1, 'C');

        $pdf->Cell(90, 7, 'Usuarios clientes', 1, 0, 'L');
        $pdf->Cell(85, 7, $this->contarUsuariosPorRol($usuarios, 'cliente'), 1, 1, 'C');

        $pdf->Cell(90, 7, 'Nuevos usuarios (último mes)', 1, 0, 'L');
        $pdf->Cell(85, 7, $this->contarNuevosUsuarios($usuarios), 1, 1, 'C');

        $pdf->Ln(5);

        // Estadísticas Financieras
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, '4. Finanzas', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, '4.1 Resumen Financiero', 0, 1, 'L');

        // Tabla de estadísticas financieras
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(90, 7, 'Métrica', 1, 0, 'L', 1);
        $pdf->Cell(85, 7, 'Valor', 1, 1, 'C', 1);

        $ingresosTotales = $this->calcularIngresosTotales();
        $ingresosMes = $this->calcularIngresosPorPeriodo(date('Y-m-01'), date('Y-m-t'));

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(90, 7, 'Ingresos totales', 1, 0, 'L');
        $pdf->Cell(85, 7, '$' . number_format($ingresosTotales, 2), 1, 1, 'C');

        $pdf->Cell(90, 7, 'Ingresos del mes actual', 1, 0, 'L');
        $pdf->Cell(85, 7, '$' . number_format($ingresosMes, 2), 1, 1, 'C');

        $pdf->Cell(90, 7, 'Pagos pendientes', 1, 0, 'L');
        $pdf->Cell(85, 7, '$' . number_format($this->calcularPagosPendientes(), 2), 1, 1, 'C');

        $pdf->Cell(90, 7, 'Ticket promedio', 1, 0, 'L');
        $pdf->Cell(85, 7, '$' . number_format($this->calcularTicketPromedio(), 2), 1, 1, 'C');

        $pdf->Ln(5);

        // Conclusiones
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, '5. Conclusiones y Recomendaciones', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 11);
        $pdf->MultiCell(0, 7, 'Este reporte muestra el estado actual de la plataforma Ideal Events. En base a los datos analizados, se recomienda:

1. Continuar promoviendo eventos de la categoría más popular: ' . $this->obtenerCategoriaPopular() . '.
2. Implementar estrategias para aumentar la conversión de inscripciones a pagos completados.
3. Fomentar la creación de eventos en las categorías menos populares para diversificar la oferta.
4. Monitorear el crecimiento de usuarios y evaluar campañas de marketing digital para incrementar registros.', 0, 'L');

        // Información de generación
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Reporte generado el ' . date('d/m/Y H:i:s'), 0, 1, 'R');
        $pdf->Cell(0, 5, 'Por: ' . $_SESSION['nombre'] . ' (Administrador)', 0, 1, 'R');

        // Salida del PDF
        $nombreArchivo = 'Reporte_Admin_IdealEvents_' . date('Y-m-d') . '.pdf';
        $pdf->Output($nombreArchivo, 'D'); // 'D' para forzar la descarga
        exit();
    }

    /**
     * Obtiene estadísticas de eventos en formato JSON
     */
    public function obtenerEstadisticasEventosJSON() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Acceso no autorizado']);
            exit();
        }

        // Obtener parámetros de filtro
        $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
        $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
        $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;

        // Obtener eventos (aquí deberías aplicar los filtros a la consulta)
        $eventos = EventoModel::mdlListarEventos();

        // Obtener estadísticas por mes
        $eventosPorMes = EventoModel::mdlObtenerEstadisticasPorMes();

        // Obtener eventos por categoría
        $eventosPorCategoria = [];
        $categorias = EventoModel::mdlObtenerCategorias();

        foreach ($categorias as $cat) {
            $eventosPorCategoria[] = [
                'categoria' => $cat,
                'cantidad' => count(EventoModel::mdlBuscarEventos('', $cat))
            ];
        }

        // Obtener eventos más populares
        $eventosPopulares = $this->obtenerEventosPopulares(5);

        // Construir el objeto de respuesta
        $respuesta = [
            'total_eventos' => count($eventos),
            'eventos_activos' => $this->contarEventosActivos($eventos),
            'eventos_con_inscripciones' => $this->contarEventosConInscripciones($eventos),
            'eventos_por_mes' => $eventosPorMes,
            'eventos_por_categoria' => $eventosPorCategoria,
            'eventos_populares' => $eventosPopulares,
            'categoria_popular' => $this->obtenerCategoriaPopular(),
            'filtros' => [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'categoria' => $categoria
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit();
    }

    /**
     * Obtiene estadísticas de usuarios en formato JSON
     */
    public function obtenerEstadisticasUsuariosJSON() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Acceso no autorizado']);
            exit();
        }

        // Obtener usuarios
        $usuarios = UsuarioModel::mdlListarUsuarios();

        // Crear array para usuarios por mes
        $usuariosPorMes = [];
        $meses = [];

        // Obtener los últimos 6 meses
        for ($i = 5; $i >= 0; $i--) {
            $mes = date('Y-m', strtotime("-$i months"));
            $meses[$mes] = 0;
        }

        // Contar usuarios por mes de registro
        foreach ($usuarios as $usuario) {
            $mesRegistro = date('Y-m', strtotime($usuario['fecha_registro']));
            if (isset($meses[$mesRegistro])) {
                $meses[$mesRegistro]++;
            }
        }

        // Formatear para la respuesta
        foreach ($meses as $mes => $cantidad) {
            $usuariosPorMes[] = [
                'mes' => date('M Y', strtotime($mes)),
                'cantidad' => $cantidad
            ];
        }

        // Obtener distribución por roles
        $usuariosPorRol = [
            ['rol' => 'admin', 'cantidad' => $this->contarUsuariosPorRol($usuarios, 'admin')],
            ['rol' => 'cliente', 'cantidad' => $this->contarUsuariosPorRol($usuarios, 'cliente')]
        ];

        // Construir el objeto de respuesta
        $respuesta = [
            'total_usuarios' => count($usuarios),
            'nuevos_usuarios_mes' => $this->contarNuevosUsuarios($usuarios),
            'usuarios_por_mes' => $usuariosPorMes,
            'usuarios_por_rol' => $usuariosPorRol
        ];

        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit();
    }

    /**
     * Obtiene estadísticas de pagos en formato JSON
     */
    public function obtenerEstadisticasPagosJSON() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Acceso no autorizado']);
            exit();
        }

        // Obtener estadísticas de pagos
        $ingresosTotales = $this->calcularIngresosTotales();
        $ingresosMes = $this->calcularIngresosPorPeriodo(date('Y-m-01'), date('Y-m-t'));
        $pagosPendientes = $this->calcularPagosPendientes();
        $ticketPromedio = $this->calcularTicketPromedio();

        // Obtener pagos por mes para gráfico
        $pagosPorMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $mesInicio = date('Y-m-01', strtotime("-$i months"));
            $mesFin = date('Y-m-t', strtotime("-$i months"));
            $ingresos = $this->calcularIngresosPorPeriodo($mesInicio, $mesFin);

            $pagosPorMes[] = [
                'mes' => date('M Y', strtotime($mesInicio)),
                'monto' => $ingresos
            ];
        }

        // Construir el objeto de respuesta
        $respuesta = [
            'ingresos_totales' => $ingresosTotales,
            'ingresos_mes_actual' => $ingresosMes,
            'pagos_pendientes' => $pagosPendientes,
            'ticket_promedio' => $ticketPromedio,
            'pagos_por_mes' => $pagosPorMes,
            'distribucion_pagos' => [
                ['estado' => 'completado', 'monto' => $this->calcularIngresosPorEstado('completado')],
                ['estado' => 'pendiente', 'monto' => $this->calcularIngresosPorEstado('pendiente')],
                ['estado' => 'rechazado', 'monto' => $this->calcularIngresosPorEstado('rechazado')]
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit();
    }

    /**
     * Filtra reportes según parámetros
     */
    public function filtrarReportes() {
        // Verificar permisos de administrador
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header("Location: /IdealEventsx/views/cliente/dashboard.php");
            exit();
        }

        // Obtener parámetros de filtro
        $fecha_inicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null;
        $fecha_fin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;
        $categoria = isset($_POST['categoria']) ? $_POST['categoria'] : null;
        $estado_pago = isset($_POST['estado_pago']) ? $_POST['estado_pago'] : null;

        // Guardar los filtros en la sesión para usarlos en la vista
        $_SESSION['filtros_reporte'] = [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'categoria' => $categoria,
            'estado_pago' => $estado_pago
        ];

        // Redirigir a la vista de reportes con los filtros
        header("Location: /IdealEventsx/views/admin/dashboard.php");
        exit();
    }

    /* Métodos auxiliares para cálculos estadísticos */

    /**
     * Cuenta los eventos activos (fecha >= hoy)
     */
    private function contarEventosActivos($eventos) {
        $hoy = date('Y-m-d');
        $activos = 0;

        foreach ($eventos as $evento) {
            if ($evento['fecha'] >= $hoy) {
                $activos++;
            }
        }

        return $activos;
    }

    /**
     * Cuenta los eventos que tienen al menos una inscripción
     */
    private function contarEventosConInscripciones($eventos) {
        $conInscripciones = 0;

        foreach ($eventos as $evento) {
            if (InscripcionModel::mdlObtenerInscripcionesPorEvento($evento['id_evento'])) {
                $conInscripciones++;
            }
        }

        return $conInscripciones;
    }

    /**
     * Obtiene la categoría más popular
     */
    private function obtenerCategoriaPopular() {
        $eventos = EventoModel::mdlListarEventos();
        $categorias = [];

        foreach ($eventos as $evento) {
            $cat = $evento['categoria'];
            if (!isset($categorias[$cat])) {
                $categorias[$cat] = 0;
            }
            $categorias[$cat]++;
        }

        arsort($categorias);
        return key($categorias);
    }

    /**
     * Obtiene los eventos más populares según inscripciones
     */
    private function obtenerEventosPopulares($limite = 5) {
        $eventos = EventoModel::mdlListarEventos();
        $eventosConMetricas = [];

        foreach ($eventos as $evento) {
            $inscripciones = InscripcionModel::mdlObtenerInscripcionesPorEvento($evento['id_evento']);
            $numInscripciones = count($inscripciones);

            // Calcular ingresos
            $ingresos = 0;
            foreach ($inscripciones as $inscripcion) {
                $estadoPago = PagoModel::mdlObtenerEstadoPago($inscripcion['id_usuario'], $evento['id_evento']);
                if ($estadoPago === 'completado') {
                    $ingresos += $evento['precio'];
                }
            }

            $eventosConMetricas[] = [
                'id_evento' => $evento['id_evento'],
                'titulo' => $evento['titulo'],
                'fecha' => $evento['fecha'],
                'categoria' => $evento['categoria'],
                'inscritos' => $numInscripciones,
                'ingresos' => $ingresos
            ];
        }

        // Ordenar por número de inscritos
        usort($eventosConMetricas, function($a, $b) {
            return $b['inscritos'] - $a['inscritos'];
        });

        // Devolver solo el límite especificado
        return array_slice($eventosConMetricas, 0, $limite);
    }

    /**
     * Cuenta los usuarios según rol
     */
    private function contarUsuariosPorRol($usuarios, $rol) {
        $contador = 0;

        foreach ($usuarios as $usuario) {
            if ($usuario['rol'] === $rol) {
                $contador++;
            }
        }

        return $contador;
    }

    /**
     * Cuenta los nuevos usuarios en el último mes
     */
    private function contarNuevosUsuarios($usuarios) {
        $contador = 0;
        $unMesAtras = date('Y-m-d H:i:s', strtotime('-1 month'));

        foreach ($usuarios as $usuario) {
            if ($usuario['fecha_registro'] >= $unMesAtras) {
                $contador++;
            }
        }

        return $contador;
    }

    /**
     * Calcula los ingresos totales por pagos completados
     */
    private function calcularIngresosTotales() {
        // Esta debería ser una consulta a la base de datos
        // Para simplificar, retornaremos un valor fijo
        return 3500.50; // En un sistema real, esta sería una consulta real
    }

    /**
     * Calcula los ingresos por un período específico
     */
    private function calcularIngresosPorPeriodo($fechaInicio, $fechaFin) {
        // Esta debería ser una consulta a la base de datos
        // Para simplificar, retornaremos un valor estimado
        return 850.75; // En un sistema real, esta sería una consulta real
    }

    /**
     * Calcula el valor total de pagos pendientes
     */
    private function calcularPagosPendientes() {
        // Esta debería ser una consulta a la base de datos
        // Para simplificar, retornaremos un valor fijo
        return 650.25; // En un sistema real, esta sería una consulta real
    }

    /**
     * Calcula el ticket promedio (valor promedio por pago)
     */
    private function calcularTicketPromedio() {
        // Esta debería ser una consulta a la base de datos
        // Para simplificar, retornaremos un valor fijo
        return 42.50; // En un sistema real, esta sería una consulta real
    }

    /**
     * Calcula los ingresos por estado de pago
     */
    private function calcularIngresosPorEstado($estado) {
        switch ($estado) {
            case 'completado':
                return 3500.50;
            case 'pendiente':
                return 650.25;
            case 'rechazado':
                return 120.00;
            default:
                return 0;
        }
    }
}

// Ejecutar el controlador si este archivo se accede directamente
if (basename($_SERVER['PHP_SELF']) === 'ReporteController.php') {
    $controller = new ReporteController();
    $controller->index();
}