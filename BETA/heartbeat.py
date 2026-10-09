import requests

def enviar_heartbeat(metricas):
    # 1. Imprimir lo recolectado
    print(f"[MÉTRICAS] Dispositivo: {metricas.get('dispositivo_id')} | CPU: {metricas.get('cpu')}% | Memoria: {metricas.get('memoria')}%")

    try:
        # 2. Enviar directamente el diccionario 'metricas' que ya construyó agente.py
        respuesta = requests.post(
            "http://localhost/titulo/telemetria.php", 
            json=metricas, 
            timeout=5
        )
        respuesta.raise_for_status()
        
        print("Respuesta de la API:")
        print(respuesta.json())

    except Exception as e:
        print(f"Error al enviar heartbeat: {e}")