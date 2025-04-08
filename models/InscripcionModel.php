<?php
// models/InscripcionModel.php
include_once __DIR__ . "/Conexion.php";

class InscripcionModel {

    // Obtener todas las inscripciones de un usuario
    public static function mdlObtenerInscripcionesPorUsuario($id_usuario) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT i.*, p.estado_pago
                FROM inscripcion i
                LEFT JOIN pago p ON i.id_usuario = p.id_usuario AND i.id_evento = p.id_evento
                WHERE i.id_usuario = :id_usuario
                ORDER BY i.fecha_inscripcion DESC
            ");
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener inscripciones del usuario: " . $e->getMessage());
            return [];
        }
    }

    // Verificar si un usuario ya está inscrito en un evento
    public static function mdlVerificarInscripcion($id_usuario, $id_evento) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT COUNT(*) as total
                FROM inscripcion
                WHERE id_usuario = :id_usuario AND id_evento = :id_evento
            ");
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado['total'] > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar inscripción: " . $e->getMessage());
            return false;
        }
    }

    // Crear una nueva inscripción
    public static function mdlCrearInscripcion($id_usuario, $id_evento) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                INSERT INTO inscripcion (id_usuario, id_evento)
                VALUES (:id_usuario, :id_evento)
            ");
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al crear inscripción: " . $e->getMessage());
            return false;
        }
    }

    // Eliminar una inscripción por su ID
    public static function mdlEliminarInscripcion($id_inscripcion) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                DELETE FROM inscripcion
                WHERE id_inscripcion = :id_inscripcion
            ");
            $stmt->bindParam(":id_inscripcion", $id_inscripcion, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar inscripción: " . $e->getMessage());
            return false;
        }
    }

    // Eliminar inscripción por usuario y evento
    public static function mdlEliminarInscripcionPorEvento($id_usuario, $id_evento) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                DELETE FROM inscripcion
                WHERE id_usuario = :id_usuario AND id_evento = :id_evento
            ");
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(":id_evento", $id_evento, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar inscripción por evento: " . $e->getMessage());
            return false;
        }
    }

    // Obtener una inscripción por su ID
    public static function mdlObtenerInscripcionPorId($id_inscripcion) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT *
                FROM inscripcion
                WHERE id_inscripcion = :id_inscripcion
            ");
            $stmt->bindParam(":id_inscripcion", $id_inscripcion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener inscripción por ID: " . $e->getMessage());
            return null;
        }
    }
}