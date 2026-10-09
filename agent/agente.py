import time
import psutil
import socket
from datetime import datetime
# Importaciones desde el módulo agent (o locales si están en la misma carpeta)
from heartbeat import enviar_heartbeat
from camaras import comprobar_y_enviar_camaras

# ==========================================
# CONFIGURACIÓN GENERAL (Al inicio del script)
# ==========================================
# DISPOSITIVO_ID = "PC-001"
DISPOSITIVO_ID = socket.gethostname().upper()
API_URL = "http://localhost/titulo/api.php"


def obtener_metricas():
    return {
        "dispositivo_id": DISPOSITIVO_ID,
        "cpu": psutil.cpu_percent(interval=1),  # Pausa de 1 segundo para medir la CPU
        "memoria": psutil.virtual_memory().percent,
        "fecha": datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    }

# ==========================================
# BUCLE PRINCIPAL DE EJECUCIÓN
# ==========================================
while True:
    print("\n--- Ejecutando ciclo de monitoreo ---")

    # 1. Monitoreo de Host / Agente
    metricas = obtener_metricas()
    enviar_heartbeat(metricas)

    # 2. Comprobación periódica de cámaras
    comprobar_y_enviar_camaras()

    # 3. Pausa entre ciclos
    time.sleep(1)
