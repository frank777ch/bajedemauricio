<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Registro.php';
require_once __DIR__ . '/../classes/Vehiculo.php';
require_once __DIR__ . '/../includes/header.php';

$registroObj = new Registro();
$vehiculoObj = new Vehiculo();

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa = $_POST['placa'] ?? '';
    
    // Buscar vehículo
    $vehiculo = $vehiculoObj->buscarPorPlaca($placa);
    
    if ($vehiculo) {
        // Buscar registro activo usando el nuevo método
        $registro = $registroObj->obtenerRegistroActivoPorVehiculo($vehiculo['id_vehiculo']);
        
        if ($registro) {
            // Registrar salida
            $resultado = $registroObj->registrarSalida($registro['id_registro']);
            
            if ($resultado) {
                $mensaje = "Salida registrada exitosamente. <br>
                           Vehículo: {$vehiculo['placa']} <br>
                           Tiempo estacionado: {$resultado['tiempo_formateado']} <br>
                           Total a pagar: $" . number_format($resultado['total'], 2) . " <br>
                           Espacio liberado: {$resultado['espacio']}";
            } else {
                $error = "Error al registrar la salida del vehículo";
            }
        } else {
            $error = "No se encontró un registro activo para este vehículo";
        }
    } else {
        $error = "Vehículo no registrado en el sistema";
    }
}
?>

<!-- El resto del código HTML permanece igual -->

<div class="container">
    <h2>Registro de Salida de Vehículo</h2>
    
    <?php if ($mensaje): ?>
    <div class="alert alert-success"><?= $mensaje ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Datos del Vehículo</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="placa">Placa del Vehículo</label>
                            <input type="text" class="form-control" id="placa" name="placa" required>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">Registrar Salida</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>