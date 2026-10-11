import time
import psutil
from datetime import datetime
from config import DISPOSITIVOS
from heartbeat import enviar_heartbeat
from camaras import comprobar_y_enviar_camaras

def obtener_metricas(dispositivo_id):
    return {
        "dispositivo_id": dispositivo_id,
        "cpu": psutil.cpu_percent(interval=0.1),
        "memoria": psutil.virtual_memory().percent,
        "fecha": datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    }

while True:
    print("\n--- Ejecutando ciclo de monitoreo ---")

    # 1. Monitoreo de PCs (Tu equipo + las demás PCs configuradas)
    for pc in DISPOSITIVOS:
        disp_id = pc["dispositivo_id"]
        metricas = obtener_metricas(disp_id)
        enviar_heartbeat(metricas)

    # 2. Monitoreo de Cámaras
    comprobar_y_enviar_camaras()

    # 3. Pausa entre ciclos completos
    time.sleep(2)
