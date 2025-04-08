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

// Para dashboard de admin
if ($_SESSION['rol'] !== 'admin') {
    header("Location: /IdealEventsx/views/dashboard.php");
    exit();
}

// Para dashboard de cliente
// if ($_SESSION['rol'] !== 'cliente') {
//     header("Location: /IdealEventsx/views/admin/dashboard.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Ideal Event's</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/main.css">
    <style>
        .navbar-logo {
            height: 40px !important;
            width: auto !important;
            object-fit: contain !important;
        }
        .stat-card {
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .admin-bg-primary {
            background-color: #4e73df;
            color: white;
        }
        .admin-bg-success {
            background-color: #1cc88a;
            color: white;
        }
        .admin-bg-warning {
            background-color: #f6c23e;
            color: white;
        }
        .admin-bg-danger {
            background-color: #e74a3b;
            color: white;
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
                    <a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="eventos.php"><i class="bi bi-calendar-event me-1"></i> Eventos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="usuarios.php"><i class="bi bi-people me-1"></i> Usuarios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reportes.php"><i class="bi bi-graph-up me-1"></i> Reportes</a>
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
    <h2 class="mb-4"><i class="bi bi-speedometer2 me-2"></i> Panel de Administración</h2>

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card admin-bg-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">
                                Eventos Activos</div>
                            <div class="h5 mb-0 fw-bold">24</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-check fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card admin-bg-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">
                                Usuarios Registrados</div>
                            <div class="h5 mb-0 fw-bold">156</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card admin-bg-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">
                                Ingresos (Mes)</div>
                            <div class="h5 mb-0 fw-bold">$3,240</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-currency-dollar fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card admin-bg-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-uppercase mb-1">
                                Pendientes</div>
                            <div class="h5 mb-0 fw-bold">5</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-exclamation-triangle fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos y Tablas -->
    <div class="row">
        <!-- Gráfico de Eventos -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold"><i class="bi bi-bar-chart me-2"></i>Eventos por Mes</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="eventosChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Últimos Eventos -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold"><i class="bi bi-list-ul me-2"></i>Próximos Eventos</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Concierto de Rock</h6>
                                <small>15 Oct</small>
                            </div>
                            <p class="mb-1">Auditorio Nacional</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Feria Tecnológica</h6>
                                <small>22 Oct</small>
                            </div>
                            <p class="mb-1">Centro de Convenciones</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Taller de Marketing</h6>
                                <small>5 Nov</small>
                            </div>
                            <p class="mb-1">Hotel Intercontinental</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Últimos Usuarios -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold"><i class="bi bi-people-fill me-2"></i>Últimos Usuarios Registrados</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Juan Pérez</td>
                        <td>juan@example.com</td>
                        <td>2023-10-10</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>María Gómez</td>
                        <td>maria@example.com</td>
                        <td>2023-10-08</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="sticky-footer bg-dark text-white mt-5">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; Ideal Event's <?= date('Y') ?></span>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de eventos
    const ctx = document.getElementById('eventosChart').getContext('2d');
    const eventosChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct'],
            datasets: [{
                label: 'Eventos creados',
                data: [12, 19, 3, 5, 2, 3, 7, 15, 10, 8],
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
</body>
</html>