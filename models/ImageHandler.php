<?php
class ImageHandler
{
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    private $maxSize = 5 * 1024 * 1024; // 5MB

    public function __construct()
    {
        // Asegurarnos de que la ruta siempre es desde la raíz del proyecto
        $this->uploadDir = __DIR__ . '/../public/img/';
    }

    public function uploadImage($file, $subfolder = 'eventos')
    {
        // Validaciones básicas
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Parámetros inválidos.');
        }

        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por PHP.',
                UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el formulario.',
                UPLOAD_ERR_PARTIAL => 'El archivo fue subido parcialmente.',
                UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo.',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal.',
                UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en el disco.',
                UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida del archivo.'
            ];

            $errorMsg = isset($errorMessages[$file['error']])
                ? $errorMessages[$file['error']]
                : 'Error desconocido al subir el archivo.';

            throw new Exception($errorMsg);
        }

        // Validar tipo de archivo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $this->allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido: ' . $mime . '. Solo se permiten JPG, PNG y GIF.');
        }

        // Validar tamaño
        if ($file['size'] > $this->maxSize) {
            throw new Exception('El archivo excede el tamaño máximo permitido de 5MB.');
        }

        // Crear estructura de directorios por año/mes
        $currentYear = date('Y');
        $currentMonth = date('m');
        $targetDir = $this->uploadDir . $subfolder . '/' . $currentYear . '/' . $currentMonth . '/';

        // Verificar y crear directorio si no existe
        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                throw new Exception('No se pudo crear el directorio para guardar la imagen: ' . $targetDir);
            }
        }

        // Generar nombre único para el archivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_', true) . '.' . $extension;
        $relativePath = $subfolder . '/' . $currentYear . '/' . $currentMonth . '/' . $filename;
        $fullPath = $targetDir . $filename;

        // Mover el archivo subido
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new Exception('No se pudo guardar el archivo subido en: ' . $fullPath);
        }

        // Debug info
        error_log("Imagen subida correctamente: " . $fullPath);
        error_log("Ruta relativa: " . $relativePath);

        // Retornar información de la imagen
        return [
            'nombre_archivo' => $file['name'],
            'mime_type' => $mime,
            'tamaño' => $file['size'],
            'ruta' => $relativePath,
            'full_path' => $fullPath
        ];
    }

    public function deleteImage($imageId)
    {
        // Obtener información de la imagen desde la BD
        $conexion = new Conexion();
        $db = $conexion->getConnection();

        $query = "SELECT ruta FROM imagenes WHERE id_imagen = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$imageId]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$image) {
            throw new Exception('Imagen no encontrada en la base de datos.');
        }

        // Eliminar archivo físico
        $filePath = $this->uploadDir . $image['ruta'];
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                throw new Exception('No se pudo eliminar el archivo físico.');
            }
        }

        // Eliminar registro de la BD
        $query = "DELETE FROM imagenes WHERE id_imagen = ?";
        $stmt = $db->prepare($query);
        return $stmt->execute([$imageId]);
    }
}