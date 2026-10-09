import requests

API_URL = "http://localhost/titulo/api.php"

def enviar_heartbeat(metricas):
    print(f"[MÉTRICAS] Dispositivo: {metricas['dispositivo_id']} | CPU: {metricas['cpu']}% | Memoria: {metricas['memoria']}%")
    
    payload = {
        "accion": "telemetria",
        "datos": metricas
    }
    
    try:
        res = requests.post(API_URL, json=payload, timeout=5)
        res.raise_for_status()
        print(" -> Telemetría enviada con éxito:", res.json().get("mensaje"))
    except Exception as e:
        print(f"Error al enviar heartbeat: {e}")