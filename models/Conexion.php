<?php

class Conexion
{
    private static $host = "localhost";
    private static $usuario = "root";
    private static $password = ""; // Si has configurado contraseña para root, colócala aquí
    private static $baseDatos = "sistema_eventos";
    private static $conexion = null;

    private function __construct() {}
    private function __clone() {}
    public function __wakeup() {}

    public static function obtenerConexion()
    {
        if (self::$conexion === null) {
            try {
                $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$baseDatos;
                $opciones = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];

                self::$conexion = new PDO($dsn, self::$usuario, self::$password, $opciones);

            } catch (PDOException $e) {
                // Mensaje más detallado para debugging (quitar en producción)
                $errorMsg = "Error de conexión: " . $e->getMessage() . "\n";
                $errorMsg .= "Intentando conectar a: " . self::$host . " con usuario: " . self::$usuario . "\n";
                $errorMsg .= "Base de datos: " . self::$baseDatos;

                error_log($errorMsg);
                throw new PDOException("Error al conectar con la base de datos. Detalles en el log.");
            }
        }
        return self::$conexion;
    }

    // Este método debería ser sinónimo de obtenerConexion
    public static function conectar() {
        return self::obtenerConexion();
    }

    public static function cerrarConexion() {
        self::$conexion = null;
    }
}