<?php
require_once 'config/db.php';
require_once 'models/DispositivoModel.php';
require_once 'models/CamaraModel.php';
require_once 'models/EventoModel.php';

class DashboardController {
    private $db;
    private $dispositivoModel;
    private $camaraModel;
    private $eventoModel;

    // Inicializamos la conexión y los modelos en el constructor
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();

        $this->dispositivoModel = new DispositivoModel($this->db);
        $this->camaraModel      = new CamaraModel($this->db);
        $this->eventoModel      = new EventoModel($this->db);
    }

    public function index() {
        // Verificar desconexiones antes de renderizar
        $this->dispositivoModel->marcarDesconectados(15);

        // Obtener datos reutilizando las propiedades de la clase
        $dispositivos = $this->dispositivoModel->obtenerTodos();
        $telemetria   = $this->dispositivoModel->obtenerUltimaTelemetria(5);
        $camaras      = $this->camaraModel->obtenerTodas();
        $eventos      = $this->eventoModel->obtenerUltimos(5);

        require_once 'views/dashboard.php';
    }

    // MÉTODO PARA EJECUTAR EL AGENTE PYTHON
    public function ejecutarAgente() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $pythonScript = realpath(__DIR__ . '/../agent/agente.py');

        if (!$pythonScript || !file_exists($pythonScript)) {
            echo json_encode(["status" => "error", "mensaje" => "No se encontró agente.py en la carpeta /agent."]);
            return;
        }

        $os = strtoupper(substr(PHP_OS, 0, 3));

        if ($os === 'WIN') {
            $comando = "start /B python \"" . $pythonScript . "\"";
            pclose(popen($comando, "r"));
            echo json_encode(["status" => "ok", "mensaje" => "Agente iniciado en segundo plano (Windows)."]);
        } else {
            $comando = "python3 \"" . $pythonScript . "\" > /dev/null 2>&1 &";
            exec($comando);
            echo json_encode(["status" => "ok", "mensaje" => "Agente iniciado en segundo plano (Linux/Mac)."]);
        }
    }

    // MÉTODO PARA DETENER EL AGENTE PYTHON (Optimizado con taskkill)
    public function detenerAgente() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $os = strtoupper(substr(PHP_OS, 0, 3));

        if ($os === 'WIN') {
            // taskkill rápido e instantáneo
            pclose(popen("start /B taskkill /F /FI \"IMAGENAME eq python*\" /T", "r"));
            echo json_encode(["status" => "ok", "mensaje" => "Proceso del agente detenido en Windows."]);
        } else {
            exec("pkill -9 -f agent/agente.py > /dev/null 2>&1 &");
            echo json_encode(["status" => "ok", "mensaje" => "Proceso del agente detenido en Linux/Mac."]);
        }
    }

    // MÉTODO PARA VACIAR TODA LA BASE DE DATOS
    public function vaciarTodo() {
        $this->eventoModel->vaciarTodo();
        header('Location: index.php?accion=dashboard');
        exit;
    }
}
?>