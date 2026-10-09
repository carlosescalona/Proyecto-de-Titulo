<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mantenedor de Eventos / Alertas</title>
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    <!-- NAV BAR -->
    <nav style="margin-bottom: 20px; background: #1e1e2e; padding: 10px 15px; border-radius: 6px;">
        <a href="index.php" style="color: #cdd6f4; font-weight: bold; margin-right: 20px; text-decoration: none;">📊 Dashboard</a>
        <a href="index.php?accion=mantenedor_dispositivos" style="color: #cdd6f4; font-weight: bold; margin-right: 20px; text-decoration: none;">💻 Mantenedor Dispositivos</a>
        <a href="index.php?accion=mantenedor_camaras" style="color: #cdd6f4; font-weight: bold; margin-right: 20px; text-decoration: none;">📷 Mantenedor Cámaras</a>
        <a href="index.php?accion=mantenedor_eventos" style="color: #89b4fa; font-weight: bold; text-decoration: none;">🔔 Mantenedor Eventos</a>
    </nav>

    <h1>Mantenedor de Eventos y Alertas</h1>

    <!-- FORMULARIO DE FILTROS -->
    <div style="background:#313244; padding:15px; border-radius:8px; margin-bottom:20px;">
        <h3>Filtrar Registro de Eventos</h3>
        <form action="index.php" method="GET" style="display:flex; gap:15px; align-items:center;">
            <input type="hidden" name="accion" value="mantenedor_eventos">
            
            <div>
                <label>Tipo de Evento:</label><br>
                <select name="tipo" style="padding:8px;">
                    <option value="">-- Todos --</option>
                    <option value="PERDIDA_CONEXION" <?= ($_GET['tipo'] ?? '') === 'PERDIDA_CONEXION' ? 'selected' : '' ?>>PERDIDA_CONEXION</option>
                    <option value="PERDIDA_IMAGEN" <?= ($_GET['tipo'] ?? '') === 'PERDIDA_IMAGEN' ? 'selected' : '' ?>>PERDIDA_IMAGEN</option>
                    <option value="IMAGEN_CONGELADA" <?= ($_GET['tipo'] ?? '') === 'IMAGEN_CONGELADA' ? 'selected' : '' ?>>IMAGEN_CONGELADA</option>
                    <option value="CAMARA_ANOMALIA" <?= ($_GET['tipo'] ?? '') === 'CAMARA_ANOMALIA' ? 'selected' : '' ?>>CAMARA_ANOMALIA</option>
                </select>
            </div>

            <div>
                <label>Desde:</label><br>
                <input type="date" name="desde" value="<?= htmlspecialchars($_GET['desde'] ?? '') ?>" style="padding:7px;">
            </div>

            <div>
                <label>Hasta:</label><br>
                <input type="date" name="hasta" value="<?= htmlspecialchars($_GET['hasta'] ?? '') ?>" style="padding:7px;">
            </div>

            <div style="margin-top:18px;">
                <button type="submit" style="padding:8px 15px; background:#89b4fa; border:none; color:#11111b; font-weight:bold; cursor:pointer;">Filtrar</button>
                <a href="index.php?accion=mantenedor_eventos" style="color:#a6adc8; margin-left:10px; text-decoration:none;">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- BOTON VACIAR TODO -->
    <div style="margin-bottom:15px; text-align:right;">
        <a href="index.php?accion=vaciar_eventos" onclick="return confirm('¿Está seguro de borrar TODO el historial de eventos?');" style="background:#f38ba8; color:#11111b; padding:8px 12px; font-weight:bold; text-decoration:none; border-radius:4px;">🗑️ Vaciar Historial Completo</a>
    </div>

    <!-- TABLA DE EVENTOS -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha y Hora</th>
                <th>Origen</th>
                <th>Tipo de Evento</th>
                <th>Descripción</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($eventos && $eventos->num_rows > 0): ?>
                <?php while($e = $eventos->fetch_assoc()): ?>
                <tr>
                    <td><?= $e['id'] ?></td>
                    <td><?= $e['fecha'] ?></td>
                    <td><?= htmlspecialchars($e['camara_id'] ?? $e['dispositivo_id'] ?? 'N/A') ?></td>
                    <td><span class="badge warn"><?= htmlspecialchars($e['tipo_evento']) ?></span></td>
                    <td><?= htmlspecialchars($e['descripcion']) ?></td>
                    <td>
                        <a href="index.php?accion=eliminar_evento&id=<?= $e['id'] ?>" onclick="return confirm('¿Eliminar esta alerta?');" style="color:#f38ba8;">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; color:#a6adc8;">No se encontraron eventos o alertas con los criterios seleccionados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>