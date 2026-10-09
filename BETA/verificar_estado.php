<?php
require_once 'db.php'; // Usa la conexión centralizada

$timeout_segundos = 15;

// Buscar dispositivos que no estén 'Sin conexión' y cuya última conexión supere el tiempo límite
$sql = "SELECT dispositivo_id, ultima_conexion 
        FROM dispositivos 
        WHERE estado != 'Sin conexión' 
        AND (TIMESTAMPDIFF(SECOND, ultima_conexion, NOW()) > $timeout_segundos OR ultima_conexion IS NULL)";

$resultado = $conn->query($sql);

if (!$resultado) {
    die("Error en la consulta SQL: " . $conn->error);
}

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $id = $row['dispositivo_id'];
        $ultimaConexion = $row['ultima_conexion'] ?? 'Nunca';

        // 1. Cambiar estado a 'Sin conexión'
        $updateStmt = $conn->prepare("UPDATE dispositivos SET estado = 'Sin conexión' WHERE dispositivo_id = ?");
        if ($updateStmt) {
            $updateStmt->bind_param("s", $id);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            echo "Error al preparar UPDATE: " . $conn->error . "\n";
        }

        // 2. Registrar evento/alerta (Insertando explícitamente fecha actual)
        $descripcion = "Pérdida de conexión detectada. Último heartbeat recibido: " . $ultimaConexion;
        $tipo_evento = "PERDIDA_CONEXION";
        
        $eventStmt = $conn->prepare("INSERT INTO eventos (dispositivo_id, tipo_evento, descripcion, fecha) VALUES (?, ?, ?, NOW())");
        if ($eventStmt) {
            $eventStmt->bind_param("sss", $id, $tipo_evento, $descripcion);
            if ($eventStmt->execute()) {
                echo "[ALERTA] Dispositivo '$id' cambiado a 'Sin conexión'. Evento registrado correctamente.\n";
            } else {
                echo "Error al ejecutar INSERT en eventos: " . $eventStmt->error . "\n";
            }
            $eventStmt->close();
        } else {
            echo "Error al preparar INSERT en eventos: " . $conn->error . "\n";
        }
    }
} else {
    echo "Todos los dispositivos responden correctamente.\n";
}

$conn->close();
?>