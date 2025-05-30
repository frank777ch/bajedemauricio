<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Vehiculo.php';
require_once __DIR__ . '/../classes/Registro.php';
require_once __DIR__ . '/../includes/header.php';

$vehiculoObj = new Vehiculo();
$registroObj = new Registro();

$resultados = [];
$busqueda_realizada = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $busqueda = $_POST['busqueda'] ?? '';
    $tipo_busqueda = $_POST['tipo_busqueda'] ?? 'placa';
    
    if (!empty($busqueda)) {
        $busqueda_realizada = true;
        
        if ($tipo_busqueda === 'placa') {
            // Buscar por placa
            $vehiculo = $vehiculoObj->buscarPorPlaca($busqueda);
            
            if ($vehiculo) {
                // Obtener registros usando el método de la clase Registro
                $resultados = $registroObj->obtenerRegistrosPorVehiculo($vehiculo['id_vehiculo']);
            }
        }
    }
}
?>

<!-- El resto del código HTML permanece igual -->

<div class="container">
    <h2>Buscar Vehículo</h2>
    
    <form method="POST">
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="input-group">
                    <select class="form-select" name="tipo_busqueda" style="max-width: 150px;">
                        <option value="placa" selected>Por Placa</option>
                    </select>
                    <input type="text" class="form-control" name="busqueda" placeholder="Ingrese la placa del vehículo">
                    <button class="btn btn-primary" type="submit">Buscar</button>
                </div>
            </div>
        </div>
    </form>
    
    <?php if ($busqueda_realizada): ?>
        <?php if (!empty($resultados)): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Resultados de la búsqueda</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Placa</th>
                                <th>Espacio</th>
                                <th>Fecha Entrada</th>
                                <th>Fecha Salida</th>
                                <th>Estado</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $registro): 
                                $vehiculo = $vehiculoObj->buscarPorId($registro['id_vehiculo']);
                            ?>
                            <tr>
                                <td><?= $vehiculo['placa'] ?></td>
                                <td><?= $registro['espacio_codigo'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($registro['fecha_entrada'])) ?></td>
                                <td>
                                    <?= $registro['fecha_salida'] ? date('d/m/Y H:i', strtotime($registro['fecha_salida'])) : '--' ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $registro['estado'] === 'Activo' ? 'success' : 'secondary' ?>">
                                        <?= $registro['estado'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $registro['total_pagar'] ? '$' . number_format($registro['total_pagar'], 2) : '--' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No se encontraron resultados para la búsqueda</div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>