<?php
// filepath: c:\xampp\htdocs\SISTEMA_ESTACIONAMIENTO\public\ajax-buscar.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Vehiculo.php';
require_once __DIR__ . '/../classes/Cliente.php';

header('Content-Type: application/json');

if (isset($_GET['placa'])) {
    $vehiculoObj = new Vehiculo();
    $vehiculo = $vehiculoObj->buscarPorPlaca($_GET['placa']);
    echo json_encode($vehiculo ?: []);
    exit;
}

if (isset($_GET['dni'])) {
    $clienteObj = new Cliente();
    $cliente = $clienteObj->buscarPorDni($_GET['dni']);
    echo json_encode($cliente ?: []);
    exit;
}
?>