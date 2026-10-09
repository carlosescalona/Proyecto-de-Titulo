<?php
header("Content-Type: application/json");
require_once 'db.php'; // Usa la conexión centralizada

$input = file_get_contents('php://input');
$payload = json_decode($input, true);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "mensaje" => "Error de BD: " . $conn->connect_error]);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data && isset($data['camara_id'])) {
    $id = $data['camara_id'];
    $nombre = $data['nombre'] ?? ("Cámara " . $id);
    $ip = $data['ip'] ?? "127.0.0.1";
    $ubicacion = $data['ubicacion'] ?? "Sin ubicación";
    $estado = $data['estado'] ?? "Operativa";
    $detalle = $data['detalle'] ?? "";

    // 1. Obtener el estado previo para evitar eventos duplicados repetitivos
    $estadoAnterior = null;
    $stmtPrev = $conn->prepare("SELECT estado FROM camaras WHERE camara_id = ?");
    $stmtPrev->bind_param("s", $id);
    $stmtPrev->execute();
    $stmtPrev->bind_result($estadoAnterior);
    $stmtPrev->fetch();
    $stmtPrev->close();

    // 2. Guardar o actualizar la cámara
    $sql = "INSERT INTO camaras (camara_id, nombre, ip_rtsp, ubicacion, estado, ultima_comprobacion) 
            VALUES (?, ?, ?, ?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE estado = ?, ultima_comprobacion = NOW()";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $id, $nombre, $ip, $ubicacion, $estado, $estado);
    $stmt->execute();
    $stmt->close();

    // 3. Registrar evento SOLO si el estado cambió o si es la primera vez que falla
    if ($estado !== 'Operativa' && $estadoAnterior !== $estado) {
        $tipoEvento = (strpos($detalle, 'congelada') !== false) ? 'IMAGEN_CONGELADA' : 'CAMARA_ANOMALIA';
        $desc = "Alerta en cámara $id ($nombre): $detalle";
        
        // Ajusta la columna (camara_id o dispositivo_id) según la estructura de tu tabla 'eventos'
        $stmtEv = $conn->prepare("INSERT INTO eventos (camara_id, tipo_evento, descripcion, fecha) VALUES (?, ?, ?, NOW())");
        if ($stmtEv) {
            $stmtEv->bind_param("sss", $id, $tipoEvento, $desc);
            $stmtEv->execute();
            $stmtEv->close();
        }
    }

    echo json_encode(["status" => "ok", "mensaje" => "Estado y eventos procesados"]);
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "mensaje" => "Datos inválidos"]);
}

$conn->close();
?>