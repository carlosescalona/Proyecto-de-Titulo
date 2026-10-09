<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mantenedor de Dispositivos</title>
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    <nav style="margin-bottom:20px;">
        <a href="index.php" style="color:#89b4fa;">← Volver al Dashboard</a>
    </nav>

    <h1>Mantenedor de Dispositivos</h1>

    <!-- FORMULARIO DE CREACIÓN/EDICIÓN -->
    <div style="background:#313244; padding:20px; border-radius:8px; margin-bottom:20px;">
        <h3><?= $editar ? 'Editar Dispositivo' : 'Registrar Nuevo Dispositivo' ?></h3>
        <form action="index.php?accion=guardar_dispositivo" method="POST">
            <input type="hidden" name="es_edicion" value="<?= $editar ? '1' : '0' ?>">
            
            <label>ID Dispositivo:</label><br>
            <input type="text" name="dispositivo_id" value="<?= htmlspecialchars($editar['dispositivo_id'] ?? '') ?>" <?= $editar ? 'readonly' : 'required' ?> style="padding:8px; width:300px;"><br><br>

            <label>Nombre:</label><br>
            <input type="text" name="nombre" value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>" required style="padding:8px; width:300px;"><br><br>

            <label>Tipo:</label><br>
            <input type="text" name="tipo" value="<?= htmlspecialchars($editar['tipo'] ?? 'Agente Monitoreo') ?>" required style="padding:8px; width:300px;"><br><br>

            <label>Ubicación:</label><br>
            <input type="text" name="ubicacion" value="<?= htmlspecialchars($editar['ubicacion'] ?? 'Oficina') ?>" required style="padding:8px; width:300px;"><br><br>

            <button type="submit" style="padding:10px 20px; background:#a6e3a1; border:none; cursor:pointer; font-weight:bold;">Guardar</button>
            <?php if ($editar): ?>
                <a href="index.php?accion=mantenedor_dispositivos" style="color:#f38ba8; margin-left:10px;">Cancelar</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- TABLA DE LISTADO -->
    <table>
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Ubicación</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php while($d = $dispositivos->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($d['dispositivo_id']) ?></td>
                <td><?= htmlspecialchars($d['nombre']) ?></td>
                <td><?= htmlspecialchars($d['tipo']) ?></td>
                <td><?= htmlspecialchars($d['ubicacion']) ?></td>
                <td><span class="badge <?= $d['estado'] === 'Activo' ? 'ok' : 'err' ?>"><?= $d['estado'] ?></span></td>
                <td>
                    <a href="index.php?accion=mantenedor_dispositivos&editar_id=<?= urlencode($d['dispositivo_id']) ?>" style="color:#f9e2af;">Editar</a> | 
                    <a href="index.php?accion=eliminar_dispositivo&id=<?= urlencode($d['dispositivo_id']) ?>" onclick="return confirm('¿Eliminar dispositivo?');" style="color:#f38ba8;">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>