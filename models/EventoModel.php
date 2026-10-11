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
}
?>