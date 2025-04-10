<?php
session_start();
// Evitar que el navegador cachee esta página
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Si es admin, redirigir al dashboard de admin
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit();
}

// Líneas corregidas para dashboard.php en la carpeta cliente
include_once "../../models/EventoModel.php";
include_once "../../models/InscripcionModel.php";

// Obtener parámetros de búsqueda y filtrado
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
$mes = isset($_GET['mes']) ? trim($_GET['mes']) : '';
$precio = isset($_GET['precio']) ? trim($_GET['precio']) : '';

// Determinar si hay filtros activos
$filtrosActivos = !empty($busqueda) ||
    (!empty($categoria) && $categoria != 'Categoría') ||
    (!empty($mes) && $mes != 'Mes') ||
    (!empty($precio) && $precio != 'Precio');

// Obtener eventos según la búsqueda y filtros
if ($filtrosActivos) {
    $eventos = EventoModel::mdlBuscarEventos($busqueda, $categoria, $mes, $precio);
} else {
    $eventos = EventoModel::mdlListarEventos();
}

// Obtener categorías disponibles para el filtro
$categorias = EventoModel::mdlObtenerCategorias();

// Obtener inscripciones del usuario actual
$misInscripciones = InscripcionModel::mdlObtenerInscripcionesPorUsuario($_SESSION['id_usuario']);

// Convertir array de inscripciones a un formato más fácil de usar
$eventosInscritos = [];
foreach ($misInscripciones as $inscripcion) {
    $eventosInscritos[] = $inscripcion['id_evento'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ideal Event's - Portal de Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/main.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <style>
        .navbar-logo {
            height: 40px !important;
            width: auto !important;
            object-fit: contain !important;
        }
        .evento-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .evento-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .evento-img {
            height: 200px;
            object-fit: cover;
        }
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../../public/img/eventos/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 40px;
        }
        .badge-inscrito {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .filters {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
        .btn-success {
            background-color: #1cc88a;
            border-color: #1cc88a;
        }
        .btn-success:hover {
            background-color: #169b6b;
            border-color: #169b6b;
        }
        .btn-danger {
            background-color: #e74a3b;
            border-color: #e74a3b;
        }
        .btn-danger:hover {
            background-color: #be2617;
            border-color: #be2617;
        }
        .social-icons a {
            margin-right: 15px;
            font-size: 1.2rem;
            color: #666;
        }
        .social-icons a:hover {
            color: #4e73df;
        }
        .filter-tag {
            display: inline-block;
            background-color: #4e73df;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            margin-right: 5px;
            margin-bottom: 5px;
            font-size: 0.85rem;
        }
        .filter-tag i {
            cursor: pointer;
            margin-left: 5px;
        }
        .filter-tag i:hover {
            opacity: 0.8;
        }
        .active-filters {
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .modal-evento-img {
            max-height: 300px;
            object-fit: cover;
            width: 100%;
            border-radius: 5px;
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
                    <a class="nav-link active" href="dashboard.php"><i class="bi bi-house-door me-1"></i> Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#eventos"><i class="bi bi-calendar-event me-1"></i> Eventos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#mis-inscripciones"><i class="bi bi-bookmark-check me-1"></i> Mis Inscripciones</a>
                </li>
            </ul>
            <div class="dropdown ms-auto">
                <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="perfil_cliente.php"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
                    <li><a class="dropdown-item" href="mis_pagos.php"><i class="bi bi-credit-card me-2"></i> Mis Pagos</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">Descubre Eventos Increíbles</h1>
        <p class="lead mb-5">Encuentra, inscríbete y disfruta de los mejores eventos. ¡Tu próxima experiencia está a un clic de distancia!</p>
        <a href="#eventos" class="btn btn-primary btn-lg px-4 me-2"><i class="bi bi-search me-2"></i>Explorar Eventos</a>
        <a href="#mis-inscripciones" class="btn btn-outline-light btn-lg px-4"><i class="bi bi-bookmark-check me-2"></i>Mis Inscripciones</a>
    </div>
</section>

<!-- Main Content -->
<div class="container">
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

    <!-- Búsqueda y Filtros -->
    <div class="filters shadow-sm" id="eventos">
        <div class="row">
            <div class="col-md-6">
                <h2 class="mb-4"><i class="bi bi-calendar-event me-2"></i>Eventos Disponibles</h2>
            </div>
            <div class="col-md-6">
                <form class="d-flex" method="GET" action="dashboard.php">
                    <input class="form-control me-2" type="search" name="busqueda" placeholder="Buscar eventos..." aria-label="Buscar"
                           value="<?= htmlspecialchars($busqueda) ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>

        <form method="GET" action="dashboard.php" id="formFiltros">
            <!-- Campo oculto para mantener la búsqueda si existe -->
            <?php if (!empty($busqueda)): ?>
                <input type="hidden" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>">
            <?php endif; ?>

            <div class="row mt-3">
                <div class="col-md-3 mb-2">
                    <select class="form-select" name="categoria" id="categoria">
                        <option value="">Categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>" <?= ($cat == $categoria) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-select" name="mes" id="mes">
                        <option value="">Mes</option>
                        <option value="Enero" <?= ($mes == 'Enero') ? 'selected' : '' ?>>Enero</option>
                        <option value="Febrero" <?= ($mes == 'Febrero') ? 'selected' : '' ?>>Febrero</option>
                        <option value="Marzo" <?= ($mes == 'Marzo') ? 'selected' : '' ?>>Marzo</option>
                        <option value="Abril" <?= ($mes == 'Abril') ? 'selected' : '' ?>>Abril</option>
                        <option value="Mayo" <?= ($mes == 'Mayo') ? 'selected' : '' ?>>Mayo</option>
                        <option value="Junio" <?= ($mes == 'Junio') ? 'selected' : '' ?>>Junio</option>
                        <option value="Julio" <?= ($mes == 'Julio') ? 'selected' : '' ?>>Julio</option>
                        <option value="Agosto" <?= ($mes == 'Agosto') ? 'selected' : '' ?>>Agosto</option>
                        <option value="Septiembre" <?= ($mes == 'Septiembre') ? 'selected' : '' ?>>Septiembre</option>
                        <option value="Octubre" <?= ($mes == 'Octubre') ? 'selected' : '' ?>>Octubre</option>
                        <option value="Noviembre" <?= ($mes == 'Noviembre') ? 'selected' : '' ?>>Noviembre</option>
                        <option value="Diciembre" <?= ($mes == 'Diciembre') ? 'selected' : '' ?>>Diciembre</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-select" name="precio" id="precio">
                        <option value="">Precio</option>
                        <option value="Menos de $25" <?= ($precio == 'Menos de $25') ? 'selected' : '' ?>>Menos de $25</option>
                        <option value="$25 - $50" <?= ($precio == '$25 - $50') ? 'selected' : '' ?>>$25 - $50</option>
                        <option value="$50 - $100" <?= ($precio == '$50 - $100') ? 'selected' : '' ?>>$50 - $100</option>
                        <option value="Más de $100" <?= ($precio == 'Más de $100') ? 'selected' : '' ?>>Más de $100</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </div>
        </form>

        <!-- Mostrar filtros activos -->
        <?php if ($filtrosActivos): ?>
            <div class="active-filters">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Filtros activos:</strong>
                        <?php if (!empty($busqueda)): ?>
                            <span class="filter-tag">
                                Búsqueda: <?= htmlspecialchars($busqueda) ?>
                                <i class="bi bi-x-circle" data-filter="busqueda"></i>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($categoria) && $categoria != 'Categoría'): ?>
                            <span class="filter-tag">
                                Categoría: <?= htmlspecialchars($categoria) ?>
                                <i class="bi bi-x-circle" data-filter="categoria"></i>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($mes) && $mes != 'Mes'): ?>
                            <span class="filter-tag">
                                Mes: <?= htmlspecialchars($mes) ?>
                                <i class="bi bi-x-circle" data-filter="mes"></i>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($precio) && $precio != 'Precio'): ?>
                            <span class="filter-tag">
                                Precio: <?= htmlspecialchars($precio) ?>
                                <i class="bi bi-x-circle" data-filter="precio"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Limpiar filtros
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Resultados de la búsqueda -->
        <?php if ($filtrosActivos): ?>
            <div class="mt-3">
                <p class="text-muted">
                    Se encontraron <?= count($eventos) ?> eventos que coinciden con tu búsqueda.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Eventos -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
        <?php if (empty($eventos)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <?php if ($filtrosActivos): ?>
                        No se encontraron eventos que coincidan con tu búsqueda. Intenta con otros filtros.
                    <?php else: ?>
                        No hay eventos disponibles actualmente. ¡Vuelve a revisar pronto!
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($eventos as $evento): ?>
                <div class="col">
                    <div class="card h-100 evento-card">
                        <?php if (in_array($evento['id_evento'], $eventosInscritos)): ?>
                            <div class="badge-inscrito">
                                <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Inscrito</span>
                            </div>
                        <?php endif; ?>
                        <img src="../../public/img/eventos/<?= htmlspecialchars($evento['imagen_nombre'] ?? 'destacado1.jpg') ?>"
                             class="card-img-top evento-img" alt="<?= htmlspecialchars($evento['titulo']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($evento['titulo']) ?></h5>
                            <p class="card-text text-muted">
                                <i class="bi bi-calendar-event"></i> <?= date('d/m/Y', strtotime($evento['fecha'])) ?>
                                <i class="bi bi-clock ms-2"></i> <?= htmlspecialchars($evento['hora']) ?>
                            </p>
                            <p class="card-text"><?= htmlspecialchars(substr($evento['descripcion'], 0, 100)) ?>...</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary"><?= htmlspecialchars($evento['categoria']) ?></span>
                                <span class="fw-bold">$<?= number_format($evento['precio'], 2) ?></span>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary btn-ver-evento"
                                        data-id="<?= $evento['id_evento'] ?>"
                                        data-titulo="<?= htmlspecialchars($evento['titulo']) ?>"
                                        data-descripcion="<?= htmlspecialchars($evento['descripcion']) ?>"
                                        data-fecha="<?= date('d/m/Y', strtotime($evento['fecha'])) ?>"
                                        data-hora="<?= htmlspecialchars($evento['hora']) ?>"
                                        data-ubicacion="<?= htmlspecialchars($evento['ubicacion']) ?>"
                                        data-categoria="<?= htmlspecialchars($evento['categoria']) ?>"
                                        data-precio="<?= number_format($evento['precio'], 2) ?>"
                                        data-imagen="<?= htmlspecialchars($evento['imagen_nombre'] ?? 'destacado1.jpg') ?>">
                                    <i class="bi bi-info-circle"></i> Detalles del evento
                                </button>
                                <?php if (in_array($evento['id_evento'], $eventosInscritos)): ?>
                                    <button class="btn btn-outline-danger btn-cancelar-inscripcion"
                                            data-id="<?= $evento['id_evento'] ?>"
                                            data-tipo="evento">
                                        <i class="bi bi-x-circle"></i> Cancelar Inscripción
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-success btn-inscribir" data-id="<?= $evento['id_evento'] ?>">
                                        <i class="bi bi-bookmark-plus"></i> Inscribirme
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Mis Inscripciones -->
    <h2 class="mb-4" id="mis-inscripciones"><i class="bi bi-bookmark-check me-2"></i>Mis Inscripciones</h2>

    <?php if (empty($misInscripciones)): ?>
        <div class="alert alert-info">
            <p>No tienes inscripciones activas. Explora nuestros eventos y ¡inscríbete en los que te interesen!</p>
        </div>
    <?php else: ?>
        <div class="table-responsive shadow-sm mb-5">
            <table class="table table-hover">
                <thead class="table-dark">
                <tr>
                    <th>Evento</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Precio</th>
                    <th>Estado de Pago</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <!-- Tabla de inscripciones corregida -->
                <tbody>
                <?php foreach ($misInscripciones as $inscripcion):
                    $evento = EventoModel::mdlObtenerEventoPorId($inscripcion['id_evento']);
                    $estadoPago = isset($inscripcion['estado_pago']) ? $inscripcion['estado_pago'] : 'pendiente';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($evento['titulo']) ?></td>
                        <td><?= date('d/m/Y', strtotime($evento['fecha'])) ?></td>
                        <td><?= htmlspecialchars($evento['hora']) ?></td>
                        <td>$<?= number_format($evento['precio'], 2) ?></td>
                        <td>
                            <?php if ($estadoPago === 'completado'): ?>
                                <span class="badge bg-success">Pagado</span>
                            <?php elseif ($estadoPago === 'rechazado'): ?>
                                <span class="badge bg-danger">Rechazado</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-ver-evento"
                                    data-id="<?= $evento['id_evento'] ?>"
                                    data-titulo="<?= htmlspecialchars($evento['titulo']) ?>"
                                    data-descripcion="<?= htmlspecialchars($evento['descripcion']) ?>"
                                    data-fecha="<?= date('d/m/Y', strtotime($evento['fecha'])) ?>"
                                    data-hora="<?= htmlspecialchars($evento['hora']) ?>"
                                    data-ubicacion="<?= htmlspecialchars($evento['ubicacion']) ?>"
                                    data-categoria="<?= htmlspecialchars($evento['categoria']) ?>"
                                    data-precio="<?= number_format($evento['precio'], 2) ?>"
                                    data-imagen="<?= htmlspecialchars($evento['imagen_nombre'] ?? 'destacado1.jpg') ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if ($estadoPago !== 'completado'): ?>
                                <button class="btn btn-sm btn-success btn-pagar-evento"
                                        data-id="<?= $evento['id_evento'] ?>"
                                        data-titulo="<?= htmlspecialchars($evento['titulo']) ?>"
                                        data-precio="<?= number_format($evento['precio'], 2) ?>">
                                    <i class="bi bi-credit-card"></i>
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-danger btn-cancelar-inscripcion"
                                    data-id="<?= $inscripcion['id_inscripcion'] ?>"
                                    data-tipo="inscripcion">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Próximos Eventos Destacados -->
    <h2 class="mb-4"><i class="bi bi-star me-2"></i>Eventos Destacados</h2>
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card mb-3 shadow-sm">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="../../public/img/eventos/destacado1.jpg" class="img-fluid rounded-start h-100 w-100 object-fit-cover" alt="Evento destacado">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title">Concierto de Rock</h5>
                            <p class="card-text">Una experiencia musical inolvidable con las mejores bandas del momento.</p>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="bi bi-calendar-event"></i> 15/12/2023
                                    <i class="bi bi-clock ms-2"></i> 20:00
                                </small>
                            </p>
                            <button class="btn btn-sm btn-primary btn-ver-evento-destacado"
                                    data-titulo="Concierto de Rock"
                                    data-descripcion="Una experiencia musical inolvidable con las mejores bandas del momento. Disfruta de una noche llena de buena música y energía positiva."
                                    data-fecha="15/12/2023"
                                    data-hora="20:00"
                                    data-ubicacion="Estadio Nacional"
                                    data-categoria="Concierto"
                                    data-precio="50.00"
                                    data-imagen="destacado1.jpg">
                                Ver detalles
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3 shadow-sm">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="../../public/img/eventos/destacado2.jpg" class="img-fluid rounded-start h-100 w-100 object-fit-cover" alt="Evento destacado">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title">Feria Tecnológica</h5>
                            <p class="card-text">Descubre las últimas innovaciones en tecnología y participa en talleres.</p>
                            <p class="card-text">
                                <small class="text-muted">
                                    <i class="bi bi-calendar-event"></i> 22/12/2023
                                    <i class="bi bi-clock ms-2"></i> 10:00
                                </small>
                            </p>
                            <button class="btn btn-sm btn-primary btn-ver-evento-destacado"
                                    data-titulo="Feria Tecnológica"
                                    data-descripcion="Descubre las últimas innovaciones en tecnología y participa en talleres interactivos. Un espacio para conocer lo último del mundo tech."
                                    data-fecha="22/12/2023"
                                    data-hora="10:00"
                                    data-ubicacion="Centro de Convenciones"
                                    data-categoria="Tecnología"
                                    data-precio="15.00"
                                    data-imagen="destacado2.jpg">
                                Ver detalles
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Newsletter Subscription -->
<section class="bg-light py-5" id="newsletter">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="mb-4">¡Mantente Informado!</h2>
                <p class="lead mb-4">Suscríbete a nuestro boletín para recibir notificaciones sobre nuevos eventos y promociones exclusivas.</p>
                <form id="newsletterForm" class="w-75 mx-auto">
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" id="newsletterEmail" placeholder="Tu correo electrónico" aria-label="Email" required>
                        <button class="btn btn-primary" type="submit" id="btnSuscribir">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerNewsletter"></span>
                            <span id="btnText">Suscribirme</span>
                        </button>
                    </div>
                </form>
                <div id="newsletterMessage" class="mt-3"></div>
                <p class="text-muted"><small>No compartimos tu correo electrónico con nadie.</small></p>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5>Ideal Event's</h5>
                <p class="text-muted">Tu plataforma confiable para descubrir y participar en los mejores eventos.</p>
                <div class="social-icons mt-3">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-twitter"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
            <div class="col-md-2 mb-4 mb-md-0">
                <h5>Enlaces</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-muted">Inicio</a></li>
                    <li><a href="#" class="text-muted">Eventos</a></li>
                    <li><a href="#" class="text-muted">Inscripciones</a></li>
                    <li><a href="#" class="text-muted">Contacto</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <h5>Contacto</h5>
                <ul class="list-unstyled text-muted">
                    <li><i class="bi bi-geo-alt-fill me-2"></i> Calle Principal 123</li>
                    <li><i class="bi bi-telephone-fill me-2"></i> (123) 456-7890</li>
                    <li><i class="bi bi-envelope-fill me-2"></i> info@idealevents.com</li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Legal</h5>
                <ul class="list-unstyled text-muted">
                    <li><a href="#">Términos y Condiciones</a></li>
                    <li><a href="#">Política de Privacidad</a></li>
                    <li><a href="#">Política de Reembolso</a></li>
                </ul>
            </div>
        </div>
        <hr class="my-4 bg-secondary">
        <div class="text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> Ideal Event's. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<!-- Modal Ver Evento -->
<div class="modal fade" id="modalVerEvento" tabindex="-1" aria-labelledby="modalVerEventoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalVerEventoLabel"><i class="bi bi-info-circle me-2"></i><span id="modal-evento-titulo"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <img id="modal-evento-imagen" src="" class="modal-evento-img" alt="Imagen del evento">
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="mb-3">
                            <span class="badge bg-primary" id="modal-evento-categoria"></span>
                            <span class="badge bg-secondary ms-2">$<span id="modal-evento-precio"></span></span>
                        </div>
                        <p><i class="bi bi-calendar-event me-2"></i><strong>Fecha:</strong> <span id="modal-evento-fecha"></span></p>
                        <p><i class="bi bi-clock me-2"></i><strong>Hora:</strong> <span id="modal-evento-hora"></span></p>
                        <p><i class="bi bi-geo-alt me-2"></i><strong>Ubicación:</strong> <span id="modal-evento-ubicacion"></span></p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h5>Descripción</h5>
                        <p id="modal-evento-descripcion"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="modal-btn-inscribir">Inscribirme</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Realizar Pago -->
<div class="modal fade" id="modalRealizarPago" tabindex="-1" aria-labelledby="modalRealizarPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalRealizarPagoLabel"><i class="bi bi-credit-card me-2"></i>Realizar Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <h4>Detalles del Pago</h4>
                    <p>Estás a punto de realizar el pago para el evento:</p>
                    <h5 id="modal-pago-titulo" class="fw-bold"></h5>
                    <p>Monto a pagar: <span class="badge bg-primary fs-5">$<span id="modal-pago-monto"></span></span></p>
                </div>

                <form id="formPago" action="../../controllers/PagoController.php?action=procesar" method="POST">
                    <input type="hidden" id="modal-pago-id-evento" name="id_evento">
                    <input type="hidden" id="modal-pago-precio" name="monto">

                    <div class="mb-3">
                        <label for="tarjeta" class="form-label">Número de Tarjeta</label>
                        <input type="text" class="form-control" id="tarjeta" placeholder="1234 5678 9012 3456" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="vencimiento" class="form-label">Fecha de Vencimiento</label>
                            <input type="text" class="form-control" id="vencimiento" placeholder="MM/AA" required>
                        </div>
                        <div class="col-md-6">
                            <label for="cvv" class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cvv" placeholder="123" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nombre_tarjeta" class="form-label">Nombre en la Tarjeta</label>
                        <input type="text" class="form-control" id="nombre_tarjeta" placeholder="NOMBRE APELLIDO" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="guardar_tarjeta">
                        <label class="form-check-label" for="guardar_tarjeta">Guardar esta tarjeta para futuros pagos</label>
                    </div>

                    <div class="alert alert-info">
                        <small><i class="bi bi-shield-lock"></i> Tus datos están protegidos con encriptación de 256 bits.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-realizar-pago">
                    <i class="bi bi-credit-card me-1"></i> Realizar Pago
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS y SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
    $(document).ready(function() {
        // Manejar el clic en el botón de ver detalles de evento
        $('.btn-ver-evento, .btn-ver-evento-destacado').on('click', function() {
            const id = $(this).data('id');
            const titulo = $(this).data('titulo');
            const descripcion = $(this).data('descripcion');
            const fecha = $(this).data('fecha');
            const hora = $(this).data('hora');
            const ubicacion = $(this).data('ubicacion');
            const categoria = $(this).data('categoria');
            const precio = $(this).data('precio');
            const imagen = $(this).data('imagen');

            $('#modal-evento-titulo').text(titulo);
            $('#modal-evento-descripcion').text(descripcion);
            $('#modal-evento-fecha').text(fecha);
            $('#modal-evento-hora').text(hora);
            $('#modal-evento-ubicacion').text(ubicacion);
            $('#modal-evento-categoria').text(categoria);
            $('#modal-evento-precio').text(precio);
            $('#modal-evento-imagen').attr('src', '../../public/img/eventos/' + imagen);

            // Actualizar botón de inscripción
            $('#modal-btn-inscribir').data('id', id);
            $('#modal-btn-inscribir').show();

            // Comprobar si el usuario ya está inscrito
            const eventosInscritos = <?= json_encode($eventosInscritos) ?>;
            if (eventosInscritos.includes(parseInt(id))) {
                $('#modal-btn-inscribir').hide();
            }

            $('#modalVerEvento').modal('show');
        });

        // Manejar el clic en el botón de inscripción desde el modal
        $('#modal-btn-inscribir').on('click', function() {
            const idEvento = $(this).data('id');
            $('#modalVerEvento').modal('hide');

            // Mostrar confirmación
            Swal.fire({
                title: '¿Confirmar inscripción?',
                text: "¿Deseas inscribirte a este evento?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, inscribirme',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Realizar la petición AJAX para inscribirse
                    $.ajax({
                        url: '../../controllers/InscripcionController.php?action=inscribir',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id_evento: idEvento
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Procesando...',
                                text: 'Estamos procesando tu inscripción',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                // Mostrar mensaje de éxito
                                Swal.fire({
                                    title: '¡Inscripción exitosa!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonColor: '#1cc88a'
                                }).then(() => {
                                    // Recargar la página para mostrar la inscripción
                                    location.reload();
                                });
                            } else {
                                // Mostrar mensaje de error
                                Swal.fire({
                                    title: 'Error',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonColor: '#4e73df'
                                });
                            }
                        },
                        error: function() {
                            // Mostrar mensaje de error
                            Swal.fire({
                                title: 'Error de conexión',
                                text: 'No se pudo completar la solicitud. Intenta nuevamente.',
                                icon: 'error',
                                confirmButtonColor: '#4e73df'
                            });
                        }
                    });
                }
            });
        });

        // Manejar el clic en el botón de realizar pago
        $('.btn-pagar-evento').on('click', function() {
            const idEvento = $(this).data('id');
            const titulo = $(this).data('titulo');
            const precio = $(this).data('precio');

            $('#modal-pago-titulo').text(titulo);
            $('#modal-pago-monto').text(precio);
            $('#modal-pago-id-evento').val(idEvento);
            $('#modal-pago-precio').val(precio);

            $('#modalRealizarPago').modal('show');
        });

        // Manejar el clic en el botón de realizar pago del modal
        $('#btn-realizar-pago').on('click', function() {
            // Validar el formulario
            const formPago = document.getElementById('formPago');
            if (formPago.checkValidity()) {
                // Enviar el formulario
                formPago.submit();
            } else {
                // Mostrar un mensaje de validación
                Swal.fire({
                    title: 'Formulario incompleto',
                    text: 'Por favor, completa todos los campos requeridos',
                    icon: 'warning',
                    confirmButtonColor: '#4e73df'
                });
                // Marcar campos inválidos
                formPago.reportValidity();
            }
        });

        // Manejar la inscripción a eventos
        $('.btn-inscribir').on('click', function() {
            const idEvento = $(this).data('id');
            const btn = $(this);

            // Mostrar confirmación
            Swal.fire({
                title: '¿Confirmar inscripción?',
                text: "¿Deseas inscribirte a este evento?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, inscribirme',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Realizar la petición AJAX para inscribirse
                    $.ajax({
                        url: '../../controllers/InscripcionController.php?action=inscribir',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id_evento: idEvento
                        },
                        beforeSend: function() {
                            // Deshabilitar el botón durante la petición
                            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                // Mostrar mensaje de éxito
                                Swal.fire({
                                    title: '¡Inscripción exitosa!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonColor: '#1cc88a'
                                }).then(() => {
                                    // Recargar la página para mostrar la inscripción
                                    location.reload();
                                });
                            } else {
                                // Mostrar mensaje de error
                                Swal.fire({
                                    title: 'Error',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonColor: '#4e73df'
                                });
                                // Habilitar el botón nuevamente
                                btn.prop('disabled', false).html('<i class="bi bi-bookmark-plus"></i> Inscribirme');
                            }
                        },
                        error: function() {
                            // Mostrar mensaje de error
                            Swal.fire({
                                title: 'Error de conexión',
                                text: 'No se pudo completar la solicitud. Intenta nuevamente.',
                                icon: 'error',
                                confirmButtonColor: '#4e73df'
                            });
                            // Habilitar el botón nuevamente
                            btn.prop('disabled', false).html('<i class="bi bi-bookmark-plus"></i> Inscribirme');
                        }
                    });
                }
            });
        });

        // Manejar la cancelación de inscripciones
        $('.btn-cancelar-inscripcion').on('click', function() {
            const id = $(this).data('id');
            const tipo = $(this).data('tipo');
            const btn = $(this);

            // Mostrar confirmación
            Swal.fire({
                title: '¿Cancelar inscripción?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#4e73df',
                confirmButtonText: 'Sí, cancelar',
                cancelButtonText: 'No, conservar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Realizar la petición AJAX para cancelar inscripción
                    $.ajax({
                        url: '../../controllers/InscripcionController.php?action=cancelar',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: id,
                            tipo: tipo
                        },
                        beforeSend: function() {
                            // Deshabilitar el botón durante la petición
                            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                // Mostrar mensaje de éxito
                                Swal.fire({
                                    title: 'Inscripción cancelada',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonColor: '#4e73df'
                                }).then(() => {
                                    // Recargar la página
                                    location.reload();
                                });
                            } else {
                                // Mostrar mensaje de error
                                Swal.fire({
                                    title: 'Error',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonColor: '#4e73df'
                                });
                                // Habilitar el botón nuevamente
                                btn.prop('disabled', false).html('<i class="bi bi-x-circle"></i>');
                            }
                        },
                        error: function() {
                            // Mostrar mensaje de error
                            Swal.fire({
                                title: 'Error de conexión',
                                text: 'No se pudo completar la solicitud. Intenta nuevamente.',
                                icon: 'error',
                                confirmButtonColor: '#4e73df'
                            });
                            // Habilitar el botón nuevamente
                            btn.prop('disabled', false).html('<i class="bi bi-x-circle"></i>');
                        }
                    });
                }
            });
        });

        // Remover filtros individuales
        $('.filter-tag i').on('click', function() {
            const filtro = $(this).data('filter');

            // Crear un nuevo formulario con los filtros actuales excepto el que se elimina
            let form = document.createElement('form');
            form.method = 'GET';
            form.action = 'dashboard.php';

            <?php if (!empty($busqueda)): ?>
            if (filtro !== 'busqueda') {
                let inputBusqueda = document.createElement('input');
                inputBusqueda.type = 'hidden';
                inputBusqueda.name = 'busqueda';
                inputBusqueda.value = '<?= htmlspecialchars($busqueda) ?>';
                form.appendChild(inputBusqueda);
            }
            <?php endif; ?>

            <?php if (!empty($categoria) && $categoria != 'Categoría'): ?>
            if (filtro !== 'categoria') {
                let inputCategoria = document.createElement('input');
                inputCategoria.type = 'hidden';
                inputCategoria.name = 'categoria';
                inputCategoria.value = '<?= htmlspecialchars($categoria) ?>';
                form.appendChild(inputCategoria);
            }
            <?php endif; ?>

            <?php if (!empty($mes) && $mes != 'Mes'): ?>
            if (filtro !== 'mes') {
                let inputMes = document.createElement('input');
                inputMes.type = 'hidden';
                inputMes.name = 'mes';
                inputMes.value = '<?= htmlspecialchars($mes) ?>';
                form.appendChild(inputMes);
            }
            <?php endif; ?>

            <?php if (!empty($precio) && $precio != 'Precio'): ?>
            if (filtro !== 'precio') {
                let inputPrecio = document.createElement('input');
                inputPrecio.type = 'hidden';
                inputPrecio.name = 'precio';
                inputPrecio.value = '<?= htmlspecialchars($precio) ?>';
                form.appendChild(inputPrecio);
            }
            <?php endif; ?>

            document.body.appendChild(form);
            form.submit();
        });

        // Seleccionar filtro y enviar automáticamente
        $('#categoria, #mes, #precio').on('change', function() {
            $('#formFiltros').submit();
        });

        // Mostrar mensaje de success o error si viene de PHP por variable de sesión
        <?php if (isset($_SESSION['success'])): ?>
        Swal.fire({
            title: '¡Éxito!',
            text: '<?= htmlspecialchars($_SESSION['success']) ?>',
            icon: 'success',
            confirmButtonColor: '#1cc88a'
        });
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        Swal.fire({
            title: 'Error',
            text: '<?= htmlspecialchars($_SESSION['error']) ?>',
            icon: 'error',
            confirmButtonColor: '#e74a3b'
        });
        <?php endif; ?>

        // Manejar el formulario de suscripción al boletín
        $('#newsletterForm').on('submit', function(e) {
            e.preventDefault();

            const email = $('#newsletterEmail').val().trim();
            const btnText = $('#btnText');
            const spinner = $('#spinnerNewsletter');
            const btnSuscribir = $('#btnSuscribir');

            if (email === '') {
                $('#newsletterMessage').html('<div class="alert alert-danger">Por favor, introduce un correo electrónico válido.</div>');
                return false;
            }

            // Mostrar spinner y deshabilitar botón
            btnText.text('Enviando...');
            spinner.removeClass('d-none');
            btnSuscribir.prop('disabled', true);

            // Enviar solicitud AJAX
            $.ajax({
                url: '../../controllers/NewsletterController.php',
                type: 'POST',
                dataType: 'json',
                data: { email: email },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#newsletterMessage').html('<div class="alert alert-success">' + response.message + '</div>');
                        $('#newsletterEmail').val(''); // Limpiar el campo
                    } else {
                        $('#newsletterMessage').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#newsletterMessage').html('<div class="alert alert-danger">Error de conexión. Por favor, inténtalo de nuevo más tarde.</div>');
                },
                complete: function() {
                    // Restaurar botón
                    btnText.text('Suscribirme');
                    spinner.addClass('d-none');
                    btnSuscribir.prop('disabled', false);
                }
            });
        });

        // Formatear campos del formulario de pago
        $('#tarjeta').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            let formattedValue = '';

            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value.charAt(i);
            }

            $(this).val(formattedValue);
        });

        $('#vencimiento').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');

            if (value.length > 0) {
                if (value.length <= 2) {
                    $(this).val(value);
                } else {
                    let mes = value.substring(0, 2);
                    let ano = value.substring(2, 4);
                    $(this).val(mes + '/' + ano);
                }
            }
        });

        $('#cvv').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            $(this).val(value);
        });
    });
</script>
</body>
</html>