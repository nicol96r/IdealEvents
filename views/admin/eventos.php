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
include_once "../../models/EventoModel.php";

// Obtener todos los eventos
$eventos = EventoModel::mdlListarEventos();

// Obtener categorías disponibles para el filtro
$categorias = EventoModel::mdlObtenerCategorias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Eventos - Ideal Event's</title>
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
        .evento-card {
            transition: transform 0.3s;
        }
        .evento-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .evento-img {
            height: 120px;
            object-fit: cover;
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
        .custom-file-label::after {
            content: "Buscar";
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
                    <a class="nav-link active" href="eventos.php"><i class="bi bi-calendar-event me-1"></i> Eventos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="usuarios.php"><i class="bi bi-people me-1"></i> Usuarios</a>
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
        <h2><i class="bi bi-calendar-event me-2"></i>Gestión de Eventos</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoEvento">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Evento
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
                    <h6 class="m-0 fw-bold"><i class="bi bi-list-ul me-2"></i>Listado de Eventos</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tablaEventos">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Ubicación</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($eventos as $evento): ?>
                                <tr>
                                    <td><?= $evento['id_evento'] ?></td>
                                    <td><?= htmlspecialchars($evento['titulo']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($evento['fecha'])) ?></td>
                                    <td><?= htmlspecialchars($evento['hora']) ?></td>
                                    <td><?= htmlspecialchars($evento['ubicacion']) ?></td>
                                    <td><span class="badge bg-primary"><?= htmlspecialchars($evento['categoria']) ?></span></td>
                                    <td>$<?= number_format($evento['precio'], 2) ?></td>
                                    <td><?= date('d/m/Y', strtotime($evento['fecha_creacion'])) ?></td>
                                    <td class="table-actions">
                                        <button class="btn btn-sm btn-outline-primary btn-ver" data-id="<?= $evento['id_evento'] ?>" title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning btn-editar"
                                                data-id="<?= $evento['id_evento'] ?>"
                                                data-titulo="<?= htmlspecialchars($evento['titulo']) ?>"
                                                data-descripcion="<?= htmlspecialchars($evento['descripcion']) ?>"
                                                data-fecha="<?= $evento['fecha'] ?>"
                                                data-hora="<?= $evento['hora'] ?>"
                                                data-ubicacion="<?= htmlspecialchars($evento['ubicacion']) ?>"
                                                data-categoria="<?= htmlspecialchars($evento['categoria']) ?>"
                                                data-precio="<?= $evento['precio'] ?>"
                                                title="Editar evento">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="<?= $evento['id_evento'] ?>" data-titulo="<?= htmlspecialchars($evento['titulo']) ?>" title="Eliminar evento">
                                            <i class="bi bi-trash"></i>
                                        </button>
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
                <?php foreach ($eventos as $evento): ?>
                    <div class="col">
                        <div class="card h-100 evento-card">
                            <img src="../../public/img/eventos/<?= htmlspecialchars($evento['imagen_nombre'] ?? 'default-event.jpg') ?>"
                                 class="card-img-top evento-img" alt="<?= htmlspecialchars($evento['titulo']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($evento['titulo']) ?></h5>
                                <p class="card-text text-muted">
                                    <i class="bi bi-calendar-event"></i> <?= date('d/m/Y', strtotime($evento['fecha'])) ?>
                                    <i class="bi bi-clock ms-2"></i> <?= htmlspecialchars($evento['hora']) ?>
                                </p>
                                <p class="card-text"><?= htmlspecialchars(substr($evento['descripcion'], 0, 80)) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary"><?= htmlspecialchars($evento['categoria']) ?></span>
                                    <span class="fw-bold">$<?= number_format($evento['precio'], 2) ?></span>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-sm btn-outline-primary btn-ver" data-id="<?= $evento['id_evento'] ?>">
                                        <i class="bi bi-eye"></i> Ver
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning btn-editar"
                                            data-id="<?= $evento['id_evento'] ?>"
                                            data-titulo="<?= htmlspecialchars($evento['titulo']) ?>"
                                            data-descripcion="<?= htmlspecialchars($evento['descripcion']) ?>"
                                            data-fecha="<?= $evento['fecha'] ?>"
                                            data-hora="<?= $evento['hora'] ?>"
                                            data-ubicacion="<?= htmlspecialchars($evento['ubicacion']) ?>"
                                            data-categoria="<?= htmlspecialchars($evento['categoria']) ?>"
                                            data-precio="<?= $evento['precio'] ?>">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="<?= $evento['id_evento'] ?>" data-titulo="<?= htmlspecialchars($evento['titulo']) ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Evento -->
<div class="modal fade" id="modalNuevoEvento" tabindex="-1" aria-labelledby="modalNuevoEventoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalNuevoEventoLabel"><i class="bi bi-calendar-plus me-2"></i>Crear Nuevo Evento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../../controllers/EventoController.php?action=guardar" method="POST" enctype="multipart/form-data" id="formNuevoEvento">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="titulo" class="form-label">Título del Evento *</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="categoria" class="form-label">Categoría *</label>
                            <select class="form-select" id="categoria" name="categoria" required>
                                <option value="" selected disabled>Seleccionar...</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= htmlspecialchars($categoria) ?>"><?= htmlspecialchars($categoria) ?></option>
                                <?php endforeach; ?>
                                <option value="nueva">+ Nueva Categoría</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3" id="nuevaCategoriaContainer" style="display: none;">
                        <label for="nuevaCategoria" class="form-label">Nueva Categoría</label>
                        <input type="text" class="form-control" id="nuevaCategoria" name="nuevaCategoria">
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción *</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="fecha" class="form-label">Fecha *</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="hora" class="form-label">Hora *</label>
                            <input type="time" class="form-control" id="hora" name="hora" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="precio" class="form-label">Precio ($) *</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="precio" name="precio" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="ubicacion" class="form-label">Ubicación *</label>
                        <input type="text" class="form-control" id="ubicacion" name="ubicacion" required>
                    </div>

                    <div class="mb-3">
                        <label for="imagen" class="form-label">Imagen del Evento</label>
                        <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                        <div class="form-text">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB.</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="destacado" name="destacado">
                            <label class="form-check-label" for="destacado">Marcar como evento destacado</label>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <small>* Campos obligatorios</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarEvento">
                    <i class="bi bi-save me-1"></i> Guardar Evento
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Evento -->
<div class="modal fade" id="modalEditarEvento" tabindex="-1" aria-labelledby="modalEditarEventoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditarEventoLabel"><i class="bi bi-pencil-square me-2"></i>Editar Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../../controllers/EventoController.php?action=actualizar" method="POST" enctype="multipart/form-data" id="formEditarEvento">
                    <input type="hidden" id="edit_id_evento" name="id_evento">

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="edit_titulo" class="form-label">Título del Evento *</label>
                            <input type="text" class="form-control" id="edit_titulo" name="titulo" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_categoria" class="form-label">Categoría *</label>
                            <select class="form-select" id="edit_categoria" name="categoria" required>
                                <option value="" selected disabled>Seleccionar...</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= htmlspecialchars($categoria) ?>"><?= htmlspecialchars($categoria) ?></option>
                                <?php endforeach; ?>
                                <option value="nueva">+ Nueva Categoría</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3" id="editNuevaCategoriaContainer" style="display: none;">
                        <label for="edit_nuevaCategoria" class="form-label">Nueva Categoría</label>
                        <input type="text" class="form-control" id="edit_nuevaCategoria" name="nuevaCategoria">
                    </div>

                    <div class="mb-3">
                        <label for="edit_descripcion" class="form-label">Descripción *</label>
                        <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="4" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_fecha" class="form-label">Fecha *</label>
                            <input type="date" class="form-control" id="edit_fecha" name="fecha" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_hora" class="form-label">Hora *</label>
                            <input type="time" class="form-control" id="edit_hora" name="hora" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_precio" class="form-label">Precio ($) *</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="edit_precio" name="precio" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_ubicacion" class="form-label">Ubicación *</label>
                        <input type="text" class="form-control" id="edit_ubicacion" name="ubicacion" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_imagen" class="form-label">Imagen del Evento (dejar vacío para mantener la actual)</label>
                        <input type="file" class="form-control" id="edit_imagen" name="imagen" accept="image/*">
                        <div class="form-text">Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB.</div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit_destacado" name="destacado">
                            <label class="form-check-label" for="edit_destacado">Marcar como evento destacado</label>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <small>* Campos obligatorios</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnActualizarEvento">
                    <i class="bi bi-save me-1"></i> Actualizar Evento
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Evento -->
<div class="modal fade" id="modalVerEvento" tabindex="-1" aria-labelledby="modalVerEventoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalVerEventoLabel"><i class="bi bi-info-circle me-2"></i>Detalles del Evento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <img src="" id="ver_imagen" class="img-fluid rounded mb-3" alt="Imagen del evento">
                    </div>
                    <div class="col-md-8">
                        <h4 id="ver_titulo"></h4>
                        <div class="d-flex align-items-center mb-3">
                            <span class="badge bg-primary me-2" id="ver_categoria"></span>
                            <span class="text-muted">
                                <i class="bi bi-calendar-event me-1"></i><span id="ver_fecha"></span>
                                <i class="bi bi-clock ms-2 me-1"></i><span id="ver_hora"></span>
                            </span>
                        </div>
                        <h6 class="fw-bold">Descripción:</h6>
                        <p id="ver_descripcion"></p>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Ubicación:</h6>
                                <p id="ver_ubicacion"></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Precio:</h6>
                                <p id="ver_precio"></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold">ID del Evento:</h6>
                                <p id="ver_id"></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold">Fecha de Creación:</h6>
                                <p id="ver_fecha_creacion"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Inscritos:</h6>
                        <div id="ver_inscripciones">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            Cargando inscripciones...
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Estado de Pagos:</h6>
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
                <button type="button" class="btn btn-primary" id="btnDescargarReporte">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Descargar Reporte
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
    // Reemplazar la sección con error en el archivo eventos.php
    $(document).ready(function() {
        // Inicializar DataTable
        $('#tablaEventos').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json',
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
                pageLength: 10,
                order: [[0, 'desc']]
            }
        });

        // Manejar categoría personalizada
        $('#categoria').change(function() {
            if ($(this).val() === 'nueva') {
                $('#nuevaCategoriaContainer').show();
                $('#nuevaCategoria').prop('required', true);
            } else {
                $('#nuevaCategoriaContainer').hide();
                $('#nuevaCategoria').prop('required', false);
            }
        });

        $('#edit_categoria').change(function() {
            if ($(this).val() === 'nueva') {
                $('#editNuevaCategoriaContainer').show();
                $('#edit_nuevaCategoria').prop('required', true);
            } else {
                $('#editNuevaCategoriaContainer').hide();
                $('#edit_nuevaCategoria').prop('required', false);
            }
        });

        // Botón guardar nuevo evento
        $('#btnGuardarEvento').click(function() {
            if ($('#formNuevoEvento')[0].checkValidity()) {
                $('#formNuevoEvento').submit();
            } else {
                $('#formNuevoEvento')[0].reportValidity();
            }
        });

        // Botón actualizar evento
        $('#btnActualizarEvento').click(function() {
            if ($('#formEditarEvento')[0].checkValidity()) {
                $('#formEditarEvento').submit();
            } else {
                $('#formEditarEvento')[0].reportValidity();
            }
        });

        // Botón ver evento
        $('.btn-ver').click(function() {
            const id = $(this).data('id');

            // Simulación de carga de datos del evento
            // En un sistema real, esto se haría mediante una llamada AJAX
            const eventosData = <?= json_encode($eventos) ?>;
            const evento = eventosData.find(e => e.id_evento == id);

            if (evento) {
                $('#ver_id').text(evento.id_evento);
                $('#ver_titulo').text(evento.titulo);
                $('#ver_categoria').text(evento.categoria);
                $('#ver_descripcion').text(evento.descripcion);
                $('#ver_fecha').text(new Date(evento.fecha).toLocaleDateString());
                $('#ver_hora').text(evento.hora);
                $('#ver_ubicacion').text(evento.ubicacion);
                $('#ver_precio').text('$' + parseFloat(evento.precio).toFixed(2)); // Corregido
                $('#ver_fecha_creacion').text(new Date(evento.fecha_creacion).toLocaleDateString());

                // Imagen del evento
                const imgSrc = evento.imagen_nombre
                    ? '../../public/img/eventos/' + evento.imagen_nombre
                    : '../../public/img/eventos/default-event.jpg';
                $('#ver_imagen').attr('src', imgSrc);

                // Cargar inscripciones y pagos mediante AJAX
                // Simulación para este ejemplo
                setTimeout(() => {
                    $('#ver_inscripciones').html('<p class="text-muted">Datos de ejemplo: 15 inscritos</p>');
                    $('#ver_pagos').html('<p class="text-muted">Datos de ejemplo: 10 pagos completados, 5 pendientes</p>');
                }, 1000);

                $('#modalVerEvento').modal('show');
            }
        });

        // Botón editar evento
        $('.btn-editar').click(function() {
            const id = $(this).data('id');
            const titulo = $(this).data('titulo');
            const descripcion = $(this).data('descripcion');
            const fecha = $(this).data('fecha');
            const hora = $(this).data('hora');
            const ubicacion = $(this).data('ubicacion');
            const categoria = $(this).data('categoria');
            const precio = $(this).data('precio');

            $('#edit_id_evento').val(id);
            $('#edit_titulo').val(titulo);
            $('#edit_descripcion').val(descripcion);
            $('#edit_fecha').val(fecha);
            $('#edit_hora').val(hora);
            $('#edit_ubicacion').val(ubicacion);
            $('#edit_categoria').val(categoria);
            $('#edit_precio').val(precio);

            $('#modalEditarEvento').modal('show');
        });

        // Botón eliminar evento
        $('.btn-eliminar').click(function() {
            const id = $(this).data('id');
            const titulo = $(this).data('titulo');

            Swal.fire({
                title: '¿Eliminar evento?',
                html: `¿Estás seguro de que deseas eliminar el evento <strong>${titulo}</strong>?<br>Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `../../controllers/EventoController.php?action=eliminar&id=${id}`;
                }
            });
        });

        // Botón descargar reporte
        $('#btnDescargarReporte').click(function() {
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
                    'El reporte se ha generado correctamente.',
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