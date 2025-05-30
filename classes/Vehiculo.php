<?php
require_once __DIR__ . '/Database.php';

class Vehiculo {
    private $db;
    private $mirrorFile;

    public function __construct() {
        $this->db = new Database();
        $this->mirrorFile = MIRROR_DIR . 'vehiculos.dat';
    }

    public function registrar($placa, $marca, $modelo, $color, $tipo) {
        $placa = $this->db->escape($placa);
        $marca = $this->db->escape($marca);
        $modelo = $this->db->escape($modelo);
        $color = $this->db->escape($color);
        $tipo = $this->db->escape($tipo);

        $sql = "INSERT INTO vehiculos (placa, marca, modelo, color, tipo) 
                VALUES ('$placa', '$marca', '$modelo', '$color', '$tipo')";
        
        if ($this->db->query($sql)) {
            $id = $this->db->getLastId();
            
            // Registrar en el mirror
            $data = "$id|$placa|$marca|$modelo|$color|$tipo\n";
            file_put_contents($this->mirrorFile, $data, FILE_APPEND);
            
            return $id;
        }
        
        return false;
    }

    public function buscarPorPlaca($placa) {
        // Primero buscar en mirror
        $vehiculo = $this->buscarEnMirror($placa);
        
        if ($vehiculo) {
            return $vehiculo;
        }
        
        // Buscar en base de datos
        $placa = $this->db->escape($placa);
        $sql = "SELECT * FROM vehiculos WHERE placa = '$placa'";
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            $vehiculo = $result->fetch_assoc();
            // Guardar en mirror
            $this->guardarEnMirror($vehiculo);
            return $vehiculo;
        }
        
        return false;
    }

    private function buscarEnMirror($placa) {
        if (!file_exists($this->mirrorFile)) return false;
        
        $lines = file($this->mirrorFile);
        foreach ($lines as $line) {
            $data = explode('|', trim($line));
            if ($data[1] === $placa) {
                return [
                    'id_vehiculo' => $data[0],
                    'placa' => $data[1],
                    'marca' => $data[2],
                    'modelo' => $data[3],
                    'color' => $data[4],
                    'tipo' => $data[5]
                ];
            }
        }
        return false;
    }

    private function guardarEnMirror($vehiculo) {
        $data = implode('|', [
            $vehiculo['id_vehiculo'],
            $vehiculo['placa'],
            $vehiculo['marca'],
            $vehiculo['modelo'],
            $vehiculo['color'],
            $vehiculo['tipo']
        ]) . "\n";
        
        file_put_contents($this->mirrorFile, $data, FILE_APPEND);
    }

    public function obtenerTodos() {
        $sql = "SELECT * FROM vehiculos";
        $result = $this->db->query($sql);
        
        $vehiculos = [];
        while ($row = $result->fetch_assoc()) {
            $vehiculos[] = $row;
        }
        
        return $vehiculos;
    }

    public function buscarPorId($id_vehiculo) {
    $id_vehiculo = $this->db->escape($id_vehiculo);
    $sql = "SELECT * FROM vehiculos WHERE id_vehiculo = '$id_vehiculo'";
    $result = $this->db->query($sql);
    
    if ($result->num_rows > 0) {
        $vehiculo = $result->fetch_assoc();
        // Guardar en mirror para futuras consultas
        $this->guardarEnMirror($vehiculo);
        return $vehiculo;
    }
    
    return false;
}
}
?>