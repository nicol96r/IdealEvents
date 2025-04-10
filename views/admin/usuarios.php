<?php
session_start();
// Evitar que el navegador cachee esta página
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Verificación de sesión y rol
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /IdealEventsx/views/login.php");
    exit();
}

// Solo permitir acceso a administradores
if ($_SESSION['rol'] !== 'admin') {
    header("Location: /IdealEventsx/views/cliente/dashboard.php");
    exit();
}

// Incluir modelos necesarios
include_once "../../models/UsuarioModel.php";

// Obtener todos los usuarios
$usuarios = UsuarioModel::mdlListarUsuarios();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Ideal Event's</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/main.css">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <style>
        .navbar-logo {
            height: 40px !important;
            width: auto !important;
            object-fit: contain !important;
        }
        .admin-bg-primary {
            background-color: #4e73df;
            color: white;
        }
        .user-card {
            transition: transform 0.3s;
        }
        .user-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .user-img {
            height: 120px;
            width: 120px;
            object-fit: cover;
            border-radius: 50%;
        }
        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.375rem 0.75rem;
        }
        .btn-icon i {
            margin-right: 0.25rem;
        }
        .table-actions .btn {
            margin-right: 5px;
        }
        .admin-badge {
            background-color: #4e73df;
        }
        .cliente-badge {
            background-color: #1cc88a;
        }
        .role-badge {
            font-size: 0.8rem;
            padding: 0.35em 0.65em;
            border-radius: 0.5rem;
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
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="eventos.php"><i class="bi bi-calendar-event me-1"></i> Eventos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="usuarios.php"><i class="bi bi-people me-1"></i> Usuarios</a>
                </li>
            </ul>
            <div class="dropdown ms-auto">
                <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['nombre'] ?? 'Admin') ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="perfil.php"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/IdealEventsx/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people me-2"></i>Gestión de Usuarios</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
            <i class="bi bi-person-plus-fill me-2"></i>Nuevo Usuario
        </button>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Vista de tarjetas y tabla -->
    <ul class="nav nav-tabs mb-4" id="viewTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" type="button" role="tab" aria-controls="table-view" aria-selected="true">
                <i class="bi bi-table me-1"></i>Vista de Tabla
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="cards-tab" data-bs-toggle="tab" data-bs-target="#cards-view" type="button" role="tab" aria-controls="cards-view" aria-selected="false">
                <i class="bi bi-grid-3x3-gap me-1"></i>Vista de Tarjetas
            </button>
        </li>
    </ul>

    <div class="tab-content" id="viewTabsContent">
        <!-- Vista de Tabla -->
        <div class="tab-pane fade show active" id="table-view" role="tabpanel" aria-labelledby="table-tab">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 fw-bold"><i class="bi bi-list-ul me-2"></i>Listado de Usuarios</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaUsuarios">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Documento</th>
                                <th>Email</th>
                                <th>Género</th>
                                <th>Rol</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?= $usuario['id_usuario'] ?></td>
                                    <td><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
                                    <td><?= htmlspecialchars($usuario['tipo_documento'] . ': ' . $usuario['documento']) ?></td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td><?= htmlspecialchars($usuario['genero']) ?></td>
                                    <td>
                                        <span class="badge <?= $usuario['rol'] === 'admin' ? 'bg-primary' : 'bg-success' ?>">
                                            <?= htmlspecialchars($usuario['rol']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                                    <td class="table-actions">
                                        <button class="btn btn-sm btn-outline-primary btn-ver-usuario"
                                                data-id="<?= $usuario['id_usuario'] ?>"
                                                title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning btn-editar-usuario"
                                                data-id="<?= $usuario['id_usuario'] ?>"
                                                data-tipo-documento="<?= htmlspecialchars($usuario['tipo_documento']) ?>"
                                                data-documento="<?= htmlspecialchars($usuario['documento']) ?>"
                                                data-nombre="<?= htmlspecialchars($usuario['nombre']) ?>"
                                                data-apellido="<?= htmlspecialchars($usuario['apellido']) ?>"
                                                data-fecha-nacimiento="<?= $usuario['fecha_nacimiento'] ?>"
                                                data-genero="<?= htmlspecialchars($usuario['genero']) ?>"
                                                data-email="<?= htmlspecialchars($usuario['email']) ?>"
                                                data-rol="<?= htmlspecialchars($usuario['rol']) ?>"
                                                title="Editar usuario">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <?php if ($usuario['id_usuario'] != $_SESSION['id_usuario']): ?>
                                            <button class="btn btn-sm btn-outline-info btn-cambiar-rol"
                                                    data-id="<?= $usuario['id_usuario'] ?>"
                                                    data-nombre="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>"
                                                    data-rol="<?= htmlspecialchars($usuario['rol']) ?>"
                                                    title="Cambiar rol">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-eliminar-usuario"
                                                    data-id="<?= $usuario['id_usuario'] ?>"
                                                    data-nombre="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>"
                                                    title="Eliminar usuario">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vista de Tarjetas -->
        <div class="tab-pane fade" id="cards-view" role="tabpanel" aria-labelledby="cards-tab">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                <?php foreach ($usuarios as $usuario): ?>
                    <div class="col">
                        <div class="card h-100 user-card">
                            <div class="text-center pt-4">
                                <img src="../../public/img/default-user.png" class="user-img" alt="Foto de perfil">
                                <span class="position-absolute top-0 end-0 p-2">
                                    <span class="badge <?= $usuario['rol'] === 'admin' ? 'bg-primary' : 'bg-success' ?>">
                                        <?= htmlspecialchars($usuario['rol']) ?>
                                    </span>
                                </span>
                            </div>
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></h5>
                                <p class="card-text text-muted">
                                    <i class="bi bi-envelope"></i> <?= htmlspecialchars($usuario['email']) ?>
                                </p>
                                <p class="card-text">
                                    <small><?= htmlspecialchars($usuario['tipo_documento'] . ': ' . $usuario['documento']) ?></small><br>
                                    <small><i class="bi bi-calendar"></i> Registro: <?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></small>
                                </p>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-sm btn-outline-primary btn-ver-usuario" data-id="<?= $usuario['id_usuario'] ?>">
                                        <i class="bi bi-eye"></i> Ver
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning btn-editar-usuario"
                                            data-id="<?= $usuario['id_usuario'] ?>"
                                            data-tipo-documento="<?= htmlspecialchars($usuario['tipo_documento']) ?>"
                                            data-documento="<?= htmlspecialchars($usuario['documento']) ?>"
                                            data-nombre="<?= htmlspecialchars($usuario['nombre']) ?>"
                                            data-apellido="<?= htmlspecialchars($usuario['apellido']) ?>"
                                            data-fecha-nacimiento="<?= $usuario['fecha_nacimiento'] ?>"
                                            data-genero="<?= htmlspecialchars($usuario['genero']) ?>"
                                            data-email="<?= htmlspecialchars($usuario['email']) ?>"
                                            data-rol="<?= htmlspecialchars($usuario['rol']) ?>">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <?php if ($usuario['id_usuario'] != $_SESSION['id_usuario']): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button class="dropdown-item btn-cambiar-rol"
                                                            data-id="<?= $usuario['id_usuario'] ?>"
                                                            data-nombre="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>"
                                                            data-rol="<?= htmlspecialchars($usuario['rol']) ?>">
                                                        <i class="bi bi-arrow-repeat me-2"></i>Cambiar rol
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger btn-eliminar-usuario"
                                                            data-id="<?= $usuario['id_usuario'] ?>"
                                                            data-nombre="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>">
                                                        <i class="bi bi-trash me-2"></i>Eliminar
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Usuario -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" aria-labelledby="modalNuevoUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalNuevoUsuarioLabel"><i class="bi bi-person-plus-fill me-2"></i>Crear Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../../controllers/UsuarioController.php?action=crear" method="POST" id="formNuevoUsuario">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="apellido" class="form-label">Apellido *</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_documento" class="form-label">Tipo de Documento *</label>
                            <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                <option value="" selected disabled>Seleccionar...</option>
                                <option value="Cédula">Cédula</option>
                                <option value="Tarjeta de Identidad">Tarjeta de Identidad</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="documento" class="form-label">Número de Documento *</label>
                            <input type="text" class="form-control" id="documento" name="documento" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="genero" class="form-label">Género *</label>
                            <select class="form-select" id="genero" name="genero" required>
                                <option value="" selected disabled>Seleccionar...</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   pattern=".{6,}" title="La contraseña debe tener al menos 6 caracteres">
                            <div class="form-text">La contraseña debe tener al menos 6 caracteres.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirmar_password" class="form-label">Confirmar Contraseña *</label>
                            <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="rol" class="form-label">Rol *</label>
                        <select class="form-select" id="rol" name="rol" required>
                            <option value="cliente" selected>Cliente</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <small>* Campos obligatorios</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarUsuario">
                    <i class="bi bi-save me-1"></i> Guardar Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditarUsuarioLabel"><i class="bi bi-pencil-square me-2"></i>Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../../controllers/UsuarioController.php?action=actualizar" method="POST" id="formEditarUsuario">
                    <input type="hidden" id="edit_id_usuario" name="id_usuario">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_apellido" class="form-label">Apellido *</label>
                            <input type="text" class="form-control" id="edit_apellido" name="apellido" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_tipo_documento" class="form-label">Tipo de Documento *</label>
                            <select class="form-select" id="edit_tipo_documento" name="tipo_documento" required>
                                <option value="Cédula">Cédula</option>
                                <option value="Tarjeta de Identidad">Tarjeta de Identidad</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_documento" class="form-label">Número de Documento *</label>
                            <input type="text" class="form-control" id="edit_documento" name="documento" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                            <input type="date" class="form-control" id="edit_fecha_nacimiento" name="fecha_nacimiento" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_genero" class="form-label">Género *</label>
                            <select class="form-select" id="edit_genero" name="genero" required>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Correo Electrónico *</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_rol" class="form-label">Rol *</label>
                        <select class="form-select" id="edit_rol" name="rol" required>
                            <option value="cliente">Cliente</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="cambiar_password" name="cambiar_password">
                            <label class="form-check-label" for="cambiar_password">
                                Cambiar Contraseña
                            </label>
                        </div>
                    </div>

                    <div id="password_fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_password" class="form-label">Nueva Contraseña *</label>
                                <input type="password" class="form-control" id="edit_password" name="password"
                                       pattern=".{6,}" title="La contraseña debe tener al menos 6 caracteres">
                                <div class="form-text">La contraseña debe tener al menos 6 caracteres.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_confirmar_password" class="form-label">Confirmar Nueva Contraseña *</label>
                                <input type="password" class="form-control" id="edit_confirmar_password" name="confirmar_password">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <small>* Campos obligatorios</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnActualizarUsuario">
                    <i class="bi bi-save me-1"></i> Actualizar Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Usuario -->
<div class="modal fade" id="modalVerUsuario" tabindex="-1" aria-labelledby="modalVerUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalVerUsuarioLabel"><i class="bi bi-info-circle me-2"></i>Detalles del Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="../../public/img/default-user.png" class="img-fluid rounded-circle mb-3" alt="Foto de perfil" style="width: 150px; height: 150px;">
                        <div id="ver_badge" class="mt-2"></div>
                    </div>
                    <div class="col-md-8">
                        <h4 id="ver_nombre"></h4>
                        <p class="text-muted" id="ver_email"></p>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Fecha de Registro:</h6>
                                <p id="ver_fecha_registro"></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold">ID de Usuario:</h6>
                                <p id="ver_id"></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Actividad:</h6>
                                <div id="ver_actividad">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    Cargando datos de actividad...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Inscripciones:</h6>
                        <div id="ver_inscripciones">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            Cargando inscripciones...
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Pagos:</h6>
                        <div id="ver_pagos">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            Cargando pagos...
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnGenerarReporte">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Generar Reporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Rol -->
<div class="modal fade" id="modalCambiarRol" tabindex="-1" aria-labelledby="modalCambiarRolLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalCambiarRolLabel"><i class="bi bi-arrow-repeat me-2"></i>Cambiar Rol de Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../../controllers/UsuarioController.php?action=cambiarRol" method="POST" id="formCambiarRol">
                    <input type="hidden" id="cambiar_rol_id" name="id_usuario">

                    <div class="mb-3">
                        <label class="form-label">Usuario:</label>
                        <p class="form-control-static" id="cambiar_rol_nombre"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rol Actual:</label>
                        <p class="form-control-static">
                            <span class="badge" id="cambiar_rol_actual"></span>
                        </p>
                    </div>

                    <div class="mb-3">
                        <label for="nuevo_rol" class="form-label">Nuevo Rol:</label>
                        <select class="form-select" id="nuevo_rol" name="nuevo_rol" required>
                            <option value="cliente">Cliente</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <small>Cambiar el rol de un usuario modificará sus permisos en el sistema. Asegúrese de que esta acción es correcta.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="btnConfirmarCambioRol">
                    <i class="bi bi-check-circle me-1"></i> Confirmar Cambio
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
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

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar DataTable
        $('#tablaUsuarios').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json',
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
                pageLength: 10,
                order: [[0, 'desc']]
            }
        });

        // Mostrar/ocultar campos de contraseña al editar
        $('#cambiar_password').change(function() {
            if(this.checked) {
                $('#password_fields').show();
                $('#edit_password, #edit_confirmar_password').prop('required', true);
            } else {
                $('#password_fields').hide();
                $('#edit_password, #edit_confirmar_password').prop('required', false);
            }
        });

        // Validar contraseñas coincidentes en formulario nuevo usuario
        $('#formNuevoUsuario').on('submit', function(e) {
            if($('#password').val() !== $('#confirmar_password').val()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Las contraseñas no coinciden',
                    confirmButtonColor: '#4e73df'
                });
                return false;
            }
            return true;
        });

        // Validar contraseñas coincidentes en formulario editar usuario
        $('#formEditarUsuario').on('submit', function(e) {
            if($('#cambiar_password').is(':checked')) {
                if($('#edit_password').val() !== $('#edit_confirmar_password').val()) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Las contraseñas no coinciden',
                        confirmButtonColor: '#4e73df'
                    });
                    return false;
                }
            }
            return true;
        });

        // Botón guardar nuevo usuario
        $('#btnGuardarUsuario').click(function() {
            if ($('#formNuevoUsuario')[0].checkValidity()) {
                $('#formNuevoUsuario').submit();
            } else {
                $('#formNuevoUsuario')[0].reportValidity();
            }
        });

        // Botón actualizar usuario
        $('#btnActualizarUsuario').click(function() {
            if ($('#formEditarUsuario')[0].checkValidity()) {
                $('#formEditarUsuario').submit();
            } else {
                $('#formEditarUsuario')[0].reportValidity();
            }
        });

        // Botón confirmar cambio de rol
        $('#btnConfirmarCambioRol').click(function() {
            if ($('#formCambiarRol')[0].checkValidity()) {
                $('#formCambiarRol').submit();
            } else {
                $('#formCambiarRol')[0].reportValidity();
            }
        });

        // Botón ver usuario
        $('.btn-ver-usuario').click(function() {
            const id = $(this).data('id');

            // En un sistema real, esto se haría mediante una llamada AJAX
            const usuariosData = <?= json_encode($usuarios) ?>;
            const usuario = usuariosData.find(u => u.id_usuario == id);

            if (usuario) {
                $('#ver_id').text(usuario.id_usuario);
                $('#ver_nombre').text(usuario.nombre + ' ' + usuario.apellido);
                $('#ver_email').text(usuario.email);
                $('#ver_documento').text(usuario.tipo_documento + ': ' + usuario.documento);
                $('#ver_fecha_nacimiento').text(new Date(usuario.fecha_nacimiento).toLocaleDateString());
                $('#ver_genero').text(usuario.genero);
                $('#ver_fecha_registro').text(new Date(usuario.fecha_registro).toLocaleDateString());

                // Mostrar badge de rol
                const rolClass = usuario.rol === 'admin' ? 'bg-primary' : 'bg-success';
                $('#ver_badge').html(`<span class="badge ${rolClass}">${usuario.rol}</span>`);

                // Cargar datos de actividad, inscripciones y pagos mediante AJAX
                // Simulación para este ejemplo
                setTimeout(() => {
                    const ultimoAcceso = new Date();
                    ultimoAcceso.setDate(ultimoAcceso.getDate() - Math.floor(Math.random() * 10));

                    $('#ver_actividad').html(`<p>Último acceso: ${ultimoAcceso.toLocaleDateString()} ${ultimoAcceso.toLocaleTimeString()}</p>`);

                    // Generar datos aleatorios para inscripciones
                    const numInscripciones = Math.floor(Math.random() * 5);
                    let inscripcionesHTML = '';

                    if (numInscripciones > 0) {
                        inscripcionesHTML = '<ul class="list-group">';
                        for (let i = 0; i < numInscripciones; i++) {
                            inscripcionesHTML += `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Evento ${i+1}
                                    <span class="badge bg-primary rounded-pill">${new Date().toLocaleDateString()}</span>
                                </li>`;
                        }
                        inscripcionesHTML += '</ul>';
                    } else {
                        inscripcionesHTML = '<p class="text-muted">No hay inscripciones registradas</p>';
                    }

                    $('#ver_inscripciones').html(inscripcionesHTML);

                    // Generar datos aleatorios para pagos
                    const numPagos = Math.floor(Math.random() * 5);
                    let pagosHTML = '';

                    if (numPagos > 0) {
                        pagosHTML = '<ul class="list-group">';
                        for (let i = 0; i < numPagos; i++) {
                            const estados = ['completado', 'pendiente', 'rechazado'];
                            const estado = estados[Math.floor(Math.random() * estados.length)];
                            const estadoClass = estado === 'completado' ? 'bg-success' :
                                (estado === 'pendiente' ? 'bg-warning text-dark' : 'bg-danger');

                            pagosHTML += `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Pago ${i+1}: ${(Math.random() * 100).toFixed(2)}
                                    <span class="badge ${estadoClass}">${estado}</span>
                                </li>`;
                        }
                        pagosHTML += '</ul>';
                    } else {
                        pagosHTML = '<p class="text-muted">No hay pagos registrados</p>';
                    }

                    $('#ver_pagos').html(pagosHTML);
                }, 1000);

                $('#modalVerUsuario').modal('show');
            }
        });

        // Botón editar usuario
        $('.btn-editar-usuario').click(function() {
            const id = $(this).data('id');
            const tipoDocumento = $(this).data('tipo-documento');
            const documento = $(this).data('documento');
            const nombre = $(this).data('nombre');
            const apellido = $(this).data('apellido');
            const fechaNacimiento = $(this).data('fecha-nacimiento');
            const genero = $(this).data('genero');
            const email = $(this).data('email');
            const rol = $(this).data('rol');

            $('#edit_id_usuario').val(id);
            $('#edit_tipo_documento').val(tipoDocumento);
            $('#edit_documento').val(documento);
            $('#edit_nombre').val(nombre);
            $('#edit_apellido').val(apellido);
            $('#edit_fecha_nacimiento').val(fechaNacimiento);
            $('#edit_genero').val(genero);
            $('#edit_email').val(email);
            $('#edit_rol').val(rol);

            // Resetear checkbox y campos de contraseña
            $('#cambiar_password').prop('checked', false);
            $('#password_fields').hide();
            $('#edit_password, #edit_confirmar_password').prop('required', false).val('');

            $('#modalEditarUsuario').modal('show');
        });

        // Botón cambiar rol
        $('.btn-cambiar-rol').click(function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            const rol = $(this).data('rol');

            $('#cambiar_rol_id').val(id);
            $('#cambiar_rol_nombre').text(nombre);

            const rolClass = rol === 'admin' ? 'bg-primary' : 'bg-success';
            $('#cambiar_rol_actual').attr('class', 'badge ' + rolClass).text(rol);

            // Seleccionar el rol opuesto como nuevo rol por defecto
            $('#nuevo_rol').val(rol === 'admin' ? 'cliente' : 'admin');

            $('#modalCambiarRol').modal('show');
        });

        // Botón eliminar usuario
        $('.btn-eliminar-usuario').click(function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');

            Swal.fire({
                title: '¿Eliminar usuario?',
                html: `¿Estás seguro de que deseas eliminar el usuario <strong>${nombre}</strong>?<br>Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `../../controllers/UsuarioController.php?action=eliminar&id=${id}`;
                }
            });
        });

        // Botón generar reporte
        $('#btnGenerarReporte').click(function() {
            const id = $('#ver_id').text();

            Swal.fire({
                title: 'Generando reporte',
                html: 'Por favor espera mientras se genera el reporte PDF...',
                timer: 2000,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                }
            }).then(() => {
                // En un sistema real, redirigiría a un endpoint para generar el PDF
                Swal.fire(
                    '¡Reporte generado!',
                    'El reporte del usuario se ha generado correctamente.',
                    'success'
                );
            });
        });

        // Mostrar mensajes de sesión con SweetAlert2
        <?php if (isset($_SESSION['success'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Operación exitosa',
            text: '<?= addslashes($_SESSION['success']) ?>',
            confirmButtonColor: '#4e73df'
        });
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?= addslashes($_SESSION['error']) ?>',
            confirmButtonColor: '#e74a3b'
        });
        <?php endif; ?>
    });
</script>
</body>
</html>
