import os
import sys
import time
import psutil
from datetime import datetime

# 1. Silenciar advertencias de OpenCV antes de cualquier import de cámaras
os.environ["OPENCV_LOG_LEVEL"] = "SILENT"

# 2. Activar modo Matrix (Fondo negro, letras verde brillante) en Windows
if os.name == 'nt':
    os.system('color 0A')

from config import DISPOSITIVOS
from heartbeat import enviar_heartbeat
from camaras import comprobar_y_enviar_camaras

# Estilos de texto ANSI
VERDE_BRIGHT = "\033[1;32m"
RESET = "\033[0m"

def obtener_metricas(dispositivo_id):
    # Obtener uso de disco en la partición principal
    disco = psutil.disk_usage('/')

    return {
        "dispositivo_id": dispositivo_id,
        "cpu": psutil.cpu_percent(interval=0.1),
        "memoria": psutil.virtual_memory().percent,
        "disco": disco.percent,  # Porcentaje de uso de disco
        "fecha": datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    }

print(f"{VERDE_BRIGHT}=== INICIANDO AGENTE MATRIX DE MONITOREO ==={RESET}")

while True:
    print(f"\n{VERDE_BRIGHT}--- Ejecutando ciclo de monitoreo ---{RESET}")

    # 1. Monitoreo de PCs
    for pc in DISPOSITIVOS:
        disp_id = pc["dispositivo_id"]
        metricas = obtener_metricas(disp_id)
        enviar_heartbeat(metricas)

    # 2. Monitoreo de Cámaras
    comprobar_y_enviar_camaras()

    # 3. Pausa entre ciclos completos
    time.sleep(2)