<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5">
    <title>Panel de Monitoreo - MVC</title>
    
    <!-- Enlace al archivo CSS externo -->
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>

    <!-- NAV CON ENLACES A MANTENEDORES -->
   <nav style="margin-bottom: 20px; background: #1e1e2e; padding: 10px 15px; border-radius: 6px;">
    <a href="index.php" style="color: #89b4fa; font-weight: bold; margin-right: 20px; text-decoration: none;">📊 Dashboard</a>
    <a href="index.php?accion=mantenedor_dispositivos" style="color: #cdd6f4; font-weight: bold; margin-right: 20px; text-decoration: none;">💻 Mantenedor Dispositivos</a>
    <a href="index.php?accion=mantenedor_camaras" style="color: #cdd6f4; font-weight: bold; margin-right: 20px; text-decoration: none;">📷 Mantenedor Cámaras</a>
    <a href="index.php?accion=mantenedor_eventos" style="color: #cdd6f4; font-weight: bold; text-decoration: none;">🔔 Mantenedor Eventos</a>
</nav>

    <h1>Panel Integral de Monitoreo (MVC)</h1>

    <!-- 1. SECCIÓN DISPOSITIVOS -->
    <h2>Dispositivos</h2>
    <table id="tabla-dispositivos">
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Estado</th><th>Última Conexión</th></tr>
        </thead>
        <tbody>
            <?php while($d = $dispositivos->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($d['dispositivo_id']) ?></td>
                <td><?= htmlspecialchars($d['nombre']) ?></td>
                <td><span class="badge <?= $d['estado'] === 'Activo' ? 'ok' : 'err' ?>"><?= $d['estado'] ?></span></td>
                <td><?= $d['ultima_conexion'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- 2. SECCIÓN TELEMETRÍA -->
    <h2>Telemetría - Últimos 10 Reportes (CPU / RAM)</h2>
    <table id="tabla-telemetria">
        <thead>
            <tr>
                <th>Dispositivo</th>
                <th>Nombre</th>
                <th>Uso CPU</th>
                <th>Uso Memoria RAM</th>
                <th>Fecha y Hora</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($telemetria && $telemetria->num_rows > 0): ?>
                <?php while($t = $telemetria->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($t['dispositivo_id']) ?></strong></td>
                    <td><?= htmlspecialchars($t['nombre'] ?? 'N/A') ?></td>
                    <td>
                        <span class="badge <?= $t['cpu'] > 85 ? 'err' : ($t['cpu'] > 60 ? 'warn' : 'ok') ?>">
                            <?= number_format($t['cpu'], 1) ?>%
                        </span>
                    </td>
                    <td>
                        <span class="badge <?= $t['memoria'] > 85 ? 'err' : ($t['memoria'] > 70 ? 'warn' : 'ok') ?>">
                            <?= number_format($t['memoria'], 1) ?>%
                        </span>
                    </td>
                    <td><?= $t['fecha'] ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; color: #a6adc8;">No hay registros de telemetría.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- 3. SECCIÓN CÁMARAS -->
    <h2>Cámaras</h2>
    <table id="tabla-camaras">
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Ubicación</th><th>Estado</th><th>Última Comprobación</th></tr>
        </thead>
        <tbody>
            <?php while($c = $camaras->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($c['camara_id']) ?></td>
                <td><?= htmlspecialchars($c['nombre']) ?></td>
                <td><?= htmlspecialchars($c['ubicacion']) ?></td>
                <td>
                    <?php 
                        $clase = 'ok';
                        if (in_array($c['estado'], ['Sin conexión', 'Sin imagen'])) $clase = 'err';
                        if ($c['estado'] === 'Con anomalía') $clase = 'warn';
                    ?>
                    <span class="badge <?= $clase ?>"><?= htmlspecialchars($c['estado']) ?></span>
                </td>
                <td><?= $c['ultima_comprobacion'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- 4. SECCIÓN EVENTOS / ALERTAS -->
    <h2>Eventos / Alertas</h2>
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

    <!-- Inclusión de Scripts JavaScript externos -->
    <script src="public/js/main.js"></script>
    <script src="public/js/dispositivos.js"></script>
    <script src="public/js/camaras.js"></script>
</body>
</html>