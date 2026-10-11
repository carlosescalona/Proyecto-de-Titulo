import socket

API_URL = "http://localhost/titulo/api.php"

# Tu PC local detectado automáticamente
MI_PC = socket.gethostname().upper()

# Lista que incluye tu PC + las otras PCs que quieras monitorear
DISPOSITIVOS = [
    {"dispositivo_id": MI_PC, "nombre": f"Mi PC Local ({MI_PC})", "activo": True},
    {"dispositivo_id": "PC-BODEGA-01", "nombre": "Equipo Bodega", "activo": True},
    {"dispositivo_id": "PC-RECEPCION", "nombre": "Equipo Recepción", "activo": False} 
    ]