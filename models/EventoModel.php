<?php
include_once __DIR__ . "/Conexion.php";

class EventoModel
{
    public static function mdlListarEventos() {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("SELECT * FROM evento ORDER BY fecha DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al listar eventos: " . $e->getMessage());
            return [];
        }
    }

    // Método para buscar y filtrar eventos
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

    // Obtener categorías únicas de eventos
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

    public static function mdlObtenerEventoPorId($id) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("SELECT * FROM evento WHERE id_evento = :id");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener evento: " . $e->getMessage());
            return null;
        }
    }

    public static function mdlAgregarEvento($titulo, $descripcion, $fecha, $hora, $ubicacion, $categoria, $precio, $imagen = null) {
        try {
            $conexion = Conexion::conectar();

            // Add image field to the query if an image was provided
            if ($imagen) {
                $stmt = $conexion->prepare("INSERT INTO evento 
                    (titulo, descripcion, fecha, hora, ubicacion, categoria, precio, imagen_nombre) 
                    VALUES (:titulo, :descripcion, :fecha, :hora, :ubicacion, :categoria, :precio, :imagen)");
                $stmt->bindParam(":imagen", $imagen, PDO::PARAM_STR);
            } else {
                $stmt = $conexion->prepare("INSERT INTO evento 
                    (titulo, descripcion, fecha, hora, ubicacion, categoria, precio) 
                    VALUES (:titulo, :descripcion, :fecha, :hora, :ubicacion, :categoria, :precio)");
            }

            $stmt->bindParam(":titulo", $titulo, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            $stmt->bindParam(":hora", $hora, PDO::PARAM_STR);
            $stmt->bindParam(":ubicacion", $ubicacion, PDO::PARAM_STR);
            $stmt->bindParam(":categoria", $categoria, PDO::PARAM_STR);
            $stmt->bindParam(":precio", $precio, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al agregar evento: " . $e->getMessage());
            return false;
        }
    }

    public static function mdlEditarEvento($id_evento, $titulo, $descripcion, $fecha, $hora, $ubicacion, $categoria, $precio, $imagen = null) {
        try {
            $conexion = Conexion::conectar();

            // If an image was provided, update that field as well
            if ($imagen) {
                $stmt = $conexion->prepare("UPDATE evento 
                    SET titulo = :titulo, 
                        descripcion = :descripcion, 
                        fecha = :fecha, 
                        hora = :hora, 
                        ubicacion = :ubicacion, 
                        categoria = :categoria, 
                        precio = :precio,
                        imagen_nombre = :imagen
                    WHERE id_evento = :id_evento");
                $stmt->bindParam(":imagen", $imagen, PDO::PARAM_STR);
            } else {
                $stmt = $conexion->prepare("UPDATE evento 
                    SET titulo = :titulo, 
                        descripcion = :descripcion, 
                        fecha = :fecha, 
                        hora = :hora, 
                        ubicacion = :ubicacion, 
                        categoria = :categoria, 
                        precio = :precio
                    WHERE id_evento = :id_evento");
            }

            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            $stmt->bindParam(":titulo", $titulo, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":fecha", $fecha, PDO::PARAM_STR);
            $stmt->bindParam(":hora", $hora, PDO::PARAM_STR);
            $stmt->bindParam(":ubicacion", $ubicacion, PDO::PARAM_STR);
            $stmt->bindParam(":categoria", $categoria, PDO::PARAM_STR);
            $stmt->bindParam(":precio", $precio, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al editar evento: " . $e->getMessage());
            return false;
        }
    }

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
}