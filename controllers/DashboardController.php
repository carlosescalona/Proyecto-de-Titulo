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
        $telemetria   = $dispositivoModel->obtenerUltimaTelemetria(10); // <-- ESTA LÍNEA ES NECESARIA
        $camaras      = $camaraModel->obtenerTodas();
        $eventos      = $eventoModel->obtenerUltimos(10);

        require_once 'views/dashboard.php';
    }
}
?>