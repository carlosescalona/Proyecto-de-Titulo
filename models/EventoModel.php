/*  	 																															
!IniHeaderDoc
*****************************************************************************
!NombreObjeto     : EventoModel.php
!Sistema          : Proyecto de Monitoreo
!Descripcion      : Monitoreo de dispositivos y cámaras a través de API
!Plataforma       : !BaseDatosMysql
!Uso              : 
!Autor            : Carlos Escalona
!Creacion         : 09/10/2026
!Retornos/Salidas : NA
!OrigenReq        : NA
=============================================================================
!ControlCambio
--------------
!cVersion !cFecha       !cProgramador        !cDescripcion 
-----------------------------------------------------------------------------
*****************************************************************************
!EndHeaderDoc 
*/

<?php
// models/EventoModel.php

class EventoModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registrarEvento($origenId, $tipoEvento, $descripcion) {
        $stmt = $this->conn->prepare("INSERT INTO eventos (dispositivo_id, tipo_evento, descripcion, fecha) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $origenId, $tipoEvento, $descripcion);
        return $stmt->execute();
    }

    public function obtenerUltimos($limite = 10) {
        return $this->conn->query("SELECT * FROM eventos ORDER BY fecha DESC LIMIT $limite");
    }

    public function obtenerTodosConFiltro($tipo = '', $desde = '', $hasta = '') {
        $sql = "SELECT * FROM eventos WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($tipo)) {
            $sql .= " AND tipo_evento = ?";
            $params[] = $tipo;
            $types .= "s";
        }
        if (!empty($desde)) {
            $sql .= " AND fecha >= ?";
            $params[] = $desde . " 00:00:00";
            $types .= "s";
        }
        if (!empty($hasta)) {
            $sql .= " AND fecha <= ?";
            $params[] = $hasta . " 23:59:59";
            $types .= "s";
        }

        $sql .= " ORDER BY fecha DESC";
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM eventos WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function limpiarHistorial() {
        return $this->conn->query("TRUNCATE TABLE eventos");
    }
}
?>