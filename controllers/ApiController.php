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
        header("Content-Type: application/json; charset=UTF-8");
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
            $id      = $data['dispositivo_id'];
            $cpu     = floatval($data['cpu'] ?? 0.0);
            $memoria = floatval($data['memoria'] ?? 0.0);
            $fecha   = $data['fecha'] ?? date('Y-m-d H:i:s');

            // 1. Primero registra/actualiza el dispositivo (ahora no fallará por NOT NULL)
            $this->dispositivoModel->registrarOActualizar($id, $fecha);
            $this->dispositivoModel->guardarTelemetria($id, $cpu, $memoria, $fecha);
            $this->dispositivoModel->marcarDesconectados(15);

            // 2. Evaluamos e insertamos alertas
            if ($cpu > 50.0) { // Umbral recomendado para pruebas
                $cpuFormateado = number_format($cpu, 1);
                $desc = "Alerta de alto consumo de CPU en $id: {$cpuFormateado}%";
                $this->eventoModel->registrarEventoDispositivo($id, 'ALTO_USO_CPU', $desc);
            }

            if ($memoria > 80.0) {
                $memoriaFormateada = number_format($memoria, 1);
                $desc = "Alerta de alto consumo de Memoria RAM en $id: {$memoriaFormateada}%";
                $this->eventoModel->registrarEventoDispositivo($id, 'ALTO_USO_RAM', $desc);
            }

            echo json_encode(["status" => "ok", "mensaje" => "Telemetría recibida desde api controller"]);
        }

        } elseif ($accion === 'camaras') {
            $id        = $data['camara_id'];
            $nombre    = $data['nombre'] ?? ("Cámara " . $id);
            $ipRtsp    = $data['ip_rtsp'] ?? $data['ip'] ?? "127.0.0.1";
            $ubicacion = $data['ubicacion'] ?? "Sin ubicación";
            $estado    = $data['estado'] ?? "Operativa";
            $detalle   = $data['detalle'] ?? "";

            $estadoAnterior = $this->camaraModel->obtenerEstadoPrevio($id);
            $this->camaraModel->registrarOActualizar($id, $nombre, $ipRtsp, $ubicacion, $estado);

            // Registrar evento en eventos_camaras solo cuando cambia a un estado anómalo
            if ($estado !== 'Operativa' && $estadoAnterior !== $estado) {
                
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
                
                // --- REGISTRO EN LA TABLA DE EVENTOS DE CÁMARAS ---
                $this->eventoModel->registrarEventoCamara($id, $tipoEvento, $desc);
            }

            echo json_encode(["status" => "ok", "mensaje" => "Estado de cámara procesado desde api controller"]);

        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "mensaje" => "Acción no válida"]);
        }
    }
}
?>