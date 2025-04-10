<?php
require_once "Conexion.php";

class LoginModel
{
    private $db;

    public function __construct() {
        $this->db = Conexion::obtenerConexion();  // Cambiado a obtenerConexion
    }

    public function validarCredenciales($email, $password)
    {
        try {
            // Removido filtro "AND activo = 1" que no está en la estructura de la tabla
            $stmt = $this->db->prepare("SELECT id_usuario, email, password, rol, nombre FROM usuario WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['password'])) {
                return $usuario;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error en LoginModel: " . $e->getMessage());
            return false;
        }
    }

    public static function mdlObtenerUsuarioPorId($id) {
        try {
            $db = Conexion::obtenerConexion();
            $stmt = $db->prepare("SELECT * FROM usuario WHERE id_usuario = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuario: " . $e->getMessage());
            return false;
        }
    }

    public static function mdlObtenerRolesUsuario($id) {
        try {
            // Como no tenemos una tabla de roles definida, retornamos el rol del usuario
            $db = Conexion::obtenerConexion();
            $stmt = $db->prepare("SELECT rol as nombre FROM usuario WHERE id_usuario = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $rol = $stmt->fetch(PDO::FETCH_ASSOC);
            return $rol ? [$rol] : [];
        } catch (PDOException $e) {
            error_log("Error al obtener roles: " . $e->getMessage());
            return [];
        }
    }
    /**
     * Actualizar datos del usuario
     */
    public static function mdlActualizarUsuario($id_usuario, $datos) {
        try {
            $db = Conexion::obtenerConexion();
            $stmt = $db->prepare("
            UPDATE usuario 
            SET nombre = :nombre, 
                apellido = :apellido, 
                tipo_documento = :tipo_documento, 
                documento = :documento, 
                fecha_nacimiento = :fecha_nacimiento, 
                genero = :genero, 
                email = :email 
            WHERE id_usuario = :id_usuario
        ");

            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':apellido', $datos['apellido']);
            $stmt->bindParam(':tipo_documento', $datos['tipo_documento']);
            $stmt->bindParam(':documento', $datos['documento']);
            $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
            $stmt->bindParam(':genero', $datos['genero']);
            $stmt->bindParam(':email', $datos['email']);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar contraseña del usuario
     */
    public static function mdlActualizarPassword($id_usuario, $password_hash) {
        try {
            $db = Conexion::obtenerConexion();
            $stmt = $db->prepare("
            UPDATE usuario 
            SET password = :password 
            WHERE id_usuario = :id_usuario
        ");

            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar contraseña: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar cuenta de usuario
     */
    public static function mdlEliminarUsuario($id_usuario) {
        try {
            $db = Conexion::obtenerConexion();

            // Iniciar transacción
            $db->beginTransaction();

            // Eliminar pagos
            $stmt = $db->prepare("DELETE FROM pago WHERE id_usuario = :id_usuario");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            // Eliminar inscripciones
            $stmt = $db->prepare("DELETE FROM inscripcion WHERE id_usuario = :id_usuario");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            // Eliminar usuario
            $stmt = $db->prepare("DELETE FROM usuario WHERE id_usuario = :id_usuario");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            // Confirmar transacción
            $db->commit();

            return true;
        } catch (PDOException $e) {
            // Revertir cambios en caso de error
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            error_log("Error al eliminar usuario: " . $e->getMessage());
            return false;
        }
    }
}