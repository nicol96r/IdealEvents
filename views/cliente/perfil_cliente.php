<?php
session_start();
// Evitar que el navegador cachee esta página
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit();
}

// Incluir modelos necesarios
include_once "../../models/LoginModel.php";

// Obtener información del usuario
$id_usuario = $_SESSION['id_usuario'];
$usuario = LoginModel::mdlObtenerUsuarioPorId($id_usuario);

if (!$usuario) {
    header("Location: dashboard.php?error=" . urlencode("Error al obtener datos del usuario"));
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Ideal Event's</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../views/css/main.css">
    <style>
        .navbar-logo {
            height: 40px !important;
            width: auto !important;
            object-fit: contain !important;
        }
        .profile-header {
            background: linear-gradient(to right, #4e73df, #224abe);
            color: white;
            padding: 2rem 0;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid rgba(255,255,255,0.5);
            object-fit: cover;
        }
        .profile-card {
            transition: transform 0.3s;
        }
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .nav-pills .nav-link.active {
            background-color: #4e73df;
        }
        .nav-pills .nav-link {
            color: #4e73df;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../../public/logo/logo.png" alt="Logo" class="navbar-logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-house-door me-1"></i> Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php#eventos"><i class="bi bi-calendar-event me-1"></i> Eventos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php#mis-inscripciones"><i class="bi bi-bookmark-check me-1"></i> Mis Inscripciones</a>
                </li>
            </ul>
            <div class="dropdown ms-auto">
                <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item active" href="perfil_cliente.php"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
                    <li><a class="dropdown-item" href="mis_pagos.php"><i class="bi bi-credit-card me-2"></i> Mis Pagos</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/IdealEventsx/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Perfil Header -->
    <div class="profile-header shadow-sm text-center">
        <img src="../../public/img/default-user.png" alt="Foto de perfil" class="profile-img mb-3">
        <h2><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></h2>
        <p class="mb-0"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($usuario['email']) ?></p>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Menú</h5>
                </div>
                <div class="card-body p-0">
                    <nav class="nav flex-column nav-pills">
                        <a class="nav-link active" href="#datos-personales" data-bs-toggle="pill" data-bs-target="#datos-personales">
                            <i class="bi bi-person me-2"></i>Datos Personales
                        </a>
                        <a class="nav-link" href="#seguridad" data-bs-toggle="pill" data-bs-target="#seguridad">
                            <i class="bi bi-shield-lock me-2"></i>Seguridad
                        </a>
                        <a class="nav-link" href="#preferencias" data-bs-toggle="pill" data-bs-target="#preferencias">
                            <i class="bi bi-gear me-2"></i>Preferencias
                        </a>
                        <a class="nav-link" href="mis_pagos.php">
                            <i class="bi bi-credit-card me-2"></i>Historial de Pagos
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="tab-content">
                <!-- Datos Personales -->
                <div class="tab-pane fade show active" id="datos-personales">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-person me-2"></i>Datos Personales</h5>
                        </div>
                        <div class="card-body">
                            <form action="../../controllers/UsuarioController.php?action=actualizarPerfil" method="POST">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="apellido" class="form-label">Apellido</label>
                                        <input type="text" class="form-control" id="apellido" name="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="tipo_documento" class="form-label">Tipo de Documento</label>
                                        <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                            <option value="Cédula" <?= $usuario['tipo_documento'] === 'Cédula' ? 'selected' : '' ?>>Cédula</option>
                                            <option value="Tarjeta de Identidad" <?= $usuario['tipo_documento'] === 'Tarjeta de Identidad' ? 'selected' : '' ?>>Tarjeta de Identidad</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="documento" class="form-label">Número de Documento</label>
                                        <input type="text" class="form-control" id="documento" name="documento" value="<?= htmlspecialchars($usuario['documento']) ?>" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($usuario['fecha_nacimiento']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="genero" class="form-label">Género</label>
                                        <select class="form-select" id="genero" name="genero" required>
                                            <option value="Masculino" <?= $usuario['genero'] === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                            <option value="Femenino" <?= $usuario['genero'] === 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Seguridad -->
                <div class="tab-pane fade" id="seguridad">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Cambiar Contraseña</h5>
                        </div>
                        <div class="card-body">
                            <form action="../../controllers/UsuarioController.php?action=cambiarPassword" method="POST">
                                <div class="mb-3">
                                    <label for="password_actual" class="form-label">Contraseña Actual</label>
                                    <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                                </div>

                                <div class="mb-3">
                                    <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_nueva" name="password_nueva" required>
                                    <div class="form-text">La contraseña debe tener al menos 8 caracteres, incluyendo letras y números.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Zona de Peligro</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">Eliminar tu cuenta es una acción permanente y no se puede deshacer. Se eliminarán todos tus datos, inscripciones y pagos.</p>
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalEliminarCuenta">
                                <i class="bi bi-trash me-2"></i>Eliminar Mi Cuenta
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Preferencias -->
                <div class="tab-pane fade" id="preferencias">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Preferencias</h5>
                        </div>
                        <div class="card-body">
                            <form action="../../controllers/UsuarioController.php?action=guardarPreferencias" method="POST">
                                <h6 class="mb-3">Notificaciones</h6>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="notif_email" name="notif_email" checked>
                                    <label class="form-check-label" for="notif_email">Recibir notificaciones por correo electrónico</label>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="notif_nuevos" name="notif_nuevos" checked>
                                    <label class="form-check-label" for="notif_nuevos">Notificarme sobre nuevos eventos</label>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="notif_recordatorios" name="notif_recordatorios" checked>
                                    <label class="form-check-label" for="notif_recordatorios">Enviarme recordatorios de eventos</label>
                                </div>

                                <h6 class="mb-3 mt-4">Categorías de Interés</h6>

                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="cat_conciertos" name="categorias[]" value="conciertos" checked>
                                            <label class="form-check-label" for="cat_conciertos">Conciertos</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="cat_teatro" name="categorias[]" value="teatro">
                                            <label class="form-check-label" for="cat_teatro">Teatro</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="cat_deportes" name="categorias[]" value="deportes">
                                            <label class="form-check-label" for="cat_deportes">Deportes</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="cat_conferencias" name="categorias[]" value="conferencias" checked>
                                            <label class="form-check-label" for="cat_conferencias">Conferencias</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="cat_gastronomia" name="categorias[]" value="gastronomia" checked>
                                            <label class="form-check-label" for="cat_gastronomia">Gastronomía</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="cat_cultura" name="categorias[]" value="cultura">
                                            <label class="form-check-label" for="cat_cultura">Cultura</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary">Guardar Preferencias</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar Cuenta -->
<div class="modal fade" id="modalEliminarCuenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Eliminar Cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar tu cuenta? Esta acción es permanente y no se puede deshacer.</p>
                <p>Se eliminarán todos tus datos personales, inscripciones a eventos y registros de pagos.</p>
                <form action="../../controllers/UsuarioController.php?action=eliminarCuenta" method="POST" id="formEliminarCuenta">
                    <div class="mb-3">
                        <label for="password_eliminar" class="form-label">Para confirmar, introduce tu contraseña:</label>
                        <input type="password" class="form-control" id="password_eliminar" name="password" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmar_eliminacion" name="confirmar" required>
                        <label class="form-check-label" for="confirmar_eliminacion">
                            Entiendo que esta acción es permanente y acepto eliminar mi cuenta.
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="document.getElementById('formEliminarCuenta').submit();">Eliminar Mi Cuenta</button>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">© <?= date('Y') ?> Ideal Event's. Todos los derechos reservados.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="#" class="text-white me-3">Términos y Condiciones</a>
                <a href="#" class="text-white">Política de Privacidad</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>