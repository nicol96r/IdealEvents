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
include_once "../../models/EventoModel.php";
include_once "../../models/InscripcionModel.php";

// Verificar que se haya recibido el ID del evento
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php?error=" . urlencode("ID de evento no válido"));
    exit();
}

$id_evento = intval($_GET['id']);
$id_usuario = $_SESSION['id_usuario'];

// Obtener información del evento
$evento = EventoModel::mdlObtenerEventoPorId($id_evento);

if (!$evento) {
    header("Location: dashboard.php?error=" . urlencode("El evento no existe"));
    exit();
}

// Verificar que el usuario esté inscrito
if (!InscripcionModel::mdlVerificarInscripcion($id_usuario, $id_evento)) {
    header("Location: dashboard.php?error=" . urlencode("Debes inscribirte primero para realizar el pago"));
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizar Pago - Ideal Event's</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../views/css/main.css">
    <style>
        .navbar-logo {
            height: 40px !important;
            width: auto !important;
            object-fit: contain !important;
        }
        .payment-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .payment-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .payment-methods {
            margin-bottom: 30px;
        }
        .payment-methods .nav-link {
            color: #495057;
            padding: 15px;
            border: 1px solid #dee2e6;
            margin-right: 10px;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 120px;
            transition: all 0.2s;
        }
        .payment-methods .nav-link i {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
        .payment-methods .nav-link.active {
            background-color: #e9ecef;
            border-color: #4e73df;
            color: #4e73df;
            font-weight: bold;
        }
        .payment-methods .nav-link:hover {
            background-color: #f1f3f5;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        .payment-summary {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-total {
            font-size: 1.25rem;
            font-weight: bold;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
            margin-top: 15px;
        }
        .btn-payment {
            background-color: #4e73df;
            border-color: #4e73df;
            padding: 12px 25px;
            font-size: 1.1rem;
        }
        .btn-payment:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
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
                    <li><a class="dropdown-item" href="mis_pagos.php"><i class="bi bi-credit-card me-2"></i> Mis Pagos</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="/IdealEventsx/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-5 payment-container">
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

    <div class="payment-header text-center">
        <h2 class="mb-3"><i class="bi bi-credit-card me-2"></i>Realizar Pago</h2>
        <p class="lead mb-0">Complete los datos de pago para confirmar su inscripción a:</p>
        <h4 class="mt-2 mb-0"><?= htmlspecialchars($evento['titulo']) ?></h4>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Método de Pago</h5>
                </div>
                <div class="card-body">
                    <!-- Tabs de métodos de pago -->
                    <ul class="nav nav-pills payment-methods mb-4" id="paymentMethods" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="credit-tab" data-bs-toggle="pill" data-bs-target="#credit-content" type="button" role="tab">
                                <i class="bi bi-credit-card"></i>
                                Tarjeta de Crédito
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="debit-tab" data-bs-toggle="pill" data-bs-target="#debit-content" type="button" role="tab">
                                <i class="bi bi-credit-card"></i>
                                Tarjeta de Débito
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="paypal-tab" data-bs-toggle="pill" data-bs-target="#paypal-content" type="button" role="tab">
                                <i class="bi bi-paypal"></i>
                                PayPal
                            </button>
                        </li>
                    </ul>

                    <!-- Contenido de los tabs -->
                    <div class="tab-content" id="paymentContent">
                        <!-- Tarjeta de Crédito -->
                        <div class="tab-pane fade show active" id="credit-content" role="tabpanel">
                            <form action="../../controllers/PagoController.php?action=procesar" method="POST" id="creditForm">
                                <input type="hidden" name="id_evento" value="<?= $evento['id_evento'] ?>">
                                <input type="hidden" name="monto" value="<?= $evento['precio'] ?>">

                                <div class="mb-3">
                                    <label for="cardNumber" class="form-label">Número de Tarjeta</label>
                                    <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19" required>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="expiryDate" class="form-label">Fecha de Expiración</label>
                                        <input type="text" class="form-control" id="expiryDate" placeholder="MM/AA" maxlength="5" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" placeholder="123" maxlength="4" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="cardName" class="form-label">Nombre en la Tarjeta</label>
                                    <input type="text" class="form-control" id="cardName" placeholder="NOMBRE APELLIDO" required>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="saveCard">
                                    <label class="form-check-label" for="saveCard">
                                        Guardar esta tarjeta para futuros pagos
                                    </label>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-payment">Realizar Pago</button>
                                </div>
                            </form>
                        </div>

                        <!-- Tarjeta de Débito -->
                        <div class="tab-pane fade" id="debit-content" role="tabpanel">
                            <form action="../../controllers/PagoController.php?action=procesar" method="POST" id="debitForm">
                                <input type="hidden" name="id_evento" value="<?= $evento['id_evento'] ?>">
                                <input type="hidden" name="monto" value="<?= $evento['precio'] ?>">

                                <div class="mb-3">
                                    <label for="debitCardNumber" class="form-label">Número de Tarjeta</label>
                                    <input type="text" class="form-control" id="debitCardNumber" placeholder="1234 5678 9012 3456" maxlength="19" required>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="debitExpiryDate" class="form-label">Fecha de Expiración</label>
                                        <input type="text" class="form-control" id="debitExpiryDate" placeholder="MM/AA" maxlength="5" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="debitCvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="debitCvv" placeholder="123" maxlength="4" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="debitCardName" class="form-label">Nombre en la Tarjeta</label>
                                    <input type="text" class="form-control" id="debitCardName" placeholder="NOMBRE APELLIDO" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-payment">Realizar Pago</button>
                                </div>
                            </form>
                        </div>

                        <!-- PayPal -->
                        <div class="tab-pane fade" id="paypal-content" role="tabpanel">
                            <div class="text-center mb-4">
                                <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg" alt="PayPal" class="img-fluid" style="height: 80px;">
                                <p class="mt-3">Serás redirigido a PayPal para completar el pago de manera segura.</p>
                            </div>

                            <form action="../../controllers/PagoController.php?action=procesar" method="POST" id="paypalForm">
                                <input type="hidden" name="id_evento" value="<?= $evento['id_evento'] ?>">
                                <input type="hidden" name="monto" value="<?= $evento['precio'] ?>">
                                <input type="hidden" name="payment_method" value="paypal">

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-payment">Continuar a PayPal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Resumen de Pago</h5>
                </div>
                <div class="card-body payment-summary">
                    <div class="summary-item">
                        <span>Evento:</span>
                        <span><?= htmlspecialchars($evento['titulo']) ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Fecha:</span>
                        <span><?= date('d/m/Y', strtotime($evento['fecha'])) ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Hora:</span>
                        <span><?= htmlspecialchars($evento['hora']) ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Precio:</span>
                        <span>$<?= number_format($evento['precio'], 2) ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Impuestos:</span>
                        <span>$0.00</span>
                    </div>
                    <div class="summary-total">
                        <span>Total:</span>
                        <span>$<?= number_format($evento['precio'], 2) ?></span>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-lock-fill text-success me-2"></i>
                        <small>Tus datos están protegidos con encriptación de 256 bits.</small>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Información de Contacto</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>¿Tienes preguntas sobre tu pago?</strong></p>
                    <p class="mb-3">Nuestro equipo de atención al cliente está disponible para ayudarte.</p>
                    <p class="mb-2"><i class="bi bi-envelope-fill me-2"></i> soporte@idealevents.com</p>
                    <p class="mb-0"><i class="bi bi-telephone-fill me-2"></i> (123) 456-7890</p>
                </div>
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
<script>
    // Formatear número de tarjeta automáticamente
    document.getElementById('cardNumber').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '');
        let newVal = '';
        for (let i = 0; i < val.length; i++) {
            if (i > 0 && i % 4 === 0) {
                newVal += ' ';
            }
            newVal += val[i];
        }
        e.target.value = newVal;
    });

    document.getElementById('debitCardNumber').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '');
        let newVal = '';
        for (let i = 0; i < val.length; i++) {
            if (i > 0 && i % 4 === 0) {
                newVal += ' ';
            }
            newVal += val[i];
        }
        e.target.value = newVal;
    });

    // Formatear fecha de expiración
    document.getElementById('expiryDate').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '');
        if (val.length > 2) {
            e.target.value = val.substring(0, 2) + '/' + val.substring(2, 4);
        } else {
            e.target.value = val;
        }
    });

    document.getElementById('debitExpiryDate').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '');
        if (val.length > 2) {
            e.target.value = val.substring(0, 2) + '/' + val.substring(2, 4);
        } else {
            e.target.value = val;
        }
    });

    // Validar formulario antes de enviar
    document.getElementById('creditForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // En un sistema real, aquí validaríamos los datos de la tarjeta
        // Para este ejemplo, simplemente enviamos el formulario
        this.submit();
    });

    document.getElementById('debitForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // En un sistema real, aquí validaríamos los datos de la tarjeta
        // Para este ejemplo, simplemente enviamos el formulario
        this.submit();
    });

    document.getElementById('paypalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        // En un sistema real, aquí redireccionaríamos a PayPal
        // Para este ejemplo, simplemente enviamos el formulario
        this.submit();
    });
</script>
</body>
</html>