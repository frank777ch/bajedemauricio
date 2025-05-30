<?php
require_once __DIR__ . '/Database.php';

class Espacio {
    private $db;
    private $mirrorFile;

    public function __construct() {
        $this->db = new Database();
        $this->mirrorFile = MIRROR_DIR . 'espacios.dat';
    }

    public function obtenerEspacioDisponible($tipoVehiculo) {
        // Determinar tipo de espacio adecuado
        $tipoEspacio = $this->determinarTipoEspacio($tipoVehiculo);
        
        $sql = "SELECT * FROM espacios 
                WHERE tipo = '$tipoEspacio' AND estado = 'Disponible' 
                LIMIT 1";
        
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            $espacio = $result->fetch_assoc();
            // Guardar en mirror
            $this->guardarEnMirror($espacio);
            return $espacio;
        }
        
        return false;
    }

    private function determinarTipoEspacio($tipoVehiculo) {
        switch ($tipoVehiculo) {
            case 'Camión':
                return 'Carga';
            case 'Motocicleta':
            case 'Automóvil':
                return 'Regular';
            default:
                return 'Regular';
        }
    }

    public function ocuparEspacio($id_espacio) {
        $id_espacio = $this->db->escape($id_espacio);
        $sql = "UPDATE espacios SET estado = 'Ocupado' WHERE id_espacio = '$id_espacio'";
        return $this->db->query($sql);
    }

    public function liberarEspacio($id_espacio) {
        $id_espacio = $this->db->escape($id_espacio);
        $sql = "UPDATE espacios SET estado = 'Disponible' WHERE id_espacio = '$id_espacio'";
        return $this->db->query($sql);
    }

    private function guardarEnMirror($espacio) {
        $data = implode('|', [
            $espacio['id_espacio'],
            $espacio['codigo'],
            $espacio['tipo'],
            $espacio['estado']
        ]) . "\n";
        
        file_put_contents($this->mirrorFile, $data, FILE_APPEND);
    }

    public function obtenerTodos() {
        $sql = "SELECT * FROM espacios";
        $result = $this->db->query($sql);
        
        $espacios = [];
        while ($row = $result->fetch_assoc()) {
            $espacios[] = $row;
        }
        
        return $espacios;
    }

    public function agregarEspacio($codigo, $tipo) {
    $codigo = $this->db->escape($codigo);
    $tipo = $this->db->escape($tipo);
    
    $sql = "INSERT INTO espacios (codigo, tipo, estado) 
            VALUES ('$codigo', '$tipo', 'Disponible')";
    
    if ($this->db->query($sql)) {
        // Registrar en el mirror
        $id = $this->db->getLastId();
        $data = "$id|$codigo|$tipo|Disponible\n";
        file_put_contents($this->mirrorFile, $data, FILE_APPEND);
        
        return true;
    }
    
    return false;
}
}
?>