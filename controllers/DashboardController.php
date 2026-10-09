/*  	 																															
!IniHeaderDoc
*****************************************************************************
!NombreObjeto     : DashboardController.php
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
require_once 'config/db.php';
require_once 'models/DispositivoModel.php';
require_once 'models/CamaraModel.php';
require_once 'models/EventoModel.php';

class DashboardController {
    public function index() {
        $database = new Database();
        $db = $database->getConnection();

        $dispositivoModel = new DispositivoModel($db);
        $camaraModel = new CamaraModel($db);
        $eventoModel = new EventoModel($db);

        // Verificar desconexiones antes de renderizar
        $dispositivoModel->marcarDesconectados(15);

        // Obtener datos
        $dispositivos = $dispositivoModel->obtenerTodos();
        $telemetria   = $dispositivoModel->obtenerUltimaTelemetria(10);
        $camaras      = $camaraModel->obtenerTodas();
        $eventos      = $eventoModel->obtenerUltimos(10);

        require_once 'views/dashboard.php';
    }
}
?>