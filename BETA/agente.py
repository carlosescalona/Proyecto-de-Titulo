import time
import psutil
from datetime import datetime
from BETA.heartbeat import enviar_heartbeat
from BETA.camaras import comprobar_y_enviar_camaras

DISPOSITIVO_ID = "PC-001"

def obtener_metricas():
    return {
        "dispositivo_id": DISPOSITIVO_ID,
        "cpu": psutil.cpu_percent(interval=1),  # Ya pausa el código 1 segundo aquí
        "memoria": psutil.virtual_memory().percent,
        "fecha": datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    }

while True:
    print("\n--- Ejecutando ciclo de monitoreo ---")
    
    # 1. Monitoreo de Host / Agente
    metricas = obtener_metricas()
    enviar_heartbeat(metricas)

    # 2. Comprobación periódica de cámaras
    comprobar_y_enviar_camaras()
    
    # 3. Pausa opcional entre ciclos
    time.sleep(1)

    