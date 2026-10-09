<?php
require_once 'config/db.php';
require_once 'models/DispositivoModel.php';
require_once 'models/CamaraModel.php';
require_once 'models/EventoModel.php';

class ApiController {
    private $db;
    private $dispositivoModel;
    private $camaraModel;
    private $eventoModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->dispositivoModel = new DispositivoModel($this->db);
        $this->camaraModel = new CamaraModel($this->db);
        $this->eventoModel = new EventoModel($this->db);
    }

    public function procesarPeticion() {
        header("Content-Type: application/json");
        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);

        $accion = $payload['accion'] ?? '';
        $data   = $payload['datos'] ?? null;

        if (!$data) {
            http_response_code(400);
            echo json_encode(["status" => "error", "mensaje" => "Datos requeridos faltantes"]);
            return;
        }

        if ($accion === 'telemetria') {
            $id = $data['dispositivo_id'];
            $cpu = floatval($data['cpu'] ?? 0);
            $memoria = floatval($data['memoria'] ?? 0);
            $fecha = $data['fecha'] ?? date('Y-m-d H:i:s');

            $this->dispositivoModel->registrarOActualizar($id, $fecha);
            $this->dispositivoModel->guardarTelemetria($id, $cpu, $memoria, $fecha);
            $this->dispositivoModel->marcarDesconectados(15);

            echo json_encode(["status" => "ok", "mensaje" => "Telemetría recibida"]);

        } elseif ($accion === 'camaras') {
            $id = $data['camara_id'];
            $nombre = $data['nombre'] ?? ("Cámara " . $id);
            $ip = $data['ip'] ?? "127.0.0.1";
            $ubicacion = $data['ubicacion'] ?? "Sin ubicación";
            $estado = $data['estado'] ?? "Operativa";
            $detalle = $data['detalle'] ?? "";

            $estadoAnterior = $this->camaraModel->obtenerEstadoPrevio($id);
            $this->camaraModel->registrarOActualizar($id, $nombre, $ip, $ubicacion, $estado);

            // Registrar evento solo cuando cambie de estado a una condición anómala
            if ($estado !== 'Operativa' && $estadoAnterior !== $estado) {
                
                // Mapeo dinámico del tipo de evento según el estado/detalle
                if ($estado === 'Sin imagen') {
                    $tipoEvento = 'PERDIDA_IMAGEN';
                } elseif ($estado === 'Sin conexión') {
                    $tipoEvento = 'PERDIDA_CONEXION';
                } elseif (strpos(strtolower($detalle), 'congelada') !== false) {
                    $tipoEvento = 'IMAGEN_CONGELADA';
                } else {
                    $tipoEvento = 'CAMARA_ANOMALIA';
                }

                $desc = "Alerta en cámara $id ($nombre): $detalle";
                $this->eventoModel->registrarEvento($id, $tipoEvento, $desc);
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado de cámara procesado"]);
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "mensaje" => "Acción no válida"]);
        }
    }
}
?>