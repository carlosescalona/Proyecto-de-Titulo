<?php
class EventoModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Guardar en la tabla 'eventos_camaras'
    public function registrarEventoCamara($camaraId, $tipoEvento, $descripcion) {
        $sql = "INSERT INTO eventos_camaras (camara_id, tipo_evento, descripcion, fecha) 
                VALUES (?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando consulta eventos_camaras: " . $this->db->error);
        }

        $stmt->bind_param("sss", $camaraId, $tipoEvento, $descripcion);
        return $stmt->execute();
    }

    // Guardar en la tabla 'eventos_dispositivos'
    public function registrarEventoDispositivo($dispositivoId, $tipoEvento, $descripcion) {
        $sql = "INSERT INTO eventos_dispositivos (dispositivo_id, tipo_evento, descripcion, fecha) 
                VALUES (?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando consulta eventos_dispositivos: " . $this->db->error);
        }

        $stmt->bind_param("sss", $dispositivoId, $tipoEvento, $descripcion);
        return $stmt->execute();
    }

    // Obtener los últimos eventos combinando cámaras y dispositivos para el Dashboard
    public function obtenerUltimos($limite = 10) {
        $sql = "SELECT 
                    id, 
                    dispositivo_id AS origen_id, 
                    'dispositivo' AS origen_tipo, 
                    tipo_evento, 
                    descripcion, 
                    fecha 
                FROM eventos_dispositivos
                
                UNION ALL
                
                SELECT 
                    id, 
                    camara_id AS origen_id, 
                    'camara' AS origen_tipo, 
                    tipo_evento, 
                    descripcion, 
                    fecha 
                FROM eventos_camaras
                
                ORDER BY fecha DESC 
                LIMIT ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando consulta unificada de eventos: " . $this->db->error);
        }

        $stmt->bind_param("i", $limite);
        $stmt->execute();
        return $stmt->get_result();
    }

    // Consultar eventos con filtros para el mantenedor
    public function obtenerTodosConFiltro($filtroOrigen = '', $filtroTipo = '') {
        $sql = "SELECT * FROM (
                    SELECT 
                        id, 
                        dispositivo_id AS origen_id, 
                        'dispositivo' AS origen_tipo, 
                        tipo_evento, 
                        descripcion, 
                        fecha 
                    FROM eventos_dispositivos
                    
                    UNION ALL
                    
                    SELECT 
                        id, 
                        camara_id AS origen_id, 
                        'camara' AS origen_tipo, 
                        tipo_evento, 
                        descripcion, 
                        fecha 
                    FROM eventos_camaras
                ) AS tabla_eventos
                WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filtroOrigen)) {
            $sql .= " AND origen_id LIKE ?";
            $params[] = "%" . $filtroOrigen . "%";
            $types .= "s";
        }

        if (!empty($filtroTipo)) {
            $sql .= " AND tipo_evento LIKE ?";
            $params[] = "%" . $filtroTipo . "%";
            $types .= "s";
        }

        $sql .= " ORDER BY fecha DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando la consulta con filtro: " . $this->db->error);
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result();
    }

    // Eliminar un evento específico identificando si es de dispositivo o cámara
public function eliminar($id, $tipoOrigen) {
    if ($tipoOrigen === 'camara') {
        $sql = "DELETE FROM eventos_camaras WHERE id = ?";
    } else {
        $sql = "DELETE FROM eventos_dispositivos WHERE id = ?";
    }

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparando eliminación de evento: " . $this->db->error);
    }

    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Vaciar todos los registros de ambas tablas de eventos
public function vaciar() {
    $sql1 = "TRUNCATE TABLE eventos_dispositivos";
    $sql2 = "TRUNCATE TABLE eventos_camaras";

    $res1 = $this->db->query($sql1);
    $res2 = $this->db->query($sql2);

    return ($res1 && $res2);
}

// Vaciar absolutamente todas las tablas del sistema
public function vaciarTodo() {
    // Desactivar temporalmente las claves foráneas por si existen relaciones
    $this->db->query("SET FOREIGN_KEY_CHECKS = 0");

    $res1 = $this->db->query("TRUNCATE TABLE eventos_dispositivos");
    $res2 = $this->db->query("TRUNCATE TABLE eventos_camaras");
    $res3 = $this->db->query("TRUNCATE TABLE dispositivos");
    $res4 = $this->db->query("TRUNCATE TABLE camaras");
    $res5 = $this->db->query("TRUNCATE TABLE telemetria");
    

    $this->db->query("SET FOREIGN_KEY_CHECKS = 1");

    return ($res1 && $res2 && $res3 && $res4 && $res5);
}

}
?>