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
include_once "../../models/PagoModel.php";

// Obtener pagos del usuario
$id_usuario = $_SESSION['id_usuario'];
$pagos = PagoModel::mdlObtenerPagosPorUsuario($id_usuario);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pagos - Ideal Event's</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../views/css/main.css">
    <style>
        .navbar-logo {
            height: 40px !important;
            width: auto !important;
            object-fit: contain !important;
        }
        .payment-status {
            font-weight: bold;
        }
        .payment-status.completed {
            color: #1cc88a;
        }
        .payment-status.pending {
            color: #f6c23e;
        }
        .payment-status.rejected {
            color: #e74a3b;
        }
        .payment-card {
            transition: transform 0.3s;
        }
        .payment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
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
                    <li><a class="dropdown-item" href="perfil_cliente.php"><i class="bi bi-person me-2"></i> Mi Perfil</a></li>
                    <li><a class="dropdown-item active" href="mis_pagos.php"><i class="bi bi-credit-card me-2"></i> Mis Pagos</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/IdealEventsx/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="mb-4"><i class="bi bi-credit-card me-2"></i>Mis Pagos</h1>

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

    <?php if (empty($pagos)): ?>
        <div class="alert alert-info">
            <p class="mb-0">No tienes pagos registrados. Cuando realices una inscripción y su pago, aparecerán aquí.</p>
        </div>
    <?php else: ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Historial de Pagos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>ID Pago</th>
                            <th>Evento</th>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td>#<?= $pago['id_pago'] ?></td>
                                <td><?= htmlspecialchars($pago['evento_titulo']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></td>
                                <td>$<?= number_format($pago['monto'], 2) ?></td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    $statusText = '';

                                    switch ($pago['estado_pago']) {
                                        case 'completado':
                                            $statusClass = 'completed';
                                            $statusText = 'Completado';
                                            break;
                                        case 'pendiente':
                                            $statusClass = 'pending';
                                            $statusText = 'Pendiente';
                                            break;
                                        case 'rechazado':
                                            $statusClass = 'rejected';
                                            $statusText = 'Rechazado';
                                            break;
                                        default:
                                            $statusClass = '';
                                            $statusText = 'Desconocido';
                                    }
                                    ?>
                                    <span class="payment-status <?= $statusClass ?>"><?= $statusText ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalDetallePago<?= $pago['id_pago'] ?>">
                                        <i class="bi bi-info-circle"></i> Detalles
                                    </button>

                                    <?php if ($pago['estado_pago'] === 'completado'): ?>
                                        <a href="#" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-download"></i> Recibo
                                        </a>
                                    <?php elseif ($pago['estado_pago'] === 'pendiente'): ?>
                                        <a href="realizar_pago.php?id=<?= $pago['id_evento'] ?>" class="btn btn-sm btn-warning">
                                            <i class="bi bi-credit-card"></i> Pagar
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Modal de Detalle de Pago -->
                            <div class="modal fade" id="modalDetallePago<?= $pago['id_pago'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detalle de Pago #<?= $pago['id_pago'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="card-body">
                                                <div class="row mb-3">
                                                    <div class="col-sm-4 fw-bold">ID de Pago:</div>
                                                    <div class="col-sm-8">#<?= $pago['id_pago'] ?></div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-sm-4 fw-bold">Evento:</div>
                                                    <div class="col-sm-8"><?= htmlspecialchars($pago['evento_titulo']) ?></div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-sm-4 fw-bold">Fecha del Evento:</div>
                                                    <div class="col-sm-8"><?= date('d/m/Y', strtotime($pago['evento_fecha'])) ?></div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-sm-4 fw-bold">Fecha de Pago:</div>
                                                    <div class="col-sm-8"><?= date('d/m/Y H:i', strtotime($pago['fecha_pago'])) ?></div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-sm-4 fw-bold">Monto:</div>
                                                    <div class="col-sm-8">$<?= number_format($pago['monto'], 2) ?></div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-sm-4 fw-bold">Estado:</div>
                                                    <div class="col-sm-8">
                                                        <span class="payment-status <?= $statusClass ?>"><?= $statusText ?></span>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-4 fw-bold">Método de Pago:</div>
                                                    <div class="col-sm-8">Tarjeta de Crédito (****1234)</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <?php if ($pago['estado_pago'] === 'completado'): ?>
                                                <a href="#" class="btn btn-outline-success">
                                                    <i class="bi bi-download me-1"></i> Descargar Recibo
                                                </a>
                                            <?php elseif ($pago['estado_pago'] === 'pendiente'): ?>
                                                <a href="realizar_pago.php?id=<?= $pago['id_evento'] ?>" class="btn btn-warning">
                                                    <i class="bi bi-credit-card me-1"></i> Completar Pago
                                                </a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 g-4 mb-4">
            <div class="col">
                <div class="card payment-card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>Información de Pagos</h5>
                        <p class="card-text">Aquí puedes ver el historial de todos tus pagos realizados en nuestra plataforma. Los pagos pueden tener los siguientes estados:</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Completado
                                <span class="badge bg-success rounded-pill">Pago procesado correctamente</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Pendiente
                                <span class="badge bg-warning text-dark rounded-pill">Esperando confirmación</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Rechazado
                                <span class="badge bg-danger rounded-pill">Error en el procesamiento</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card payment-card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-question-circle me-2"></i>Preguntas Frecuentes</h5>
                        <div class="accordion" id="accordionFAQ">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                        ¿Cómo solicitar un reembolso?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        Para solicitar un reembolso, debes contactar a nuestro equipo de soporte dentro de las 24 horas posteriores a la realización del pago.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                        ¿Qué métodos de pago aceptan?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        Aceptamos tarjetas de crédito, tarjetas de débito y PayPal como métodos de pago.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                        ¿Los pagos son seguros?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        Sí, todos los pagos se procesan a través de pasarelas seguras con encriptación SSL de 256 bits.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
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