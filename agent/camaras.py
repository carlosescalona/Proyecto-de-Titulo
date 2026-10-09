import cv2
import requests
import time
from datetime import datetime
from analizador_congelamiento import evaluar_congelamiento

API_URL = "http://localhost/titulo/api.php"

# Diccionario para controlar el tiempo desde que una cámara empezó a fallar
fallas_inicio = {}

# Configuración con tiempo límite en segundos para declarar "Sin imagen"
TIMEOUT_SIN_IMAGEN = 5  # Segundos tolerados antes de declarar "Sin imagen"

CAMARAS_CONFIG = [
    {
        "camara_id": "CAM-001",
        "nombre": "Cámara Pc-001",
        "ip": "127.0.0.1",
        "fuente": 0,  # Cambia a una ruta no válida como "rtsp://invalid" para probar la falla
        "ubicacion": "Oficina Central"
    }   
]

def es_frame_valido(frame):
    """Verifica si la imagen existe y no es completamente negra / vacía"""
    if frame is None or frame.size == 0:
        return False
    # Opcional: verificar si la imagen no es un frame totalmente negro
    if cv2.countNonZero(cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)) == 0:
        return False
    return True

def comprobar_y_enviar_camaras():
    tiempo_actual = time.time()

    for cam in CAMARAS_CONFIG:
        cam_id = cam["camara_id"]
        fuente = cam["fuente"]

        # Intento de captura de frame
        if isinstance(fuente, str) and fuente.endswith(('.jpg', '.png', '.jpeg')):
            frame = cv2.imread(fuente)
            ret = frame is not None
        else:
            cap = cv2.VideoCapture(fuente)
            ret, frame = cap.read()
            cap.release()

        imagen_valida = ret and es_frame_valido(frame)

        if imagen_valida:
            # Si recupera la imagen, reiniciamos el contador de falla
            if cam_id in fallas_inicio:
                del fallas_inicio[cam_id]

            esta_congelada, msg = evaluar_congelamiento(cam_id, frame)
            estado = "Con anomalía" if esta_congelada else "Operativa"
            detalle = f"Imagen congelada detectada. {msg}" if esta_congelada else msg

        else:
            # Si no entrega imagen válida, medimos el tiempo de la falla
            if cam_id not in fallas_inicio:
                fallas_inicio[cam_id] = tiempo_actual

            tiempo_fallando = tiempo_actual - fallas_inicio[cam_id]

            if tiempo_fallando >= TIMEOUT_SIN_IMAGEN:
                estado = "Sin imagen"
                detalle = f"Pérdida de señal detectada. La cámara no entrega una imagen válida desde hace {int(tiempo_fallando)}s."
            else:
                # Período de gracia antes de cumplir el timeout
                estado = "Operativa" 
                detalle = "Reintentando captura de imagen..."

        payload = {
            "accion": "camaras",
            "datos": {
                "camara_id": cam_id,
                "nombre": cam["nombre"],
                "ip": cam["ip"],
                "ubicacion": cam["ubicacion"],
                "estado": estado,
                "detalle": detalle,
                "fecha": datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            }
        }

        try:
            res = requests.post(API_URL, json=payload, timeout=5)
            res.raise_for_status()
            print(f" -> Estado cámara {cam_id} [{estado}]: {res.json().get('mensaje')}")
        except Exception as e:
            print(f"Error enviando estado de cámara {cam_id}: {e}")