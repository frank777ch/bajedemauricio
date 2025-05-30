<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sistema_estacionamiento');

define('MIRROR_DIR', __DIR__ . '/../mirror/');

if (!file_exists(MIRROR_DIR)) {
    mkdir(MIRROR_DIR, 0755, true);
}

$mirrorFiles = ['vehiculos.dat', 'clientes.dat', 'espacios.dat', 'registros.dat'];
foreach ($mirrorFiles as $file) {
    if (!file_exists(MIRROR_DIR . $file)) {
        file_put_contents(MIRROR_DIR . $file, '');
    }
}

date_default_timezone_set('America/Lima');
?>