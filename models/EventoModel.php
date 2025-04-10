<?php
include_once __DIR__ . "/Conexion.php";

class EventoModel
{
    /**
     * Obtiene todos los eventos ordenados por fecha descendente
     * @return array
     */
    public static function mdlListarEventos() {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT e.*, u.nombre as creador_nombre 
                FROM evento e
                LEFT JOIN usuario u ON e.creado_por = u.id_usuario
                ORDER BY e.fecha DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al listar eventos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca y filtra eventos
     * @param string $busqueda Término de búsqueda
     * @param string $categoria Categoría del evento
     * @param string $mes Mes del evento
     * @param string $precio Rango de precios
     * @return array
     */
    public static function mdlBuscarEventos($busqueda = '', $categoria = '', $mes = '', $precio = '') {
        try {
            $conexion = Conexion::conectar();

            $sql = "SELECT * FROM evento WHERE 1=1";
            $params = [];

            // Agregar condición de búsqueda
            if (!empty($busqueda)) {
                $sql .= " AND (titulo LIKE :busqueda OR descripcion LIKE :busqueda OR ubicacion LIKE :busqueda)";
                $params[':busqueda'] = '%' . $busqueda . '%';
            }

            // Filtrar por categoría
            if (!empty($categoria) && $categoria != 'Categoría') {
                $sql .= " AND categoria = :categoria";
                $params[':categoria'] = $categoria;
            }

            // Filtrar por mes
            if (!empty($mes) && $mes != 'Mes') {
                // Convertir nombre de mes a número
                $meses = [
                    'Enero' => '01',
                    'Febrero' => '02',
                    'Marzo' => '03',
                    'Abril' => '04',
                    'Mayo' => '05',
                    'Junio' => '06',
                    'Julio' => '07',
                    'Agosto' => '08',
                    'Septiembre' => '09',
                    'Octubre' => '10',
                    'Noviembre' => '11',
                    'Diciembre' => '12'
                ];

                if (isset($meses[$mes])) {
                    $sql .= " AND MONTH(fecha) = :mes";
                    $params[':mes'] = $meses[$mes];
                }
            }

            // Filtrar por precio
            if (!empty($precio) && $precio != 'Precio') {
                switch ($precio) {
                    case 'Menos de $25':
                        $sql .= " AND precio < 25";
                        break;
                    case '$25 - $50':
                        $sql .= " AND precio >= 25 AND precio <= 50";
                        break;
                    case '$50 - $100':
                        $sql .= " AND precio > 50 AND precio <= 100";
                        break;
                    case 'Más de $100':
                        $sql .= " AND precio > 100";
                        break;
                }
            }

            // Ordenar por fecha
            $sql .= " ORDER BY fecha DESC";

            $stmt = $conexion->prepare($sql);

            // Vincular parámetros
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar eventos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todas las categorías únicas de eventos
     * @return array
     */
    public static function mdlObtenerCategorias() {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("SELECT DISTINCT categoria FROM evento ORDER BY categoria");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un evento por su ID
     * @param int $id ID del evento
     * @return array|null
     */
    public static function mdlObtenerEventoPorId($id) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT e.*, u.nombre as creador_nombre 
                FROM evento e
                LEFT JOIN usuario u ON e.creado_por = u.id_usuario
                WHERE e.id_evento = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener evento: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Agrega un nuevo evento
     * @param string $titulo Título del evento
     * @param string $descripcion Descripción del evento
     * @param string $fecha Fecha del evento
     * @param string $hora Hora del evento
     * @param string $ubicacion Ubicación del evento
     * @param string $categoria Categoría del evento
     * @param float $precio Precio del evento
     * @param string|null $imagen Ruta de la imagen
     * @param int $creado_por ID del usuario que crea el evento
     * @param int $destacado 1 si es destacado, 0 si no
     * @return bool
     */
    public static function mdlAgregarEvento($titulo, $descripcion, $fecha, $hora, $ubicacion, $categoria, $precio, $imagen = null, $creado_por = null, $destacado = 0) {
        try {
            $conexion = Conexion::conectar();

            $sql = "INSERT INTO evento 
                   (titulo, descripcion, fecha, hora, ubicacion, categoria, precio, creado_por";

            // Agregar campos opcionales si están presentes
            if ($imagen !== null) {
                $sql .= ", imagen_nombre";
            }

            if ($destacado !== null) {
                $sql .= ", destacado";
            }

            $sql .= ") VALUES (:titulo, :descripcion, :fecha, :hora, :ubicacion, :categoria, :precio, :creado_por";

            // Agregar valores para campos opcionales
            if ($imagen !== null) {
                $sql .= ", :imagen";
            }

            if ($destacado !== null) {
                $sql .= ", :destacado";
            }

            $sql .= ")";

            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(":titulo", $titulo, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            $stmt->bindParam(":hora", $hora, PDO::PARAM_STR);
            $stmt->bindParam(":ubicacion", $ubicacion, PDO::PARAM_STR);
            $stmt->bindParam(":categoria", $categoria, PDO::PARAM_STR);
            $stmt->bindParam(":precio", $precio, PDO::PARAM_STR);
            $stmt->bindParam(":creado_por", $creado_por, PDO::PARAM_INT);

            // Vincular parámetros opcionales
            if ($imagen !== null) {
                $stmt->bindParam(":imagen", $imagen, PDO::PARAM_STR);
            }

            if ($destacado !== null) {
                $stmt->bindParam(":destacado", $destacado, PDO::PARAM_INT);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al agregar evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Edita un evento existente
     * @param int $id_evento ID del evento
     * @param string $titulo Título del evento
     * @param string $descripcion Descripción del evento
     * @param string $fecha Fecha del evento
     * @param string $hora Hora del evento
     * @param string $ubicacion Ubicación del evento
     * @param string $categoria Categoría del evento
     * @param float $precio Precio del evento
     * @param string|null $imagen Ruta de la imagen
     * @param int $destacado 1 si es destacado, 0 si no
     * @return bool
     */
    public static function mdlEditarEvento($id_evento, $titulo, $descripcion, $fecha, $hora, $ubicacion, $categoria, $precio, $imagen = null, $destacado = null) {
        try {
            $conexion = Conexion::conectar();

            $sql = "UPDATE evento 
                   SET titulo = :titulo, 
                       descripcion = :descripcion, 
                       fecha = :fecha, 
                       hora = :hora, 
                       ubicacion = :ubicacion, 
                       categoria = :categoria, 
                       precio = :precio";

            // Agregar campos opcionales si están presentes
            if ($imagen !== null) {
                $sql .= ", imagen_nombre = :imagen";
            }

            if ($destacado !== null) {
                $sql .= ", destacado = :destacado";
            }

            $sql .= " WHERE id_evento = :id_evento";

            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            $stmt->bindParam(":titulo", $titulo, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            $stmt->bindParam(":hora", $hora, PDO::PARAM_STR);
            $stmt->bindParam(":ubicacion", $ubicacion, PDO::PARAM_STR);
            $stmt->bindParam(":categoria", $categoria, PDO::PARAM_STR);
            $stmt->bindParam(":precio", $precio, PDO::PARAM_STR);

            // Vincular parámetros opcionales
            if ($imagen !== null) {
                $stmt->bindParam(":imagen", $imagen, PDO::PARAM_STR);
            }

            if ($destacado !== null) {
                $stmt->bindParam(":destacado", $destacado, PDO::PARAM_INT);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al editar evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un evento
     * @param int $id_evento ID del evento
     * @return bool
     */
    public static function mdlEliminarEvento($id_evento) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("DELETE FROM evento WHERE id_evento = :id_evento");
            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un evento tiene inscripciones
     * @param int $id_evento ID del evento
     * @return bool
     */
    public static function mdlTieneInscripciones($id_evento) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT COUNT(*) as total FROM inscripcion WHERE id_evento = :id_evento
            ");
            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar inscripciones del evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un evento tiene pagos
     * @param int $id_evento ID del evento
     * @return bool
     */
    public static function mdlTienePagos($id_evento) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT COUNT(*) as total FROM pago WHERE id_evento = :id_evento
            ");
            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar pagos del evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene eventos destacados
     * @param int $limite Número máximo de eventos a devolver
     * @return array
     */
    public static function mdlObtenerEventosDestacados($limite = 4) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT * FROM evento 
                WHERE destacado = 1 AND fecha >= CURDATE()
                ORDER BY fecha ASC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener eventos destacados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene próximos eventos
     * @param int $limite Número máximo de eventos a devolver
     * @return array
     */
    public static function mdlObtenerProximosEventos($limite = 5) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT * FROM evento 
                WHERE fecha >= CURDATE()
                ORDER BY fecha ASC
                LIMIT :limite
            ");
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener próximos eventos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene eventos por categoría
     * @param string $categoria Categoría de los eventos
     * @param int $limite Número máximo de eventos a devolver
     * @return array
     */
    public static function mdlObtenerEventosPorCategoria($categoria, $limite = 8) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT * FROM evento 
                WHERE categoria = :categoria AND fecha >= CURDATE()
                ORDER BY fecha ASC
                LIMIT :limite
            ");
            $stmt->bindParam(":categoria", $categoria, PDO::PARAM_STR);
            $stmt->bindParam(":limite", $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener eventos por categoría: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta el número total de eventos
     * @return int
     */
    public static function mdlContarEventos() {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM evento");
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado['total'];
        } catch (PDOException $e) {
            error_log("Error al contar eventos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Cuenta el número de eventos por categoría
     * @return array
     */
    public static function mdlContarEventosPorCategoria() {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT categoria, COUNT(*) as total
                FROM evento
                GROUP BY categoria
                ORDER BY total DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al contar eventos por categoría: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene estadísticas de eventos por mes
     * @param int $año Año para las estadísticas
     * @return array
     */
    public static function mdlObtenerEstadisticasPorMes($año = null) {
        try {
            $conexion = Conexion::conectar();

            if ($año === null) {
                $año = date('Y');
            }

            $stmt = $conexion->prepare("
                SELECT 
                    MONTH(fecha) as mes,
                    COUNT(*) as total
                FROM evento
                WHERE YEAR(fecha) = :anio
                GROUP BY MONTH(fecha)
                ORDER BY mes
            ");
            $stmt->bindParam(":anio", $año, PDO::PARAM_INT);
            $stmt->execute();

            // Preparar array con todos los meses (incluso los que no tienen eventos)
            $estadisticas = [];
            for ($i = 1; $i <= 12; $i++) {
                $estadisticas[$i] = ['mes' => $i, 'total' => 0];
            }

            // Rellenar con datos reales
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($resultados as $row) {
                $estadisticas[$row['mes']]['total'] = $row['total'];
            }

            return array_values($estadisticas);
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas por mes: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Elimina todas las inscripciones asociadas a un evento
     * @param int $id_evento ID del evento
     * @return bool
     */
    public static function mdlEliminarInscripcionesPorEvento($id_evento) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("DELETE FROM inscripcion WHERE id_evento = :id_evento");
            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar inscripciones del evento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina todos los pagos asociados a un evento
     * @param int $id_evento ID del evento
     * @return bool
     */
    public static function mdlEliminarPagosPorEvento($id_evento) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("DELETE FROM pago WHERE id_evento = :id_evento");
            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar pagos del evento: " . $e->getMessage());
            return false;
        }
    }
}