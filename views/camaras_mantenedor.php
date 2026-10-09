<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mantenedor de Cámaras</title>
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    <nav style="margin-bottom:20px;">
        <a href="index.php" style="color:#89b4fa;">← Volver al Dashboard</a>
    </nav>

    <h1>Mantenedor de Cámaras</h1>

    <!-- FORMULARIO DE CREACIÓN/EDICIÓN -->
    <div style="background:#313244; padding:20px; border-radius:8px; margin-bottom:20px;">
        <h3><?= $editar ? 'Editar Cámara' : 'Registrar Nueva Cámara' ?></h3>
        <form action="index.php?accion=guardar_camara" method="POST">
            <input type="hidden" name="es_edicion" value="<?= $editar ? '1' : '0' ?>">
            
            <label>ID Cámara:</label><br>
            <input type="text" name="camara_id" value="<?= htmlspecialchars($editar['camara_id'] ?? '') ?>" <?= $editar ? 'readonly' : 'required' ?> style="padding:8px; width:300px;"><br><br>

            <label>Nombre:</label><br>
            <input type="text" name="nombre" value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>" required style="padding:8px; width:300px;"><br><br>

            <label>IP / RTSP URL:</label><br>
            <input type="text" name="ip_rtsp" value="<?= htmlspecialchars($editar['ip_rtsp'] ?? '') ?>" required style="padding:8px; width:300px;"><br><br>

            <label>Ubicación:</label><br>
            <input type="text" name="ubicacion" value="<?= htmlspecialchars($editar['ubicacion'] ?? '') ?>" required style="padding:8px; width:300px;"><br><br>

            <button type="submit" style="padding:10px 20px; background:#a6e3a1; border:none; cursor:pointer; font-weight:bold;">Guardar</button>
            <?php if ($editar): ?>
                <a href="index.php?accion=mantenedor_camaras" style="color:#f38ba8; margin-left:10px;">Cancelar</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- TABLA DE LISTADO -->
    <table>
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>IP/Fuente</th><th>Ubicación</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
            <?php while($c = $camaras->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($c['camara_id']) ?></td>
                <td><?= htmlspecialchars($c['nombre']) ?></td>
                <td><?= htmlspecialchars($c['ip_rtsp']) ?></td>
                <td><?= htmlspecialchars($c['ubicacion']) ?></td>
                <td><span class="badge <?= $c['estado'] === 'Operativa' ? 'ok' : 'err' ?>"><?= $c['estado'] ?></span></td>
                <td>
                    <a href="index.php?accion=mantenedor_camaras&editar_id=<?= urlencode($c['camara_id']) ?>" style="color:#f9e2af;">Editar</a> | 
                    <a href="index.php?accion=eliminar_camara&id=<?= urlencode($c['camara_id']) ?>" onclick="return confirm('¿Eliminar cámara?');" style="color:#f38ba8;">Eliminar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>