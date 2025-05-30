<?php
require_once __DIR__ . '/Database.php';

class Tarifa {
    private $db;
    private $mirrorFile;

    public function __construct() {
        $this->db = new Database();
        $this->mirrorFile = MIRROR_DIR . 'tarifas.dat';
    }

    public function actualizarTarifa($tipo_vehiculo, $tipo_espacio, $tarifa_hora, $tarifa_dia) {
        $tipo_vehiculo = $this->db->escape($tipo_vehiculo);
        $tipo_espacio = $this->db->escape($tipo_espacio);
        $tarifa_hora = $this->db->escape($tarifa_hora);
        $tarifa_dia = $this->db->escape($tarifa_dia);
        
        // Verificar si ya existe la tarifa
        $sql = "SELECT id_tarifa FROM tarifas 
                WHERE tipo_vehiculo = '$tipo_vehiculo' 
                AND tipo_espacio = '$tipo_espacio'";
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            // Actualizar tarifa existente
            $sql = "UPDATE tarifas 
                    SET tarifa_hora = '$tarifa_hora', 
                        tarifa_dia = '$tarifa_dia' 
                    WHERE tipo_vehiculo = '$tipo_vehiculo' 
                    AND tipo_espacio = '$tipo_espacio'";
        } else {
            // Insertar nueva tarifa
            $sql = "INSERT INTO tarifas (tipo_vehiculo, tipo_espacio, tarifa_hora, tarifa_dia) 
                    VALUES ('$tipo_vehiculo', '$tipo_espacio', '$tarifa_hora', '$tarifa_dia')";
        }
        
        if ($this->db->query($sql)) {
            // Actualizar mirror
            $this->actualizarMirror($tipo_vehiculo, $tipo_espacio, $tarifa_hora, $tarifa_dia);
            return true;
        }
        
        return false;
    }

    private function actualizarMirror($tipo_vehiculo, $tipo_espacio, $tarifa_hora, $tarifa_dia) {
        $data = "$tipo_vehiculo|$tipo_espacio|$tarifa_hora|$tarifa_dia\n";
        
        // Crear el archivo si no existe
        if (!file_exists($this->mirrorFile)) {
            file_put_contents($this->mirrorFile, '');
        }

        $lines = file_exists($this->mirrorFile) ? file($this->mirrorFile) : [];
        $updated = false;

        foreach ($lines as $i => $line) {
            $parts = explode('|', trim($line));
            if ($parts[0] === $tipo_vehiculo && $parts[1] === $tipo_espacio) {
                $lines[$i] = $data;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $lines[] = $data;
        }

        file_put_contents($this->mirrorFile, implode('', $lines));
    }

    public function obtenerTodas() {
        $sql = "SELECT * FROM tarifas ORDER BY tipo_vehiculo, tipo_espacio";
        $result = $this->db->query($sql);
        
        $tarifas = [];
        while ($row = $result->fetch_assoc()) {
            $tarifas[] = $row;
        }
        
        return $tarifas;
    }
}
?>