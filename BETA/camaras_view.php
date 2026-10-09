<?php
$conn = new mysqli("localhost", "root", "", "schema");

if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

$camaras = $conn->query("SELECT * FROM camaras ORDER BY ultima_comprobacion DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5"> <!-- Recarga automática cada 5 segundos -->
    <title>Monitoreo de Cámaras</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f8f9fa; color: #333; }
        h2 { color: #0d6efd; border-bottom: 2px solid #0d6efd; padding-bottom: 5px; }
        table { border-collapse: collapse; width: 100%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #dee2e6; padding: 10px; text-align: left; }
        th { background-color: #e9ecef; }
        
        /* Estilos de badges para estados */
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.9em; }
        .operativa { background-color: #d1e7dd; color: #0f5132; }
        .sin-conexion { background-color: #f8d7da; color: #842029; }
        .anomalia { background-color: #fff3cd; color: #664d03; }
    </style>
</head>
<body>

    <h2>Estado de Cámaras Monitoreadas</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>IP / URL</th>
                <th>Ubicación</th>
                <th>Estado</th>
                <th>Última Comprobación</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($camaras && $camaras->num_rows > 0): ?>
                <?php while($c = $camaras->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($c['camara_id']) ?></td>
                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                    <td><?= htmlspecialchars($c['ip_rtsp']) ?></td>
                    <td><?= htmlspecialchars($c['ubicacion']) ?></td>
                    <td>
                        <?php 
                            $clase = 'operativa';
                            if ($c['estado'] === 'Sin conexión') $clase = 'sin-conexion';
                            if ($c['estado'] === 'Con anomalía') $clase = 'anomalia';
                        ?>
                        <span class="badge <?= $clase ?>"><?= htmlspecialchars($c['estado']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($c['ultima_comprobacion']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No hay cámaras registradas en el sistema.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>