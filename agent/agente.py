import time
import psutil
from datetime import datetime
from heartbeat import enviar_heartbeat
from camaras import comprobar_y_enviar_camaras

# 1. Definimos la lista de dispositivos a monitorear
DISPOSITIVOS = [
    {"id": "PC-001", "ip": "127.0.0.1"}
]

API_URL = "http://localhost/titulo/api.php"

def obtener_metricas(dispositivo_id):
    # Nota: psutil.cpu_percent() toma las métricas del equipo local.
    # Si monitoreas otros equipos por red, deberías consultar su API/SNMP correspondiente.
    return {
        "dispositivo_id": dispositivo_id,
        "cpu": psutil.cpu_percent(interval=1),
        "memoria": psutil.virtual_memory().percent,
        "fecha": datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    }

# ==========================================
# BUCLE PRINCIPAL DE EJECUCIÓN
# ==========================================
while True:
    print("\n--- Ejecutando ciclo de monitoreo ---")

    # Recorremos cada dispositivo de la lista
    for disp in DISPOSITIVOS:
        dispositivo_id = disp["id"]
        print(f"Enviando telemetría para: {dispositivo_id}")
        
        metricas = obtener_metricas(dispositivo_id)
        enviar_heartbeat(metricas)

    # Comprobación de cámaras
    comprobar_y_enviar_camaras()

    # Pausa entre ciclos completos
    time.sleep(1)
   
