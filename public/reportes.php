<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Registro.php';
require_once __DIR__ . '/../includes/header.php';

$registroObj = new Registro();

// Obtener datos usando los nuevos métodos
$estadisticas = $registroObj->obtenerEstadisticas();
$ocupacion = $registroObj->obtenerOcupacionEspacios();
$registros_hoy = $registroObj->obtenerRegistrosDelDia();
?>

<div class="container">
    <h2>Reportes y Estadísticas</h2>
    
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
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
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
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Vehículos Estacionados Hoy</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($registros_hoy)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Placa</th>
                                        <th>Espacio</th>
                                        <th>Hora Entrada</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($registros_hoy as $registro): ?>
                                    <tr>
                                        <td><?= $registro['placa'] ?></td>
                                        <td><?= $registro['espacio_codigo'] ?></td>
                                        <td><?= date('H:i', strtotime($registro['fecha_entrada'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $registro['estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                                                <?= $registro['estado'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No hay vehículos estacionados hoy.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>