<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Vehiculo.php';
require_once __DIR__ . '/../classes/Cliente.php';
require_once __DIR__ . '/../classes/Espacio.php';
require_once __DIR__ . '/../classes/Registro.php';
require_once __DIR__ . '/../includes/header.php';

$vehiculoObj = new Vehiculo();
$clienteObj = new Cliente();
$registroObj = new Registro();

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Datos del vehículo
    $placa = $_POST['placa'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $color = $_POST['color'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    // Datos del cliente (opcional)
    $dni_cliente = $_POST['dni_cliente'] ?? '';
    $nombre_cliente = $_POST['nombre_cliente'] ?? '';
    $apellido_cliente = $_POST['apellido_cliente'] ?? '';

    // 1. Verificar si el vehículo ya está registrado
    $vehiculo = $vehiculoObj->buscarPorPlaca($placa);
    if (!$vehiculo) {
        // Si no existe, registrar vehículo
        $idVehiculo = $vehiculoObj->registrar($placa, $marca, $modelo, $color, $tipo);
        if (!$idVehiculo) {
            $error = "Error al registrar el vehículo";
        } else {
            $vehiculo = $vehiculoObj->buscarPorPlaca($placa);
        }
    }

    // *** NUEVO: Verificar si el vehículo ya tiene un registro activo ***
    if (empty($error)) {
        $registroActivo = $registroObj->obtenerRegistroActivoPorVehiculo($vehiculo['id_vehiculo']);
        if ($registroActivo) {
            $error = "Este vehículo ya está registrado en un espacio y no ha registrado salida.";
        }
    }

    // 2. Verificar si el cliente ya está registrado (si se proporciona DNI)
    if (!empty($dni_cliente) && empty($error)) {
        $cliente = $clienteObj->buscarPorDni($dni_cliente);
        if (!$cliente) {
            // Si no existe, registrar cliente
            if (empty($nombre_cliente) || empty($apellido_cliente)) {
                $error = "Debe proporcionar nombre y apellido para registrar un nuevo cliente";
            } else {
                $idCliente = $clienteObj->registrar($nombre_cliente, $apellido_cliente, $dni_cliente);
                if (!$idCliente) {
                    $error = "Error al registrar el cliente";
                } else {
                    $cliente = $clienteObj->buscarPorDni($dni_cliente);
                }
            }
        }
        // Asociar cliente con vehículo si ambos existen y no hay error
        if (empty($error) && isset($cliente['id_cliente']) && isset($vehiculo['id_vehiculo'])) {
            $clienteObj->asociarVehiculo($cliente['id_cliente'], $vehiculo['id_vehiculo']);
        }
    }

    // 3. Registrar entrada si no hay errores
    if (empty($error)) {
        $resultado = $registroObj->registrarEntrada($vehiculo['id_vehiculo'], $_POST['id_espacio']);
        if ($resultado) {
            $mensaje = "Vehículo registrado exitosamente";
        } else {
            $error = "Error al registrar la entrada del vehículo";
        }
    }
}

// Obtener espacios disponibles
$espacioObj = new Espacio();
$espacios = $espacioObj->obtenerTodos();
$espaciosDisponibles = array_filter($espacios, function($espacio) {
    return $espacio['estado'] === 'Disponible';
});

$tiposVehiculo = ['Automóvil', 'Motocicleta', 'Camión', 'Otro'];
?>

<div class="container">
    <h2>Registro de Entrada de Vehículo</h2>
    
    <?php if ($mensaje): ?>
    <div class="alert alert-success"><?= $mensaje ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Datos del Vehículo</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="placa">Placa</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="placa" name="placa" required>
                                <button type="button" class="btn btn-secondary" id="buscarPlaca">Buscar</button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="marca">Marca</label>
                            <input type="text" class="form-control" id="marca" name="marca" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="modelo">Modelo</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="color">Color</label>
                            <input type="text" class="form-control" id="color" name="color" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="tipo">Tipo de Vehículo</label>
                            <select class="form-control" id="tipo" name="tipo" required>
                                <?php foreach ($tiposVehiculo as $tipo): ?>
                                <option value="<?= $tipo ?>"><?= $tipo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Datos del Cliente (Opcional)</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="dni_cliente">DNI</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="dni_cliente" name="dni_cliente">
                                <button type="button" class="btn btn-secondary" id="buscarDni">Buscar</button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="nombre_cliente">Nombre</label>
                            <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente">
                        </div>
                        
                        <div class="form-group">
                            <label for="apellido_cliente">Apellido</label>
                            <input type="text" class="form-control" id="apellido_cliente" name="apellido_cliente">
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5>Espacio de Estacionamiento</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="id_espacio">Seleccionar Espacio</label>
                            <select class="form-control" id="id_espacio" name="id_espacio" required>
                                <?php foreach ($espaciosDisponibles as $espacio): ?>
                                <option value="<?= $espacio['id_espacio'] ?>">
                                    <?= $espacio['codigo'] ?> (<?= $espacio['tipo'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <button type="submit" class="btn btn-primary btn-lg">Registrar Entrada</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
document.getElementById('buscarPlaca').addEventListener('click', function() {
    var placa = document.getElementById('placa').value.trim();
    if (placa === '') {
        alert('Ingrese una placa para buscar');
        return;
    }

    fetch('/SISTEMA_ESTACIONAMIENTO/public/ajax-buscar.php?placa=' + encodeURIComponent(placa))
    .then(response => response.json())
    .then(data => {
        if (data && data.placa) {
            document.getElementById('marca').value = data.marca;
            document.getElementById('modelo').value = data.modelo;
            document.getElementById('color').value = data.color;
            document.getElementById('tipo').value = data.tipo;
        } else {
            // Limpiar campos de vehículo
            document.getElementById('marca').value = '';
            document.getElementById('modelo').value = '';
            document.getElementById('color').value = '';
            document.getElementById('tipo').value = '';
            alert('Vehículo no encontrado');
        }
    })
    .catch(error => console.error('Error al buscar vehículo:', error));
});

document.getElementById('buscarDni').addEventListener('click', function() {
    var dni = document.getElementById('dni_cliente').value.trim();
    if (dni === '') {
        alert('Ingrese un DNI para buscar');
        return;
    }

    fetch('/SISTEMA_ESTACIONAMIENTO/public/ajax-buscar.php?dni=' + encodeURIComponent(dni))
    .then(response => response.json())
    .then(data => {
        if (data && data.dni) {
            document.getElementById('nombre_cliente').value = data.nombre;
            document.getElementById('apellido_cliente').value = data.apellido;
        } else {
            // Limpiar campos de cliente
            document.getElementById('nombre_cliente').value = '';
            document.getElementById('apellido_cliente').value = '';
            alert('Cliente no encontrado');
        }
    })
    .catch(error => console.error('Error al buscar cliente:', error));
});
</script>