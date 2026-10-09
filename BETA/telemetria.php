<?php
header("Content-Type: application/json");
require_once 'db.php'; // Usa la conexión centralizada

$input = file_get_contents('php://input');
$payload = json_decode($input, true);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "mensaje" => "Error de conexión a la BD"]);
    exit();
}

// Leer JSON recibido desde Python
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data && !empty($data['dispositivo_id'])) {
    $id = $data['dispositivo_id']; // Directo al bind_param, no requiere real_escape_string
    $cpu = floatval($data['cpu'] ?? 0);
    $memoria = floatval($data['memoria'] ?? 0);
    
    // Asignación limpia de la fecha
    $fecha = !empty($data['fecha']) ? $data['fecha'] : date('Y-m-d H:i:s');

    try {
        // 1. Insertar / Actualizar dispositivo
        $stmtReg = $conn->prepare("INSERT INTO dispositivos (dispositivo_id, nombre, tipo, ubicacion, estado, ultima_conexion) 
            VALUES (?, ?, 'Agente Monitoreo', 'Oficina Central', 'Activo', ?) 
            ON DUPLICATE KEY UPDATE ultima_conexion = ?, estado = 'Activo'");
        
        $nombreInicial = "Equipo " . $id;
        $stmtReg->bind_param("ssss", $id, $nombreInicial, $fecha, $fecha);
        $stmtReg->execute();
        $stmtReg->close();

        // 2. Guardar Telemetría
        $stmtInsert = $conn->prepare("INSERT INTO telemetria (dispositivo_id, cpu, memoria, fecha) VALUES (?, ?, ?, ?)");
        $stmtInsert->bind_param("sdds", $id, $cpu, $memoria, $fecha);
        $stmtInsert->execute();
        $stmtInsert->close();

        echo json_encode(["status" => "ok", "mensaje" => "Heartbeat recibido"]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "mensaje" => "Error en la consulta: " . $e->getMessage()]);
    }

} else {
    http_response_code(400);
    echo json_encode([
        "status" => "error", 
        "mensaje" => "Falta 'dispositivo_id' en el JSON recibido",
        "datos_recibidos" => $data
    ]);
}

$conn->close();
?>