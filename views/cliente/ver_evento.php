<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Fix the include path - adjusting based on the directory structure
include_once "../../models/EventoModel.php";

// Verificamos que los modelos existan
if (!class_exists("EventoModel")) {
    die("Error: No se pudo cargar el modelo de eventos");
}

// Obtener el rol del usuario
$rol_usuario = isset($_SESSION['rol_usuario']) ? $_SESSION['rol_usuario'] : 'cliente';
$es_administrador = ($rol_usuario === 'admin' || $rol_usuario === 'organizador');

$eventos = EventoModel::mdlListarEventos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos Disponibles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/main.css">
    <style>
        .navbar-logo {
            height: 40px !important;
            width: auto !important;
            object-fit: contain !important;
        }
        .evento-card {
            transition: transform 0.3s;
        }
        .evento-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .evento-img {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-md custom-navbar">
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
                    <a class="nav-link active" href="ver_evento.php">Eventos</a>
                </li>
                <?php if ($es_administrador): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="Organizador.php">Organizador</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Clientes.php">Clientes</a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="dropdown ms-auto">
                <button class="btn dropdown-toggle profile-button" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> Ver perfil
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="../../usuarios.php">Ver datos</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="../../logout.php">Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Eventos Disponibles</h1>
        <a href="dashboard.php" class="btn btn-secondary">Volver al Panel</a>
    </div>

    <div class="alert alert-info mb-4">
        <p class="mb-0">¡Busca eventos de tu interés aquí! No olvides registrarte para recibir notificaciones de eventos.</p>
    </div>

    <?php if (isset($_SESSION['success'])) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row mb-4">
        <?php if ($es_administrador): ?>
            <div class="col-md-6">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarEvento">
                    <i class="bi bi-plus-circle me-1"></i> Agregar Evento
                </button>
            </div>
        <?php endif; ?>
        <div class="<?= $es_administrador ? 'col-md-6' : 'col-md-12' ?>">
            <form class="d-flex">
                <input class="form-control me-2" type="search" placeholder="Buscar eventos..." aria-label="Buscar">
                <button class="btn btn-outline-success" type="submit">Buscar</button>
            </form>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($eventos as $evento) : ?>
            <div class="col">
                <div class="card h-100 evento-card">
                    <img src="../../public/img/eventos/<?= htmlspecialchars($evento['imagen'] ?? 'destacado1.jpg') ?>"
                         class="card-img-top evento-img" alt="<?= htmlspecialchars($evento['titulo']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($evento['titulo']) ?></h5>
                        <p class="card-text text-muted">
                            <i class="bi bi-calendar-event"></i> <?= date('d/m/Y', strtotime($evento['fecha'])) ?>
                            <i class="bi bi-clock ms-2"></i> <?= htmlspecialchars($evento['hora']) ?>
                        </p>
                        <p class="card-text"><?= htmlspecialchars(substr($evento['descripción'], 0, 100)) ?>...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary"><?= htmlspecialchars($evento['categoria']) ?></span>
                            <span class="fw-bold">$<?= number_format($evento['precio'], 2) ?></span>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-<?= $es_administrador ? 'between' : 'center' ?>">
                            <a href="ver_evento.php?action=ver&id=<?= $evento['id_evento'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </a>

                            <?php if ($es_administrador): ?>
                                <button class="btn btn-sm btn-outline-warning" onclick="editarEvento(
                                <?= $evento['id_evento'] ?>,
                                        '<?= addslashes($evento['titulo']) ?>',
                                        '<?= addslashes($evento['descripción']) ?>',
                                        '<?= $evento['fecha'] ?>',
                                        '<?= $evento['hora'] ?>',
                                        '<?= addslashes($evento['ubicacion']) ?>',
                                        '<?= addslashes($evento['categoria']) ?>',
                                <?= $evento['precio'] ?>
                                        )">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <a href="../../controllers/EventoController.php?action=eliminar&id=<?= $evento['id_evento'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('¿Estás seguro de eliminar este evento?');">
                                    <i class="bi bi-trash"></i> Eliminar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($es_administrador): ?>
    <!-- Modal Agregar Evento - Only shown to administrators -->
    <div class="modal fade" id="modalAgregarEvento">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Agregar Evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../../controllers/EventoController.php?action=guardar" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="titulo" class="form-label">Título</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <input type="text" class="form-control" id="categoria" name="categoria" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="hora" class="form-label">Hora</label>
                                <input type="time" class="form-control" id="hora" name="hora" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="ubicacion" class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="ubicacion" name="ubicacion" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="precio" class="form-label">Precio ($)</label>
                                <input type="number" step="0.01" class="form-control" id="precio" name="precio" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="imagen" class="form-label">Imagen del Evento</label>
                                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Guardar Evento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Evento - Only shown to administrators -->
    <div class="modal fade" id="modalEditarEvento">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../../controllers/EventoController.php?action=actualizar" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="edit-id" name="id_evento">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-titulo" class="form-label">Título</label>
                                <input type="text" class="form-control" id="edit-titulo" name="titulo" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-categoria" class="form-label">Categoría</label>
                                <input type="text" class="form-control" id="edit-categoria" name="categoria" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit-descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="edit-descripcion" name="descripcion" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="edit-fecha" name="fecha" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-hora" class="form-label">Hora</label>
                                <input type="time" class="form-control" id="edit-hora" name="hora" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit-ubicacion" class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="edit-ubicacion" name="ubicacion" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit-precio" class="form-label">Precio ($)</label>
                                <input type="number" step="0.01" class="form-control" id="edit-precio" name="precio" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit-imagen" class="form-label">Imagen del Evento (Dejar vacío para mantener la actual)</label>
                                <input type="file" class="form-control" id="edit-imagen" name="imagen" accept="image/*">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<footer class="footer mt-5 py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>Ideal Events</h5>
                <p>Plataforma de gestión de eventos</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0">© <?= date('Y') ?> Ideal Events. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($es_administrador): ?>
    <script>
        function editarEvento(id, titulo, descripcion, fecha, hora, ubicacion, categoria, precio) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-titulo').value = titulo;
            document.getElementById('edit-descripcion').value = descripcion;
            document.getElementById('edit-fecha').value = fecha;
            document.getElementById('edit-hora').value = hora;
            document.getElementById('edit-ubicacion').value = ubicacion;
            document.getElementById('edit-categoria').value = categoria;
            document.getElementById('edit-precio').value = precio;

            let modal = new bootstrap.Modal(document.getElementById('modalEditarEvento'));
            modal.show();
        }
    </script>
<?php endif; ?>
</body>
</html>