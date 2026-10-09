<?php
$conn = new mysqli("localhost", "root", "", "schema");

if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

$dispositivos = $conn->query("SELECT * FROM dispositivos ORDER BY ultima_conexion DESC");
$eventos = $conn->query("SELECT * FROM eventos ORDER BY fecha DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5"> <!-- Recarga automática cada 5 segundos -->
    <title>Monitoreo de Heartbeat</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f8f9fa; color: #333; }
        h2 { color: #0d6efd; border-bottom: 2px solid #0d6efd; padding-bottom: 5px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #dee2e6; padding: 10px; text-align: left; }
        th { background-color: #e9ecef; }
        .activo { color: #198754; font-weight: bold; }
        .desconectado { color: #dc3545; font-weight: bold; }
        .badge-alerta { background: #ffc107; color: #000; padding: 3px 6px; border-radius: 4px; font-size: 0.9em; }
    </style>
</head>
<body>

    <h2>Estado de Dispositivos</h2>
    <table>
        <thead>
            <tr>
                <th>ID Dispositivo</th>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Última Conexión</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($dispositivos && $dispositivos->num_rows > 0): ?>
                <?php while($d = $dispositivos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($d['dispositivo_id']) ?></td>
                    <td><?= htmlspecialchars($d['nombre'] ?? 'N/A') ?></td>
                    <td class="<?= ($d['estado'] == 'Sin conexión' || $d['estado'] == 'Inactivo') ? 'desconectado' : 'activo' ?>">
                        <?= htmlspecialchars($d['estado']) ?>
                    </td>
                    <td><?= htmlspecialchars($d['ultima_conexion']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No hay dispositivos registrados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Registro de Eventos / Alertas</h2>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Origen (Dispositivo / Cámara)</th>
                <th>Tipo Evento</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($eventos && $eventos->num_rows > 0): ?>
                <?php while($e = $eventos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($e['fecha']) ?></td>
                    <td>
                        <?= htmlspecialchars($e['camara_id'] ?? $e['dispositivo_id'] ?? 'Desconocido') ?>
                    </td>
                    <td><span class="badge-alerta"><?= htmlspecialchars($e['tipo_evento']) ?></span></td>
                    <td><?= htmlspecialchars($e['descripcion']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No hay eventos registrados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>