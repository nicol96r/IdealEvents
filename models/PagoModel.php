<?php
include_once "Conexion.php";

class PagoModel {

    /**
     * Registra un nuevo pago
     */
    public static function mdlRegistrarPago($id_usuario, $id_evento, $monto, $estado = 'pendiente') {
        try {
            $conexion = Conexion::conectar();

            // Verificar si ya existe un pago para esta inscripción
            $stmt = $conexion->prepare("
                SELECT id_pago FROM pago 
                WHERE id_usuario = :id_usuario AND id_evento = :id_evento
            ");

            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Si existe, actualizar el estado
                $pago = $stmt->fetch(PDO::FETCH_ASSOC);
                $id_pago = $pago['id_pago'];

                $stmt = $conexion->prepare("
                    UPDATE pago 
                    SET estado_pago = :estado, monto = :monto, fecha_pago = NOW() 
                    WHERE id_pago = :id_pago
                ");

                $stmt->bindParam(":estado", $estado);
                $stmt->bindParam(":monto", $monto);
                $stmt->bindParam(":id_pago", $id_pago);

                if ($stmt->execute()) {
                    return ["exito" => true, "mensaje" => "Pago actualizado correctamente.", "id_pago" => $id_pago];
                } else {
                    return ["exito" => false, "mensaje" => "Error al actualizar el pago."];
                }
            } else {
                // Si no existe, crear un nuevo pago
                $stmt = $conexion->prepare("
                    INSERT INTO pago (id_usuario, id_evento, monto, estado_pago) 
                    VALUES (:id_usuario, :id_evento, :monto, :estado)
                ");

                $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
                $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
                $stmt->bindParam(":monto", $monto);
                $stmt->bindParam(":estado", $estado);

                if ($stmt->execute()) {
                    return [
                        "exito" => true,
                        "mensaje" => "Pago registrado correctamente.",
                        "id_pago" => $conexion->lastInsertId()
                    ];
                } else {
                    return ["exito" => false, "mensaje" => "Error al registrar el pago."];
                }
            }
        } catch (PDOException $e) {
            error_log("Error al registrar pago: " . $e->getMessage());
            return ["exito" => false, "mensaje" => "Error en el sistema: " . $e->getMessage()];
        }
    }

    /**
     * Obtiene todos los pagos de un usuario
     */
    public static function mdlObtenerPagosPorUsuario($id_usuario) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT p.*, e.titulo as evento_titulo, e.fecha as evento_fecha 
                FROM pago p
                JOIN evento e ON p.id_evento = e.id_evento 
                WHERE p.id_usuario = :id_usuario
                ORDER BY p.fecha_pago DESC
            ");

            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener pagos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el estado de pago de una inscripción
     */
    public static function mdlObtenerEstadoPago($id_usuario, $id_evento) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT estado_pago FROM pago 
                WHERE id_usuario = :id_usuario AND id_evento = :id_evento
            ");

            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $pago = $stmt->fetch(PDO::FETCH_ASSOC);
                return $pago['estado_pago'];
            } else {
                return 'pendiente'; // Si no hay registro, se considera pendiente
            }
        } catch (PDOException $e) {
            error_log("Error al obtener estado de pago: " . $e->getMessage());
            return 'error';
        }
    }
}
?>