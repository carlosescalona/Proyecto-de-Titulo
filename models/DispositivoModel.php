<?php
class DispositivoModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registrarOActualizar($id, $fecha) {
        $stmt = $this->conn->prepare("INSERT INTO dispositivos (dispositivo_id, nombre, tipo, ubicacion, estado, ultima_conexion) 
            VALUES (?, ?, 'Agente Monitoreo', 'Oficina Central', 'Activo', ?) 
            ON DUPLICATE KEY UPDATE ultima_conexion = ?, estado = 'Activo'");
        $nombre = "Equipo " . $id;
        $stmt->bind_param("ssss", $id, $nombre, $fecha, $fecha);
        return $stmt->execute();
    }

    public function guardarTelemetria($id, $cpu, $memoria, $fecha) {
        $stmt = $this->conn->prepare("INSERT INTO telemetria (dispositivo_id, cpu, memoria, fecha) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdds", $id, $cpu, $memoria, $fecha);
        return $stmt->execute();
    }

    public function obtenerTodos() {
        return $this->conn->query("SELECT * FROM dispositivos ORDER BY ultima_conexion DESC");
    }

    public function marcarDesconectados($timeoutSegundos = 15) {
        $sql = "UPDATE dispositivos 
                SET estado = 'Sin conexión' 
                WHERE estado != 'Sin conexión' 
                AND (TIMESTAMPDIFF(SECOND, ultima_conexion, NOW()) > ? OR ultima_conexion IS NULL)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $timeoutSegundos);
        return $stmt->execute();
    }

    public function obtenerUltimaTelemetria($limite = 10) {
    $sql = "SELECT t.dispositivo_id, d.nombre, t.cpu, t.memoria, t.fecha 
            FROM telemetria t 
            LEFT JOIN dispositivos d ON t.dispositivo_id = d.dispositivo_id 
            ORDER BY t.fecha DESC 
            LIMIT ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $limite);
    $stmt->execute();
    return $stmt->get_result();
}

public function crearManual($id, $nombre, $tipo, $ubicacion) {
    $stmt = $this->conn->prepare("INSERT INTO dispositivos (dispositivo_id, nombre, tipo, ubicacion, estado, ultima_conexion) VALUES (?, ?, ?, ?, 'Inactivo', NOW())");
    $stmt->bind_param("ssss", $id, $nombre, $tipo, $ubicacion);
    return $stmt->execute();
}

public function actualizarManual($id, $nombre, $tipo, $ubicacion) {
    $stmt = $this->conn->prepare("UPDATE dispositivos SET nombre = ?, tipo = ?, ubicacion = ? WHERE dispositivo_id = ?");
    $stmt->bind_param("ssss", $nombre, $tipo, $ubicacion, $id);
    return $stmt->execute();
}

public function eliminar($id) {
    $stmt = $this->conn->prepare("DELETE FROM dispositivos WHERE dispositivo_id = ?");
    $stmt->bind_param("s", $id);
    return $stmt->execute();
}

public function obtenerPorId($id) {
    $stmt = $this->conn->prepare("SELECT * FROM dispositivos WHERE dispositivo_id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

}
?>