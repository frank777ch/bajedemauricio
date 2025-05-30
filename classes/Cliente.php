<?php
require_once __DIR__ . '/Database.php';

class Cliente {
    private $db;
    private $mirrorFile;

    public function __construct() {
        $this->db = new Database();
        $this->mirrorFile = MIRROR_DIR . 'clientes.dat';
    }

    public function registrar($nombre, $apellido, $dni, $telefono = null, $email = null) {
        $nombre = $this->db->escape($nombre);
        $apellido = $this->db->escape($apellido);
        $dni = $this->db->escape($dni);
        $telefono = $this->db->escape($telefono);
        $email = $this->db->escape($email);

        $sql = "INSERT INTO clientes (nombre, apellido, dni, telefono, email) 
                VALUES ('$nombre', '$apellido', '$dni', '$telefono', '$email')";
        
        if ($this->db->query($sql)) {
            $id = $this->db->getLastId();
            
            // Registrar en mirror
            $data = "$id|$nombre|$apellido|$dni|$telefono|$email\n";
            file_put_contents($this->mirrorFile, $data, FILE_APPEND);
            
            return $id;
        }
        
        return false;
    }

    public function buscarPorDni($dni) {
        // Buscar en mirror primero
        $cliente = $this->buscarEnMirror($dni);
        
        if ($cliente) {
            return $cliente;
        }
        
        // Buscar en base de datos
        $dni = $this->db->escape($dni);
        $sql = "SELECT * FROM clientes WHERE dni = '$dni'";
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            $cliente = $result->fetch_assoc();
            // Guardar en mirror
            $this->guardarEnMirror($cliente);
            return $cliente;
        }
        
        return false;
    }

    private function buscarEnMirror($dni) {
        if (!file_exists($this->mirrorFile)) return false;
        
        $lines = file($this->mirrorFile);
        foreach ($lines as $line) {
            $data = explode('|', trim($line));
            if ($data[3] === $dni) {
                return [
                    'id_cliente' => $data[0],
                    'nombre' => $data[1],
                    'apellido' => $data[2],
                    'dni' => $data[3],
                    'telefono' => $data[4],
                    'email' => $data[5]
                ];
            }
        }
        return false;
    }

    private function guardarEnMirror($cliente) {
        $data = implode('|', [
            $cliente['id_cliente'],
            $cliente['nombre'],
            $cliente['apellido'],
            $cliente['dni'],
            $cliente['telefono'] ?? '',
            $cliente['email'] ?? ''
        ]) . "\n";
        
        file_put_contents($this->mirrorFile, $data, FILE_APPEND);
    }

    public function asociarVehiculo($id_cliente, $id_vehiculo) {
        $id_cliente = $this->db->escape($id_cliente);
        $id_vehiculo = $this->db->escape($id_vehiculo);

        // Verificar si ya existe la relación
        $sql = "SELECT 1 FROM cliente_vehiculo WHERE id_cliente = '$id_cliente' AND id_vehiculo = '$id_vehiculo'";
        $result = $this->db->query($sql);
        if ($result && $result->num_rows > 0) {
            // Ya existe, no hacer nada
            return true;
        }

        $sql = "INSERT INTO cliente_vehiculo (id_cliente, id_vehiculo) 
                VALUES ('$id_cliente', '$id_vehiculo')";

        return $this->db->query($sql);
    }
    
}
?>