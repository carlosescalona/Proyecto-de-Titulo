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
        $telemetria   = $dispositivoModel->obtenerUltimaTelemetria(5);
        $camaras      = $camaraModel->obtenerTodas();
        $eventos      = $eventoModel->obtenerUltimos(5);
   
        require_once 'views/dashboard.php';
    }

    // MÉTODO PARA EJECUTAR EL AGENTE PYTHON
    public function ejecutarAgente() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        // Según tu árbol, 'agente.py' o 'agente' está en la carpeta /agent
        $pythonScript = realpath(__DIR__ . '/../agent/agente.py');

        if (!$pythonScript || !file_exists($pythonScript)) {
            echo json_encode(["status" => "error", "mensaje" => "No se encontró agente.py en la carpeta /agent."]);
            return;
        }

        $os = strtoupper(substr(PHP_OS, 0, 3));

        if ($os === 'WIN') {
            // Ejecución en segundo plano para Windows
            $comando = "start /B python \"" . $pythonScript . "\"";
            pclose(popen($comando, "r"));
            echo json_encode(["status" => "ok", "mensaje" => "Agente iniciado en segundo plano (Windows)."]);
        } else {
            // Ejecución en segundo plano para Linux/Mac
            $comando = "python3 \"" . $pythonScript . "\" > /dev/null 2>&1 &";
            exec($comando);
            echo json_encode(["status" => "ok", "mensaje" => "Agente iniciado en segundo plano (Linux/Mac)."]);
        }
    }

    // MÉTODO PARA DETENER EL AGENTE PYTHON
    public function detenerAgente() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $os = strtoupper(substr(PHP_OS, 0, 3));

        if ($os === 'WIN') {
            // En Windows finaliza los procesos python que ejecutan agente.py (usando wmic / taskkill)
            $comando = 'wmic process where "name=\'python.exe\' and commandline like \'%agente.py%\'" call terminate';
            exec($comando, $output, $returnVar);

            // Fallback básico por si WMIC no está disponible en versiones recientes de Windows
            if ($returnVar !== 0) {
                exec('taskkill /F /IM python.exe 2>&1');
            }

            echo json_encode(["status" => "ok", "mensaje" => "Proceso del agente detenido en Windows."]);
        } else {
            // En Linux / Mac mata los procesos python3 asociados a agente.py
            $comando = "pkill -f agent/agente.py 2>&1";
            exec($comando);

            echo json_encode(["status" => "ok", "mensaje" => "Proceso del agente detenido en Linux/Mac."]);
        }
    }
}
?>