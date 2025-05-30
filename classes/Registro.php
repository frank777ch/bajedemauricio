<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Espacio.php'; // <-- Agrega esta línea

class Registro {
    private $db;
    private $mirrorFile;

    public function __construct() {
        $this->db = new Database();
        $this->mirrorFile = MIRROR_DIR . 'registros.dat';
    }

    public function registrarEntrada($id_vehiculo, $id_espacio) {
        $id_vehiculo = $this->db->escape($id_vehiculo);
        $id_espacio = $this->db->escape($id_espacio);
        
        // Obtener tarifa
        $tarifa = $this->obtenerTarifa($id_vehiculo, $id_espacio);
        
        if (!$tarifa) {
            return false;
        }
        
        $sql = "INSERT INTO registros (id_vehiculo, id_espacio, fecha_entrada, tarifa, estado) 
                VALUES ('$id_vehiculo', '$id_espacio', NOW(), '$tarifa', 'Activo')";
        
        if ($this->db->query($sql)) {
            $id_registro = $this->db->getLastId();
            
            // Ocupar espacio
            $espacioObj = new Espacio();
            $espacioObj->ocuparEspacio($id_espacio);
            
            // Guardar en mirror
            $this->guardarRegistroEnMirror($id_registro, $id_vehiculo, $id_espacio, $tarifa);
            
            return $id_registro;
        }
        
        return false;
    }

    private function obtenerTarifa($id_vehiculo, $id_espacio) {
        // Obtener tipo de vehículo
        $sql = "SELECT tipo FROM vehiculos WHERE id_vehiculo = '$id_vehiculo'";
        $result = $this->db->query($sql);
        
        if ($result->num_rows === 0) return false;
        
        $vehiculo = $result->fetch_assoc();
        $tipo_vehiculo = $vehiculo['tipo'];
        
        // Obtener tipo de espacio
        $sql = "SELECT tipo FROM espacios WHERE id_espacio = '$id_espacio'";
        $result = $this->db->query($sql);
        
        if ($result->num_rows === 0) return false;
        
        $espacio = $result->fetch_assoc();
        $tipo_espacio = $espacio['tipo'];
        
        // Obtener tarifa
        $sql = "SELECT tarifa_hora FROM tarifas 
                WHERE tipo_vehiculo = '$tipo_vehiculo' AND tipo_espacio = '$tipo_espacio'";
        $result = $this->db->query($sql);
        
        if ($result->num_rows === 0) return false;
        
        $tarifa = $result->fetch_assoc();
        return $tarifa['tarifa_hora'];
    }

    public function registrarSalida($id_registro) {
        $id_registro = $this->db->escape($id_registro);

        // Obtener registro activo
        $sql = "SELECT * FROM registros WHERE id_registro = '$id_registro' AND estado = 'Activo' LIMIT 1";
        $result = $this->db->query($sql);
        if (!$result || $result->num_rows === 0) {
            return false;
        }
        $registro = $result->fetch_assoc();

        $fecha_entrada = new DateTime($registro['fecha_entrada']);
        $fecha_salida = new DateTime(); // Ahora

        // Calcular diferencia en segundos
        $segundos = $fecha_salida->getTimestamp() - $fecha_entrada->getTimestamp();

        // Cobro por minuto
        $minutos_cobro = ceil($segundos / 60);
        if ($minutos_cobro < 1) $minutos_cobro = 1; // Cobro mínimo 1 minuto

        // Tiempo formateado para mostrar al usuario
        $horas_int = floor($minutos_cobro / 60);
        $minutos_restantes = $minutos_cobro % 60;
        $tiempo_formateado = $horas_int . 'h ' . $minutos_restantes . 'm';

        // Tarifa y total
        $tarifa = $registro['tarifa'];
        $tarifa_por_minuto = $tarifa / 60;
        $total = round($minutos_cobro * $tarifa_por_minuto, 2);

        // Obtener código del espacio
        $sqlEspacio = "SELECT codigo FROM espacios WHERE id_espacio = '{$registro['id_espacio']}'";
        $resEspacio = $this->db->query($sqlEspacio);
        $espacio = $resEspacio && $resEspacio->num_rows > 0 ? $resEspacio->fetch_assoc()['codigo'] : '';

        // Actualizar registro
        $sql_update = "UPDATE registros SET fecha_salida = '{$fecha_salida->format('Y-m-d H:i:s')}', total_pagar = '$total', estado = 'Finalizado' WHERE id_registro = '$id_registro'";
        $this->db->query($sql_update);

        // Liberar espacio (ponerlo como disponible)
        $sql_libera = "UPDATE espacios SET estado = 'Disponible' WHERE id_espacio = '{$registro['id_espacio']}'";
        $this->db->query($sql_libera);

        // Actualizar mirror si lo usas
        $this->actualizarRegistroEnMirror($id_registro, $total);

        return [
            'minutos' => $minutos_cobro,
            'tiempo_formateado' => $tiempo_formateado,
            'total' => $total,
            'espacio' => $espacio
        ];
    }

    private function guardarRegistroEnMirror($id_registro, $id_vehiculo, $id_espacio, $tarifa) {
        $data = implode('|', [
            $id_registro,
            $id_vehiculo,
            $id_espacio,
            date('Y-m-d H:i:s'),
            '', // fecha_salida vacía
            $tarifa,
            '0.00', // total_pagar inicial
            'Activo'
        ]) . "\n";
        
        file_put_contents($this->mirrorFile, $data, FILE_APPEND);
    }

    private function actualizarRegistroEnMirror($id_registro, $total_pagar) {
        if (!file_exists($this->mirrorFile)) return false;
        
        $lines = file($this->mirrorFile);
        $updated = false;
        
        foreach ($lines as $i => $line) {
            $data = explode('|', trim($line));
            if ($data[0] == $id_registro) {
                $data[4] = date('Y-m-d H:i:s'); // fecha_salida
                $data[6] = number_format($total_pagar, 2); // total_pagar
                $data[7] = 'Finalizado'; // estado
                $lines[$i] = implode('|', $data) . "\n";
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            file_put_contents($this->mirrorFile, implode('', $lines));
        }
    }

    public function obtenerEstadisticas() {
        $estadisticas = [];
        
        // Vehículos estacionados actualmente
        $sql = "SELECT COUNT(*) as total FROM registros WHERE estado = 'Activo'";
        $result = $this->db->query($sql);
        $estadisticas['activos'] = $result->fetch_assoc()['total'];
        
        // Vehículos atendidos hoy
        $sql = "SELECT COUNT(*) as total FROM registros 
                WHERE DATE(fecha_entrada) = CURDATE()";
        $result = $this->db->query($sql);
        $estadisticas['hoy'] = $result->fetch_assoc()['total'];
        
        // Vehículos atendidos este mes
        $sql = "SELECT COUNT(*) as total FROM registros 
                WHERE MONTH(fecha_entrada) = MONTH(CURDATE()) 
                AND YEAR(fecha_entrada) = YEAR(CURDATE())";
        $result = $this->db->query($sql);
        $estadisticas['mes'] = $result->fetch_assoc()['total'];
        
        // Ingresos hoy
        $sql = "SELECT SUM(total_pagar) as total FROM registros 
                WHERE DATE(fecha_salida) = CURDATE() 
                AND estado = 'Finalizado'";
        $result = $this->db->query($sql);
        $estadisticas['ingresos_hoy'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Ingresos este mes
        $sql = "SELECT SUM(total_pagar) as total FROM registros 
                WHERE MONTH(fecha_salida) = MONTH(CURDATE()) 
                AND YEAR(fecha_salida) = YEAR(CURDATE())
                AND estado = 'Finalizado'";
        $result = $this->db->query($sql);
        $estadisticas['ingresos_mes'] = $result->fetch_assoc()['total'] ?? 0;
        
        return $estadisticas;
    }

    public function obtenerOcupacionEspacios() {
        $sql = "SELECT tipo, 
                SUM(CASE WHEN estado = 'Ocupado' THEN 1 ELSE 0 END) as ocupados,
                SUM(CASE WHEN estado = 'Disponible' THEN 1 ELSE 0 END) as disponibles,
                COUNT(*) as total
                FROM espacios
                GROUP BY tipo";
        
        $result = $this->db->query($sql);
        
        $ocupacion = [];
        while ($row = $result->fetch_assoc()) {
            $ocupacion[$row['tipo']] = [
                'ocupados' => $row['ocupados'],
                'disponibles' => $row['disponibles'],
                'total' => $row['total']
            ];
        }
        
        return $ocupacion;
    }

    public function obtenerRegistrosPorVehiculo($id_vehiculo) {
    $id_vehiculo = $this->db->escape($id_vehiculo);
    $sql = "SELECT r.*, e.codigo as espacio_codigo 
            FROM registros r
            JOIN espacios e ON r.id_espacio = e.id_espacio
            WHERE r.id_vehiculo = '$id_vehiculo'
            ORDER BY r.fecha_entrada DESC";
    
    $result = $this->db->query($sql);
    
    $registros = [];
    while ($row = $result->fetch_assoc()) {
        $registros[] = $row;
    }
    
    return $registros;
}

public function obtenerRegistroActivoPorVehiculo($id_vehiculo) {
    $id_vehiculo = $this->db->escape($id_vehiculo);
    $sql = "SELECT * FROM registros WHERE id_vehiculo = '$id_vehiculo' AND estado = 'Activo' LIMIT 1";
    $result = $this->db->query($sql);
    return $result && $result->num_rows > 0 ? $result->fetch_assoc() : null;
}
public function obtenerRegistrosDelDia() {
    $sql = "SELECT r.*, v.placa, e.codigo as espacio_codigo 
            FROM registros r
            JOIN vehiculos v ON r.id_vehiculo = v.id_vehiculo
            JOIN espacios e ON r.id_espacio = e.id_espacio
            WHERE DATE(r.fecha_entrada) = CURDATE()
            ORDER BY r.fecha_entrada DESC";
    
    $result = $this->db->query($sql);
    
    $registros = [];
    while ($row = $result->fetch_assoc()) {
        $registros[] = $row;
    }
    
    return $registros;
}



}
?>
