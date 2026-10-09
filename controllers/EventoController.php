/*  	 																															
!IniHeaderDoc
*****************************************************************************
!NombreObjeto     : EventoController.php
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
require_once 'models/EventoModel.php';

class EventoController {
    private $model;

    public function __construct() {
        $database = new Database();
        $this->model = new EventoModel($database->getConnection());
    }

    public function index() {
        $tipo = $_GET['tipo'] ?? '';
        $desde = $_GET['desde'] ?? '';
        $hasta = $_GET['hasta'] ?? '';

        $eventos = $this->model->obtenerTodosConFiltro($tipo, $desde, $hasta);
        require_once 'views/eventos_mantenedor.php';
    }

    public function eliminar() {
        $id = $_GET['id'] ?? '';
        if ($id) {
            $this->model->eliminar($id);
        }
        header("Location: index.php?accion=mantenedor_eventos");
        exit();
    }

    public function vaciar() {
        $this->model->limpiarHistorial();
        header("Location: index.php?accion=mantenedor_eventos");
        exit();
    }
}
?>