<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5">
    <title>Panel de Monitoreo - MVC</title>
    
    <!-- Enlace al archivo CSS externo -->
    <link rel="stylesheet" href="public/css/styles.css">

    <style>
        .btn-agente {
            background-color: #89b4fa;
            color: #11111b;
            border: none;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .btn-agente:hover {
            background-color: #b4befe;
        }
        .btn-agente:disabled {
            background-color: #6c7086;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

    <!-- NAV CON ENLACES A MANTENEDORES -->
    <nav style="margin-bottom: 20px; background: #1e1e2e; padding: 10px 15px; border-radius: 6px;">
        <a href="index.php" style="color: #89b4fa; font-weight: bold; margin-right: 20px; text-decoration: none;">📊 Dashboard</a>
        <a href="index.php?accion=mantenedor_dispositivos" style="color: #cdd6f4; font-weight: bold; margin-right: 20px; text-decoration: none;">💻 Mantenedor Dispositivos</a>
        <a href="index.php?accion=mantenedor_camaras" style="color: #cdd6f4; font-weight: bold; margin-right: 20px; text-decoration: none;">📷 Mantenedor Cámaras</a>
        <a href="index.php?accion=mantenedor_eventos" style="color: #cdd6f4; font-weight: bold; text-decoration: none;">🔔 Mantenedor Eventos</a>
    </nav>

    <!-- Botón para ejecutar el agente Python -->
   <!-- SECCIÓN DE CONTROL DEL AGENTE -->
<div style="margin-bottom: 20px; background: #181825; padding: 15px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
    <button id="btn-ejecutar-agente" class="btn-agente" onclick="ejecutarAgente()">
        🚀 Iniciar Agente Python
    </button>
    
    <button id="btn-detener-agente" class="btn-detener" onclick="detenerAgente()">
        🛑 Detener Agente Python
    </button>

    <span id="estado-agente" style="font-weight: bold; color: #a6adc8;"></span>

    <a href="index.php?accion=vaciar_todo" 
   class="btn-vaciar-todo"
   onclick="return confirm('⚠️ ¡ATENCIÓN! Se borrarán TODOS los dispositivos, cámaras e historial de eventos. ¿Deseas continuar?');">
   🗑️ Vaciar Toda la Base de Datos
</a>

<style>
    .btn-vaciar-todo {
        background-color: #f38ba8;
        color: #11111b;
        padding: 10px 18px;
        font-size: 14px;
        font-weight: bold;
        border-radius: 6px;
        text-decoration: none;
        display: inline-block;
        transition: background 0.2s ease;
    }
    .btn-vaciar-todo:hover {
        background-color: #eba0ac;
    }
</style>
</div>

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
            <?php if ($eventos && $eventos->num_rows > 0): ?>
                <?php while($e = $eventos->fetch_assoc()): ?>
                <tr>
                    <td><?= $e['fecha'] ?></td>
                    <td>
                        <?php 
                            $origen = $e['origen_id'] ?? $e['camara_id'] ?? $e['dispositivo_id'] ?? 'N/A';
                            $tipoOrigen = $e['origen_tipo'] ?? 'dispositivo';
                        ?>
                        <span class="badge <?= $tipoOrigen === 'camara' ? 'warn' : 'ok' ?>">
                            <?= $tipoOrigen === 'camara' ? '📷' : '💻' ?> <?= htmlspecialchars($origen) ?>
                        </span>
                    </td>
                    <td><span class="badge err"><?= htmlspecialchars($e['tipo_evento']) ?></span></td>
                    <td><?= htmlspecialchars($e['descripcion']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: #a6adc8;">No hay registros de eventos.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Scripts JavaScript -->
    <script>
function ejecutarAgente() {
    const btn = document.getElementById('btn-ejecutar-agente');
    const estado = document.getElementById('estado-agente');

    btn.disabled = true;
    estado.style.color = '#f9e2af';
    estado.innerText = '⏳ Iniciando agente Python...';

    fetch('index.php?accion=ejecutar_agente', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'ok') {
            estado.style.color = '#a6e3a1';
            estado.innerText = '✅ ' + data.mensaje + ' Recargando página...';
            
            // Espera 1.5 segundos y recarga la página para mostrar los nuevos eventos
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            estado.style.color = '#f38ba8';
            estado.innerText = '❌ Error: ' + data.mensaje;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        estado.style.color = '#f38ba8';
        estado.innerText = '❌ Error de conexión al servidor.';
        btn.disabled = false;
    });
}

function detenerAgente() {
    const btnDetener = document.getElementById('btn-detener-agente');
    const estado = document.getElementById('estado-agente');

    btnDetener.disabled = true;
    estado.style.color = '#f9e2af';
    estado.innerText = '⏳ Deteniendo agente Python...';

    fetch('index.php?accion=detener_agente', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'ok') {
            estado.style.color = '#a6e3a1';
            estado.innerText = '🛑 ' + data.mensaje + ' Recargando página...';
            
            // Espera 1.5 segundos y recarga la página
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            estado.style.color = '#f38ba8';
            estado.innerText = '❌ Error: ' + data.mensaje;
            btnDetener.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        estado.style.color = '#f38ba8';
        estado.innerText = '❌ Error de conexión al servidor.';
        btnDetener.disabled = false;
    });
}
</script>
    <script src="public/js/main.js"></script>
    <script src="public/js/dispositivos.js"></script>
    <script src="public/js/camaras.js"></script>
</body>
</html>