<?php
include_once __DIR__ . "/Conexion.php";

class UsuarioModel
{
    /**
     * Obtiene todos los usuarios ordenados por ID
     * @return array
     */
    public static function mdlListarUsuarios() {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT * FROM usuario
                ORDER BY id_usuario DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al listar usuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca usuarios según criterios
     * @param string $busqueda Término de búsqueda
     * @param string $rol Rol específico (admin/cliente)
     * @return array
     */
    public static function mdlBuscarUsuarios($busqueda = '', $rol = '') {
        try {
            $conexion = Conexion::conectar();

            $sql = "SELECT * FROM usuario WHERE 1=1";
            $params = [];

            // Agregar condición de búsqueda
            if (!empty($busqueda)) {
                $sql .= " AND (nombre LIKE :busqueda OR apellido LIKE :busqueda OR email LIKE :busqueda OR documento LIKE :busqueda)";
                $params[':busqueda'] = '%' . $busqueda . '%';
            }

            // Filtrar por rol
            if (!empty($rol)) {
                $sql .= " AND rol = :rol";
                $params[':rol'] = $rol;
            }

            // Ordenar por ID descendente
            $sql .= " ORDER BY id_usuario DESC";

            $stmt = $conexion->prepare($sql);

            // Vincular parámetros
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar usuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un usuario por su ID
     * @param int $id ID del usuario
     * @return array|null
     */
    public static function mdlObtenerUsuarioPorId($id) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT * FROM usuario
                WHERE id_usuario = :id
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuario: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo usuario
     * @param array $datos Datos del usuario
     * @return array Resultado de la operación
     */
    public static function mdlCrearUsuario($datos) {
        try {
            $conexion = Conexion::conectar();

            // Verificar si el email o documento ya existe
            $stmt = $conexion->prepare("
                SELECT id_usuario FROM usuario 
                WHERE email = :email OR documento = :documento
            ");
            $stmt->bindParam(":email", $datos['email']);
            $stmt->bindParam(":documento", $datos['documento']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return [
                    "exito" => false,
                    "mensaje" => "El email o número de documento ya está registrado en el sistema"
                ];
            }

            // Crear el usuario
            $stmt = $conexion->prepare("
                INSERT INTO usuario (
                    tipo_documento, 
                    documento, 
                    nombre, 
                    apellido, 
                    fecha_nacimiento, 
                    genero, 
                    email, 
                    password, 
                    rol
                ) VALUES (
                    :tipo_documento, 
                    :documento, 
                    :nombre, 
                    :apellido, 
                    :fecha_nacimiento, 
                    :genero, 
                    :email, 
                    :password, 
                    :rol
                )
            ");

            // Encriptar contraseña
            $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);

            $stmt->bindParam(":tipo_documento", $datos['tipo_documento']);
            $stmt->bindParam(":documento", $datos['documento']);
            $stmt->bindParam(":nombre", $datos['nombre']);
            $stmt->bindParam(":apellido", $datos['apellido']);
            $stmt->bindParam(":fecha_nacimiento", $datos['fecha_nacimiento']);
            $stmt->bindParam(":genero", $datos['genero']);
            $stmt->bindParam(":email", $datos['email']);
            $stmt->bindParam(":password", $passwordHash);
            $stmt->bindParam(":rol", $datos['rol']);

            if ($stmt->execute()) {
                return [
                    "exito" => true,
                    "mensaje" => "Usuario creado correctamente",
                    "id" => $conexion->lastInsertId()
                ];
            } else {
                return [
                    "exito" => false,
                    "mensaje" => "Error al crear el usuario"
                ];
            }
        } catch (PDOException $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return [
                "exito" => false,
                "mensaje" => "Error en el sistema: " . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza un usuario existente
     * @param int $id_usuario ID del usuario a actualizar
     * @param array $datos Datos a actualizar
     * @param bool $actualizar_password Indica si se debe actualizar la contraseña
     * @return array Resultado de la operación
     */
    public static function mdlActualizarUsuario($id_usuario, $datos, $actualizar_password = false) {
        try {
            $conexion = Conexion::conectar();

            // Verificar si el email o documento ya está en uso por otro usuario
            $stmt = $conexion->prepare("
                SELECT id_usuario FROM usuario 
                WHERE (email = :email OR documento = :documento) AND id_usuario != :id_usuario
            ");
            $stmt->bindParam(":email", $datos['email']);
            $stmt->bindParam(":documento", $datos['documento']);
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return [
                    "exito" => false,
                    "mensaje" => "El email o número de documento ya está en uso por otro usuario"
                ];
            }

            // Construir la consulta de actualización
            $sql = "
                UPDATE usuario SET 
                    tipo_documento = :tipo_documento, 
                    documento = :documento, 
                    nombre = :nombre, 
                    apellido = :apellido, 
                    fecha_nacimiento = :fecha_nacimiento, 
                    genero = :genero, 
                    email = :email, 
                    rol = :rol
            ";

            // Si se debe actualizar la contraseña, agregarla a la consulta
            if ($actualizar_password && isset($datos['password'])) {
                $sql .= ", password = :password";
            }

            $sql .= " WHERE id_usuario = :id_usuario";

            $stmt = $conexion->prepare($sql);

            $stmt->bindParam(":tipo_documento", $datos['tipo_documento']);
            $stmt->bindParam(":documento", $datos['documento']);
            $stmt->bindParam(":nombre", $datos['nombre']);
            $stmt->bindParam(":apellido", $datos['apellido']);
            $stmt->bindParam(":fecha_nacimiento", $datos['fecha_nacimiento']);
            $stmt->bindParam(":genero", $datos['genero']);
            $stmt->bindParam(":email", $datos['email']);
            $stmt->bindParam(":rol", $datos['rol']);
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);

            // Si se debe actualizar la contraseña, vincular el parámetro
            if ($actualizar_password && isset($datos['password'])) {
                $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
                $stmt->bindParam(":password", $passwordHash);
            }

            if ($stmt->execute()) {
                return [
                    "exito" => true,
                    "mensaje" => "Usuario actualizado correctamente"
                ];
            } else {
                return [
                    "exito" => false,
                    "mensaje" => "Error al actualizar el usuario"
                ];
            }
        } catch (PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return [
                "exito" => false,
                "mensaje" => "Error en el sistema: " . $e->getMessage()
            ];
        }
    }

    /**
     * Cambia el rol de un usuario
     * @param int $id_usuario ID del usuario
     * @param string $nuevo_rol Nuevo rol (admin/cliente)
     * @return array Resultado de la operación
     */
    public static function mdlCambiarRol($id_usuario, $nuevo_rol) {
        try {
            $conexion = Conexion::conectar();

            // Verificar que el usuario existe
            $usuario = self::mdlObtenerUsuarioPorId($id_usuario);
            if (!$usuario) {
                return [
                    "exito" => false,
                    "mensaje" => "El usuario no existe"
                ];
            }

            // Verificar que el rol sea válido
            if ($nuevo_rol !== 'admin' && $nuevo_rol !== 'cliente') {
                return [
                    "exito" => false,
                    "mensaje" => "El rol especificado no es válido"
                ];
            }

            // Actualizar el rol
            $stmt = $conexion->prepare("
                UPDATE usuario 
                SET rol = :rol 
                WHERE id_usuario = :id_usuario
            ");
            $stmt->bindParam(":rol", $nuevo_rol);
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return [
                    "exito" => true,
                    "mensaje" => "Rol actualizado correctamente"
                ];
            } else {
                return [
                    "exito" => false,
                    "mensaje" => "Error al actualizar el rol"
                ];
            }
        } catch (PDOException $e) {
            error_log("Error al cambiar rol: " . $e->getMessage());
            return [
                "exito" => false,
                "mensaje" => "Error en el sistema: " . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un usuario
     * @param int $id_usuario ID del usuario a eliminar
     * @return array Resultado de la operación
     */
    public static function mdlEliminarUsuario($id_usuario) {
        try {
            $conexion = Conexion::conectar();

            // Iniciar transacción
            $conexion->beginTransaction();

            // Eliminar pagos asociados al usuario
            $stmt = $conexion->prepare("DELETE FROM pago WHERE id_usuario = :id_usuario");
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            // Eliminar inscripciones asociadas al usuario
            $stmt = $conexion->prepare("DELETE FROM inscripcion WHERE id_usuario = :id_usuario");
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            // Opción 1: Eliminar eventos creados por el usuario
            // $stmt = $conexion->prepare("DELETE FROM evento WHERE creado_por = :id_usuario");
            // $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            // $stmt->execute();

            // Opción 2: Transferir eventos a un administrador (id=1)
            $stmt = $conexion->prepare("UPDATE evento SET creado_por = 1 WHERE creado_por = :id_usuario");
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            // Finalmente, eliminar el usuario
            $stmt = $conexion->prepare("DELETE FROM usuario WHERE id_usuario = :id_usuario");
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);

            $resultado = $stmt->execute();

            if ($resultado) {
                $conexion->commit();
                return [
                    "exito" => true,
                    "mensaje" => "Usuario eliminado correctamente"
                ];
            } else {
                $conexion->rollBack();
                return [
                    "exito" => false,
                    "mensaje" => "Error al eliminar el usuario"
                ];
            }
        } catch (PDOException $e) {
            // Revertir los cambios en caso de error
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            error_log("Error al eliminar usuario: " . $e->getMessage());
            return [
                "exito" => false,
                "mensaje" => "Error en el sistema: " . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene las inscripciones de un usuario
     * @param int $id_usuario ID del usuario
     * @return array Inscripciones del usuario
     */
    public static function mdlObtenerInscripcionesUsuario($id_usuario) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT i.*, e.titulo, e.fecha, e.hora, e.ubicacion
                FROM inscripcion i
                JOIN evento e ON i.id_evento = e.id_evento
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

    /**
     * Obtiene los pagos de un usuario
     * @param int $id_usuario ID del usuario
     * @return array Pagos del usuario
     */
    public static function mdlObtenerPagosUsuario($id_usuario) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT p.*, e.titulo
                FROM pago p
                JOIN evento e ON p.id_evento = e.id_evento
                WHERE p.id_usuario = :id_usuario
                ORDER BY p.fecha_pago DESC
            ");
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener pagos del usuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si un usuario existe por email o documento
     * @param string $email Email del usuario
     * @param string $documento Documento del usuario
     * @return bool True si existe, false en caso contrario
     */
    public static function mdlVerificarUsuarioExistente($email, $documento) {
        try {
            $conexion = Conexion::conectar();
            $stmt = $conexion->prepare("
                SELECT id_usuario FROM usuario 
                WHERE email = :email OR documento = :documento
            ");
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":documento", $documento);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar usuario existente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene estadísticas de usuarios
     * @return array Estadísticas de usuarios
     */
    public static function mdlObtenerEstadisticasUsuarios() {
        try {
            $conexion = Conexion::conectar();

            // Total de usuarios
            $stmt = $conexion->query("SELECT COUNT(*) as total FROM usuario");
            $totalUsuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Usuarios por rol
            $stmt = $conexion->query("
                SELECT rol, COUNT(*) as total 
                FROM usuario 
                GROUP BY rol
            ");
            $usuariosPorRol = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Usuarios nuevos por mes (últimos 6 meses)
            $stmt = $conexion->query("
                SELECT 
                    DATE_FORMAT(fecha_registro, '%Y-%m') as mes,
                    COUNT(*) as total
                FROM usuario
                WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(fecha_registro, '%Y-%m')
                ORDER BY mes
            ");
            $usuariosPorMes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'total' => $totalUsuarios,
                'por_rol' => $usuariosPorRol,
                'por_mes' => $usuariosPorMes
            ];
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas de usuarios: " . $e->getMessage());
            return [
                'total' => 0,
                'por_rol' => [],
                'por_mes' => []
            ];
        }
    }
}