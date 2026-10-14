import cv2
import requests
import time
from datetime import datetime
from config import API_URL  # Usamos la constante centralizada
from analizador_congelamiento import evaluar_congelamiento

# Diccionario para controlar el tiempo desde que una cámara empezó a fallar
fallas_inicio = {}

# Diccionario para mantener abiertas las conexiones de cámaras/webcams
capturas_activas = {}

TIMEOUT_SIN_IMAGEN = 1  # Segundos tolerados antes de declarar "Sin imagen"

CAMARAS_CONFIG = [
    {
        "camara_id": "CAM-prueba",
        "nombre": "Cámara prueba",
        "ip": "127.0.0.1",
        "fuente": "imagen_prueba.jpg",
        "ubicacion": "Imagen de prueba"
    },
    {
        "camara_id": "CAM-002",
        "nombre": "Cámara USB Secundaria",
        "ip": "127.0.0.1",
        "fuente": 0,  # 0 para Webcam local o URL RTSP
        "ubicacion": "Oficina Central"
    },
    {
        "camara_id": "CAM-003",
        "nombre": "Cámara Pc-001",
        "ip": "127.0.0.1",
        "fuente": 1,  # Índice 1: Segunda cámara conectada por USB
        "ubicacion": "Recepción"
    }  
]

def es_frame_valido(frame):
    """Verifica si la imagen existe y no es completamente negra / vacía"""
    if frame is None or frame.size == 0:
        return False
    if cv2.countNonZero(cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)) == 0:
        return False
    return True

def obtener_frame(cam_id, fuente):
    """Obtiene el frame reutilizando la captura si es un dispositivo de video/webcam"""
    if isinstance(fuente, str) and fuente.endswith(('.jpg', '.png', '.jpeg')):
        frame = cv2.imread(fuente)
        return frame is not None, frame
    else:
        if cam_id not in capturas_activas or not capturas_activas[cam_id].isOpened():
            capturas_activas[cam_id] = cv2.VideoCapture(fuente)
        
        cap = capturas_activas[cam_id]
        ret, frame = cap.read()
        
        # Si la cámara física se desconectó, liberamos para reintentar abrir en el próximo ciclo
        if not ret:
            cap.release()
            capturas_activas.pop(cam_id, None)
            
        return ret, frame

def comprobar_y_enviar_camaras():
    tiempo_actual = time.time()

    for cam in CAMARAS_CONFIG:
        cam_id = cam["camara_id"]
        fuente = cam["fuente"]

        # Intento de captura de frame
        ret, frame = obtener_frame(cam_id, fuente)
        imagen_valida = ret and es_frame_valido(frame)

        if imagen_valida:
            if cam_id in fallas_inicio:
                del fallas_inicio[cam_id]

            esta_congelada, msg = evaluar_congelamiento(cam_id, frame)
            estado = "Con anomalía" if esta_congelada else "Operativa"
            detalle = f"Imagen congelada detectada. {msg}" if esta_congelada else msg

        else:
            if cam_id not in fallas_inicio:
                fallas_inicio[cam_id] = tiempo_actual

            tiempo_fallando = tiempo_actual - fallas_inicio[cam_id]

            if tiempo_fallando >= TIMEOUT_SIN_IMAGEN:
                estado = "Sin imagen"
                detalle = f"Pérdida de señal detectada. La cámara no entrega una imagen válida desde hace {int(tiempo_fallando)}s."
            else:
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

        # --- ENVÍO DENTRO DEL BUCLE FOR ---
        try:
            res = requests.post(API_URL, json=payload, timeout=5)
            res.raise_for_status()

            try:
                data = res.json()
                print(f" -> Estado cámara {cam_id} [{estado}]: {data.get('mensaje')}")
            except ValueError:
                print(f"Error: La API no devolvió un JSON válido para {cam_id}. Respuesta recibida:\n{res.text}")
        except requests.exceptions.RequestException as e:
            print(f"Error de red/HTTP enviando estado de cámara {cam_id}: {e}")