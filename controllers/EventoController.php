<?php
require_once 'config/db.php';
require_once 'models/EventoModel.php';

class EventoController {
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

    // MÉTODO REQUERIDO POR EventoController.php PARA EL MANTENEDOR DE EVENTOS
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
}
?>