<?php
// models/CamaraModel.php

class CamaraModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerEstadoPrevio($camaraId) {
        $stmt = $this->conn->prepare("SELECT estado FROM camaras WHERE camara_id = ?");
        $stmt->bind_param("s", $camaraId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row['estado'] ?? null;
    }

    public function registrarOActualizar($id, $nombre, $ip, $ubicacion, $estado) {
        $sql = "INSERT INTO camaras (camara_id, nombre, ip_rtsp, ubicacion, estado, ultima_comprobacion) 
                VALUES (?, ?, ?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                    nombre = VALUES(nombre),
                    ip_rtsp = VALUES(ip_rtsp),
                    ubicacion = VALUES(ubicacion),
                    estado = VALUES(estado),
                    ultima_comprobacion = NOW()";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $id, $nombre, $ip, $ubicacion, $estado);
        return $stmt->execute();
    }

    public function obtenerTodas() {
        return $this->conn->query("SELECT * FROM camaras ORDER BY ultima_comprobacion DESC");
    }

    public function crearManual($id, $nombre, $ip, $ubicacion) {
        $stmt = $this->conn->prepare("INSERT INTO camaras (camara_id, nombre, ip_rtsp, ubicacion, estado, ultima_comprobacion) VALUES (?, ?, ?, ?, 'Sin conexión', NOW())");
        $stmt->bind_param("ssss", $id, $nombre, $ip, $ubicacion);
        return $stmt->execute();
    }

    public function actualizarManual($id, $nombre, $ip, $ubicacion) {
        $stmt = $this->conn->prepare("UPDATE camaras SET nombre = ?, ip_rtsp = ?, ubicacion = ? WHERE camara_id = ?");
        $stmt->bind_param("ssss", $nombre, $ip, $ubicacion, $id);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM camaras WHERE camara_id = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }

    public function obtenerPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM camaras WHERE camara_id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
}


}
?>