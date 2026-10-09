<?php
require_once 'db.php'; // Usa la conexión centralizada

$dispositivos = $conn->query("SELECT * FROM dispositivos ORDER BY ultima_conexion DESC");
$camaras      = $conn->query("SELECT * FROM camaras ORDER BY ultima_comprobacion DESC");
$telemetria   = $conn->query("SELECT * FROM telemetria ORDER BY fecha DESC LIMIT 10");
$eventos      = $conn->query("SELECT * FROM eventos ORDER BY fecha DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5">
    <title>Panel Integral de Monitoreo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #181825; color: #cdd6f4; }
        h1 { color: #89b4fa; text-align: center; }
        h2 { color: #89b4fa; border-bottom: 2px solid #313244; padding-bottom: 5px; margin-top: 30px; }
        table { border-collapse: collapse; width: 100%; background: #313244; margin-bottom: 20px; border-radius: 6px; overflow: hidden; }
        th, td { border: 1px solid #45475a; padding: 10px; text-align: left; }
        th { background: #11111b; color: #89b4fa; }
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.85em; }
        .ok { background: #a6e3a1; color: #11111b; }
        .err { background: #f38ba8; color: #11111b; }
        .warn { background: #f9e2af; color: #11111b; }
    </style>
</head>
<body>

    <h1>Panel Integral de Monitoreo</h1>

    <!-- SECCIÓN 1: DISPOSITIVOS -->
    <h2>Dispositivos Monitoreados</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Estado</th><th>Última Conexión</th></tr>
        </thead>
        <tbody>
            <?php while($d = $dispositivos->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($d['dispositivo_id']) ?></td>
                <td><?= htmlspecialchars($d['nombre']) ?></td>
                <td><span class="badge <?= $d['estado'] == 'Activo' ? 'ok' : 'err' ?>"><?= $d['estado'] ?></span></td>
                <td><?= $d['ultima_conexion'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- SECCIÓN 2: TELEMETRÍA RECIENTE -->
    <h2>Métricas de Telemetría (Últimos Registros)</h2>
    <table>
        <thead>
            <tr><th>ID Dispositivo</th><th>Uso CPU</th><th>Uso Memoria</th><th>Fecha</th></tr>
        </thead>
        <tbody>
            <?php while($t = $telemetria->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($t['dispositivo_id']) ?></td>
                <td><?= $t['cpu'] ?>%</td>
                <td><?= $t['memoria'] ?>%</td>
                <td><?= $t['fecha'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- SECCIÓN 3: CÁMARAS -->
    <h2>Cámaras de Video</h2>
    <table>
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>IP</th><th>Ubicación</th><th>Estado</th><th>Última Comprobación</th></tr>
        </thead>
        <tbody>
            <?php while($c = $camaras->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($c['camara_id']) ?></td>
                <td><?= htmlspecialchars($c['nombre']) ?></td>
                <td><?= htmlspecialchars($c['ip_rtsp']) ?></td>
                <td><?= htmlspecialchars($c['ubicacion']) ?></td>
                <td>
                    <?php 
                        $clase = 'ok';
                        if ($c['estado'] === 'Sin conexión') $clase = 'err';
                        if ($c['estado'] === 'Con anomalía') $clase = 'warn';
                    ?>
                    <span class="badge <?= $clase ?>"><?= $c['estado'] ?></span>
                </td>
                <td><?= $c['ultima_comprobacion'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- SECCIÓN 4: EVENTOS / ALERTAS -->
    <h2>Historial de Eventos</h2>
    <table>
        <thead>
            <tr><th>Fecha</th><th>Origen</th><th>Tipo</th><th>Descripción</th></tr>
        </thead>
        <tbody>
            <?php while($e = $eventos->fetch_assoc()): ?>
            <tr>
                <td><?= $e['fecha'] ?></td>
                <td><?= htmlspecialchars($e['camara_id'] ?? $e['dispositivo_id'] ?? 'N/A') ?></td>
                <td><span class="badge warn"><?= htmlspecialchars($e['tipo_evento']) ?></span></td>
                <td><?= htmlspecialchars($e['descripcion']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>