<?php
require_once 'controllers/DashboardController.php';
require_once 'controllers/DispositivoController.php';
require_once 'controllers/CamaraController.php';
require_once 'controllers/EventoController.php'; 

$accion = $_GET['accion'] ?? 'dashboard';

switch ($accion) {
    // DISPOSITIVOS
    case 'mantenedor_dispositivos':
        (new DispositivoController())->index();
        break;
    case 'guardar_dispositivo':
        (new DispositivoController())->guardar();
        break;
    case 'eliminar_dispositivo':
        (new DispositivoController())->eliminar();
        break;

    // CAMARAS
    case 'mantenedor_camaras':
        (new CamaraController())->index();
        break;
    case 'guardar_camara':
        (new CamaraController())->guardar();
        break;
    case 'eliminar_camara':
        (new CamaraController())->eliminar();
        break;
    case 'mantenedor_eventos':
        (new EventoController())->index();
        break;
    case 'eliminar_evento':
        (new EventoController())->eliminar();
        break;
    case 'vaciar_eventos':
        (new EventoController())->vaciar();
        break;

    // DASHBOARD
    case 'dashboard':
    default:
        (new DashboardController())->index();
        break;
}
?>