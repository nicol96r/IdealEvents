<?php
include_once __DIR__ . "/Conexion.php";

class RegisterModel
{
    public static function mdlRegister($tipo_documento, $documento, $nombre, $apellido, $fecha_nacimiento, $genero, $email, $contrasena)
    {
        try {
            $pdo = Conexion::conectar();

            // Verificar si el usuario ya existe
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuario WHERE email = :email OR documento = :documento");
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":documento", $documento);
            $stmt->execute();

            if ($stmt->fetch()) {
                return ["codigo" => 409, "mensaje" => "El usuario ya existe con este correo o documento."];
            }

            // Encriptar la contraseña
            $hashPassword = password_hash($contrasena, PASSWORD_DEFAULT);

            // Insertar el nuevo usuario
            $stmt = $pdo->prepare("INSERT INTO usuario (tipo_documento, documento, nombre, apellido, fecha_nacimiento, genero, email, password) 
                                   VALUES (:tipo_documento, :documento, :nombre, :apellido, :fecha_nacimiento, :genero, :email, :password)");

            $stmt->bindParam(":tipo_documento", $tipo_documento);
            $stmt->bindParam(":documento", $documento);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":apellido", $apellido);
            $stmt->bindParam(":fecha_nacimiento", $fecha_nacimiento);
            $stmt->bindParam(":genero", $genero);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $hashPassword);

            if ($stmt->execute()) {
                return ["codigo" => 200, "mensaje" => "Registro exitoso. Ahora puedes iniciar sesión."];
            } else {
                return ["codigo" => 500, "mensaje" => "Error al registrar el usuario."];
            }
        } catch (Exception $e) {
            return ["codigo" => 500, "mensaje" => "Error del servidor: " . $e->getMessage()];
        }
    }
}
?>