<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Espacio.php';
require_once __DIR__ . '/../classes/Tarifa.php';
require_once __DIR__ . '/../includes/header.php';

$espacioObj = new Espacio();
$tarifaObj = new Tarifa();

$mensaje = '';
$error = '';

// Procesar formulario de espacios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_espacio'])) {
    $codigo = $_POST['codigo'] ?? '';
    $tipo = $_POST['tipo_espacio'] ?? '';
    
    if (!empty($codigo) && !empty($tipo)) {
        if ($espacioObj->agregarEspacio($codigo, $tipo)) {
            $mensaje = "Espacio agregado correctamente";
        } else {
            $error = "Error al agregar el espacio";
        }
    } else {
        $error = "Debe completar todos los campos";
    }
}

// Procesar formulario de tarifas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_tarifa'])) {
    $tipo_vehiculo = $_POST['tipo_vehiculo'] ?? '';
    $tipo_espacio = $_POST['tipo_espacio'] ?? '';
    $tarifa_hora = $_POST['tarifa_hora'] ?? 0;
    $tarifa_dia = $_POST['tarifa_dia'] ?? 0;
    
    if (!empty($tipo_vehiculo) && !empty($tipo_espacio)) {
        if ($tarifaObj->actualizarTarifa($tipo_vehiculo, $tipo_espacio, $tarifa_hora, $tarifa_dia)) {
            $mensaje = "Tarifa actualizada correctamente";
        } else {
            $error = "Error al actualizar la tarifa";
        }
    } else {
        $error = "Debe completar todos los campos";
    }
}

// Obtener datos
$espacios = $espacioObj->obtenerTodos();
$tarifas = $tarifaObj->obtenerTodas();

$tiposEspacio = ['Regular', 'Discapacitados', 'Carga', 'VIP'];
$tiposVehiculo = ['Automóvil', 'Motocicleta', 'Camión', 'Otro'];
?>

<div class="container">
    <h2>Administración del Sistema</h2>
    
    <?php if ($mensaje): ?>
    <div class="alert alert-success"><?= $mensaje ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Gestión de Espacios</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group mb-3">
                            <label for="codigo">Código del Espacio</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="tipo_espacio">Tipo de Espacio</label>
                            <select class="form-control" id="tipo_espacio" name="tipo_espacio" required>
                                <?php foreach ($tiposEspacio as $tipo): ?>
                                <option value="<?= $tipo ?>"><?= $tipo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" name="agregar_espacio" class="btn btn-primary">Agregar Espacio</button>
                    </form>
                    
                    <hr>
                    
                    <h6>Espacios Registrados</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($espacios as $espacio): ?>
                                <tr>
                                    <td><?= $espacio['codigo'] ?></td>
                                    <td><?= $espacio['tipo'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $espacio['estado'] === 'Disponible' ? 'success' : 'warning' ?>">
                                            <?= $espacio['estado'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Gestión de Tarifas</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group mb-3">
                            <label for="tipo_vehiculo">Tipo de Vehículo</label>
                            <select class="form-control" id="tipo_vehiculo" name="tipo_vehiculo" required>
                                <?php foreach ($tiposVehiculo as $tipo): ?>
                                <option value="<?= $tipo ?>"><?= $tipo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="tipo_espacio">Tipo de Espacio</label>
                            <select class="form-control" id="tipo_espacio" name="tipo_espacio" required>
                                <?php foreach ($tiposEspacio as $tipo): ?>
                                <option value="<?= $tipo ?>"><?= $tipo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="tarifa_hora">Tarifa por Hora ($)</label>
                            <input type="number" step="0.01" class="form-control" id="tarifa_hora" name="tarifa_hora" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="tarifa_dia">Tarifa por Día ($)</label>
                            <input type="number" step="0.01" class="form-control" id="tarifa_dia" name="tarifa_dia" required>
                        </div>
                        
                        <button type="submit" name="actualizar_tarifa" class="btn btn-primary">Actualizar Tarifa</button>
                    </form>
                    
                    <hr>
                    
                    <h6>Tarifas Actuales</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Vehículo</th>
                                    <th>Espacio</th>
                                    <th>Hora</th>
                                    <th>Día</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tarifas as $tarifa): ?>
                                <tr>
                                    <td><?= $tarifa['tipo_vehiculo'] ?></td>
                                    <td><?= $tarifa['tipo_espacio'] ?></td>
                                    <td>$<?= number_format($tarifa['tarifa_hora'], 2) ?></td>
                                    <td>$<?= number_format($tarifa['tarifa_dia'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>