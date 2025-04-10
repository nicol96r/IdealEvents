<?php
// controllers/DashboardController.php
require_once __DIR__ . '/../models/EventoModel.php';
require_once __DIR__ . '/../models/UsuarioModel.php';
require_once __DIR__ . '/../models/InscripcionModel.php';
require_once __DIR__ . '/../models/PagoModel.php';

class DashboardController {

    /**
     * Obtiene todas las estadísticas necesarias para el dashboard administrativo
     * @return array Array asociativo con todas las estadísticas
     */
    public function obtenerEstadisticasDashboard() {
        try {
            // Estadísticas básicas
            $estadisticas = [
                'total_eventos' => EventoModel::mdlContarEventos(),
                'total_usuarios' => count(UsuarioModel::mdlListarUsuarios()),
                'eventos_por_categoria' => EventoModel::mdlContarEventosPorCategoria(),
                'categorias' => EventoModel::mdlObtenerCategorias(),
                'eventos_por_mes' => EventoModel::mdlObtenerEstadisticasPorMes(),
                'proximos_eventos' => EventoModel::mdlObtenerProximosEventos(5),
                'eventos_populares' => $this->obtenerEventosPopulares(),
                'ultimos_usuarios' => $this->obtenerUltimosUsuarios(5),
                'ultimos_pagos' => $this->obtenerUltimosPagos(5),
                'resumen_pagos' => $this->obtenerResumenPagos(),
                'tendencia_ingresos' => $this->obtenerTendenciaIngresos(),
                'actividad_usuarios' => $this->obtenerActividadUsuarios()
            ];

            return $estadisticas;
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas del dashboard: " . $e->getMessage());
            return [
                'error' => true,
                'mensaje' => "Error al obtener estadísticas: " . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene los eventos más populares basados en inscripciones
     * @param int $limite Número de eventos a obtener
     * @return array Eventos más populares con sus estadísticas
     */
    private function obtenerEventosPopulares($limite = 5) {
        try {
            // Obtener eventos
            $eventos = EventoModel::mdlListarEventos();

            // Para cada evento, obtener número de inscripciones
            $eventos_con_inscripciones = [];
            foreach ($eventos as $evento) {
                // Aquí asumimos que existe un método en InscripcionModel que cuenta inscripciones por evento
                // Si no existe, deberíamos crearlo
                $id_evento = $evento['id_evento'];

                // Contar inscripciones
                $inscripciones = $this->contarInscripcionesPorEvento($id_evento);

                // Calcular ingresos (precio * inscripciones pagadas)
                $ingresos = $this->calcularIngresosPorEvento($id_evento, $evento['precio']);

                $evento['inscripciones'] = $inscripciones;
                $evento['ingresos'] = $ingresos;

                $eventos_con_inscripciones[] = $evento;
            }

            // Ordenar eventos por número de inscripciones (descendente)
            usort($eventos_con_inscripciones, function($a, $b) {
                return $b['inscripciones'] - $a['inscripciones'];
            });

            // Limitar a la cantidad solicitada
            return array_slice($eventos_con_inscripciones, 0, $limite);
        } catch (Exception $e) {
            error_log("Error al obtener eventos populares: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los últimos usuarios registrados
     * @param int $limite Número de usuarios a obtener
     * @return array Últimos usuarios registrados
     */
    private function obtenerUltimosUsuarios($limite = 5) {
        try {
            // Obtener todos los usuarios
            $usuarios = UsuarioModel::mdlListarUsuarios();

            // Ordenar por fecha de registro (descendente)
            usort($usuarios, function($a, $b) {
                return strtotime($b['fecha_registro']) - strtotime($a['fecha_registro']);
            });

            // Limitar a la cantidad solicitada
            return array_slice($usuarios, 0, $limite);
        } catch (Exception $e) {
            error_log("Error al obtener últimos usuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los últimos pagos realizados
     * @param int $limite Número de pagos a obtener
     * @return array Últimos pagos realizados
     */
    private function obtenerUltimosPagos($limite = 5) {
        try {
            // Obtener todos los pagos
            // Aquí asumo que existe un método en PagoModel que obtiene todos los pagos
            // Si no existe, deberíamos crearlo o modificar uno existente
            $pagos = $this->obtenerTodosLosPagos();

            // Ordenar por fecha de pago (descendente)
            usort($pagos, function($a, $b) {
                return strtotime($b['fecha_pago']) - strtotime($a['fecha_pago']);
            });

            // Limitar a la cantidad solicitada
            return array_slice($pagos, 0, $limite);
        } catch (Exception $e) {
            error_log("Error al obtener últimos pagos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un resumen de los pagos (completados, pendientes, rechazados)
     * @return array Resumen de pagos
     */
    private function obtenerResumenPagos() {
        try {
            // Obtener todos los pagos
            $pagos = $this->obtenerTodosLosPagos();

            // Contar pagos por estado
            $completados = 0;
            $pendientes = 0;
            $rechazados = 0;
            $total_ingresos = 0;

            foreach ($pagos as $pago) {
                if ($pago['estado_pago'] === 'completado') {
                    $completados++;
                    $total_ingresos += $pago['monto'];
                } elseif ($pago['estado_pago'] === 'pendiente') {
                    $pendientes++;
                } elseif ($pago['estado_pago'] === 'rechazado') {
                    $rechazados++;
                }
            }

            return [
                'completados' => $completados,
                'pendientes' => $pendientes,
                'rechazados' => $rechazados,
                'total' => count($pagos),
                'total_ingresos' => $total_ingresos
            ];
        } catch (Exception $e) {
            error_log("Error al obtener resumen de pagos: " . $e->getMessage());
            return [
                'completados' => 0,
                'pendientes' => 0,
                'rechazados' => 0,
                'total' => 0,
                'total_ingresos' => 0
            ];
        }
    }

    /**
     * Obtiene la tendencia de ingresos por mes
     * @return array Tendencia de ingresos
     */
    private function obtenerTendenciaIngresos() {
        try {
            // Obtener todos los pagos
            $pagos = $this->obtenerTodosLosPagos();

            // Agrupar pagos por mes y calcular ingresos
            $ingresos_por_mes = [];

            foreach ($pagos as $pago) {
                if ($pago['estado_pago'] === 'completado') {
                    $fecha_pago = new DateTime($pago['fecha_pago']);
                    $mes = $fecha_pago->format('Y-m');
                    $mes_nombre = $fecha_pago->format('M Y');

                    if (!isset($ingresos_por_mes[$mes])) {
                        $ingresos_por_mes[$mes] = [
                            'mes' => $mes_nombre,
                            'monto' => 0
                        ];
                    }

                    $ingresos_por_mes[$mes]['monto'] += $pago['monto'];
                }
            }

            // Ordenar por mes (ascendente)
            ksort($ingresos_por_mes);

            return array_values($ingresos_por_mes);
        } catch (Exception $e) {
            error_log("Error al obtener tendencia de ingresos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene estadísticas de actividad de usuarios
     * @return array Estadísticas de actividad
     */
    private function obtenerActividadUsuarios() {
        try {
            // Obtener todos los usuarios
            $usuarios = UsuarioModel::mdlListarUsuarios();

            // Fechas de referencia
            $fecha_actual = new DateTime();
            $fecha_un_mes_atras = clone $fecha_actual;
            $fecha_un_mes_atras->modify('-1 month');
            $fecha_una_semana_atras = clone $fecha_actual;
            $fecha_una_semana_atras->modify('-1 week');

            // Contar usuarios según actividad
            $activos_ultimo_mes = 0;
            $inactivos = 0;
            $nuevos_ultima_semana = 0;

            foreach ($usuarios as $usuario) {
                $fecha_registro = new DateTime($usuario['fecha_registro']);

                if ($fecha_registro >= $fecha_una_semana_atras) {
                    $nuevos_ultima_semana++;
                }

                // Aquí asumimos que tenemos un campo "ultima_actividad" en la tabla usuario
                // Si no existe, deberíamos modificar la estructura de la tabla
                // En caso de no tener este dato, podríamos usar la fecha del último pago o inscripción
                if (isset($usuario['ultima_actividad'])) {
                    $fecha_ultima_actividad = new DateTime($usuario['ultima_actividad']);

                    if ($fecha_ultima_actividad >= $fecha_un_mes_atras) {
                        $activos_ultimo_mes++;
                    } else {
                        $inactivos++;
                    }
                } else {
                    // Si no tenemos el dato de última actividad, asumimos que es activo si se registró en el último mes
                    if ($fecha_registro >= $fecha_un_mes_atras) {
                        $activos_ultimo_mes++;
                    } else {
                        $inactivos++;
                    }
                }
            }

            return [
                'activos_ultimo_mes' => $activos_ultimo_mes,
                'inactivos' => $inactivos,
                'nuevos_ultima_semana' => $nuevos_ultima_semana,
                'total' => count($usuarios)
            ];
        } catch (Exception $e) {
            error_log("Error al obtener actividad de usuarios: " . $e->getMessage());
            return [
                'activos_ultimo_mes' => 0,
                'inactivos' => 0,
                'nuevos_ultima_semana' => 0,
                'total' => 0
            ];
        }
    }

    /**
     * Cuenta el número de inscripciones para un evento específico
     * @param int $id_evento ID del evento
     * @return int Número de inscripciones
     */
    private function contarInscripcionesPorEvento($id_evento) {
        try {
            // Conectar a la base de datos
            $conexion = Conexion::conectar();

            // Consultar número de inscripciones
            $stmt = $conexion->prepare("
                SELECT COUNT(*) as total
                FROM inscripcion
                WHERE id_evento = :id_evento
            ");
            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error al contar inscripciones por evento: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcula los ingresos generados por un evento
     * @param int $id_evento ID del evento
     * @param float $precio Precio del evento
     * @return float Total de ingresos
     */
    private function calcularIngresosPorEvento($id_evento, $precio) {
        try {
            // Conectar a la base de datos
            $conexion = Conexion::conectar();

            // Consultar número de pagos completados
            $stmt = $conexion->prepare("
                SELECT COUNT(*) as total
                FROM pago
                WHERE id_evento = :id_evento AND estado_pago = 'completado'
            ");
            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            $stmt->execute();

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $pagos_completados = $resultado['total'];

            // Calcular ingresos
            return $pagos_completados * $precio;
        } catch (PDOException $e) {
            error_log("Error al calcular ingresos por evento: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene todos los pagos con información adicional
     * @return array Todos los pagos
     */
    private function obtenerTodosLosPagos() {
        try {
            // Conectar a la base de datos
            $conexion = Conexion::conectar();

            // Consultar todos los pagos con información de usuario y evento
            $stmt = $conexion->prepare("
                SELECT 
                    p.*,
                    u.nombre as usuario_nombre,
                    u.apellido as usuario_apellido,
                    e.titulo as evento_titulo
                FROM pago p
                JOIN usuario u ON p.id_usuario = u.id_usuario
                JOIN evento e ON p.id_evento = e.id_evento
                ORDER BY p.fecha_pago DESC
            ");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener todos los pagos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene datos para mostrar en el dashboard
     */
    public function mostrarDashboard() {
        // Verificar si ya hay una sesión iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar si el usuario está logueado y es administrador
        if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
            header("Location: /IdealEventsx/views/login.php");
            exit();
        }

        // Obtener estadísticas
        $estadisticas = $this->obtenerEstadisticasDashboard();

        // Incluir la vista del dashboard
        include_once __DIR__ . '/../views/admin/dashboard.php';
    }
}

// Si se llama directamente al controlador
if (basename($_SERVER['PHP_SELF']) === 'DashboardController.php') {
    $controller = new DashboardController();
    $controller->mostrarDashboard();
}