<?php
require_once __DIR__ . '/../config/database.php';

class Database {
    private $connection;

    public function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->connection->connect_error) {
            die("Error de conexión: " . $this->connection->connect_error);
        }
        
        // Crear tablas si no existen
        $this->crearTablasSiNoExisten();
    }

    private function crearTablasSiNoExisten() {
        $sql = "
        CREATE TABLE IF NOT EXISTS vehiculos (
            id_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
            placa VARCHAR(15) NOT NULL UNIQUE,
            marca VARCHAR(50) NOT NULL,
            modelo VARCHAR(50) NOT NULL,
            color VARCHAR(30) NOT NULL,
            tipo ENUM('Automóvil', 'Motocicleta', 'Camión', 'Otro') NOT NULL
        );
        
        CREATE TABLE IF NOT EXISTS clientes (
            id_cliente INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            apellido VARCHAR(100) NOT NULL,
            dni VARCHAR(20) NOT NULL UNIQUE,
            telefono VARCHAR(20),
            email VARCHAR(100)
        );
        
        CREATE TABLE IF NOT EXISTS cliente_vehiculo (
            id_relacion INT AUTO_INCREMENT PRIMARY KEY,
            id_cliente INT NOT NULL,
            id_vehiculo INT NOT NULL,
            FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
            FOREIGN KEY (id_vehiculo) REFERENCES vehiculos(id_vehiculo),
            UNIQUE KEY (id_cliente, id_vehiculo)
        );
        
        CREATE TABLE IF NOT EXISTS espacios (
            id_espacio INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(10) NOT NULL UNIQUE,
            tipo ENUM('Regular', 'Discapacitados', 'Carga', 'VIP') NOT NULL,
            estado ENUM('Disponible', 'Ocupado', 'Mantenimiento') DEFAULT 'Disponible'
        );
        
        CREATE TABLE IF NOT EXISTS registros (
            id_registro INT AUTO_INCREMENT PRIMARY KEY,
            id_vehiculo INT NOT NULL,
            id_espacio INT NOT NULL,
            fecha_entrada DATETIME NOT NULL,
            fecha_salida DATETIME,
            tarifa DECIMAL(10,2),
            total_pagar DECIMAL(10,2),
            estado ENUM('Activo', 'Finalizado', 'Cancelado') DEFAULT 'Activo',
            FOREIGN KEY (id_vehiculo) REFERENCES vehiculos(id_vehiculo),
            FOREIGN KEY (id_espacio) REFERENCES espacios(id_espacio)
        );
        
        CREATE TABLE IF NOT EXISTS tarifas (
            id_tarifa INT AUTO_INCREMENT PRIMARY KEY,
            tipo_vehiculo ENUM('Automóvil', 'Motocicleta', 'Camión', 'Otro') NOT NULL,
            tipo_espacio ENUM('Regular', 'Discapacitados', 'Carga', 'VIP') NOT NULL,
            tarifa_hora DECIMAL(10,2) NOT NULL,
            tarifa_dia DECIMAL(10,2) NOT NULL,
            UNIQUE KEY (tipo_vehiculo, tipo_espacio)
        );
        
        INSERT IGNORE INTO tarifas (tipo_vehiculo, tipo_espacio, tarifa_hora, tarifa_dia) VALUES
        ('Automóvil', 'Regular', 5.00, 40.00),
        ('Automóvil', 'VIP', 8.00, 60.00),
        ('Motocicleta', 'Regular', 3.00, 20.00),
        ('Camión', 'Regular', 10.00, 80.00),
        ('Camión', 'Carga', 12.00, 90.00);
        
        INSERT IGNORE INTO espacios (codigo, tipo, estado) VALUES
        ('A-01', 'Regular', 'Disponible'),
        ('A-02', 'Regular', 'Disponible'),
        ('A-03', 'Regular', 'Disponible'),
        ('B-01', 'VIP', 'Disponible'),
        ('B-02', 'VIP', 'Disponible'),
        ('C-01', 'Carga', 'Disponible'),
        ('D-01', 'Discapacitados', 'Disponible');
        ";
        
        $this->connection->multi_query($sql);
        while ($this->connection->more_results()) {
            $this->connection->next_result();
        }
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }

    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }

    public function getLastId() {
        return $this->connection->insert_id;
    }

    public function __destruct() {
        $this->connection->close();
    }
}
?>