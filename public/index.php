<?php
// Redirección a login.php si no hay sesión activa
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
} else {
    // Si hay sesión, muestra el dashboard
    require_once __DIR__ . '/../includes/header.php';
    require_once __DIR__ . '/../classes/Registro.php';
    
    $registroObj = new Registro();
    $estadisticas = $registroObj->obtenerEstadisticas();
    $ocupacion = $registroObj->obtenerOcupacionEspacios();
    
    // Resto de tu código HTML...
}
?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5>Resumen de Estacionamiento</h5>
            </div>
            <div class="card-body">
                <p><strong>Vehículos estacionados:</strong> <?= $estadisticas['activos'] ?></p>
                <p><strong>Vehículos hoy:</strong> <?= $estadisticas['hoy'] ?></p>
                <p><strong>Vehículos este mes:</strong> <?= $estadisticas['mes'] ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5>Ingresos</h5>
            </div>
            <div class="card-body">
                <p><strong>Ingresos hoy:</strong> $<?= number_format($estadisticas['ingresos_hoy'], 2) ?></p>
                <p><strong>Ingresos este mes:</strong> $<?= number_format($estadisticas['ingresos_mes'], 2) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Ocupación de Espacios</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tipo de Espacio</th>
                    <th>Ocupados</th>
                    <th>Disponibles</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ocupacion as $tipo => $datos): ?>
                <tr>
                    <td><?= $tipo ?></td>
                    <td><?= $datos['ocupados'] ?></td>
                    <td><?= $datos['disponibles'] ?></td>
                    <td><?= $datos['total'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>