import cv2
import requests
from datetime import datetime
from BETA.analizador_congelamiento import evaluar_congelamiento

CAMARAS_CONFIG = [
    {
        "camara_id": "CAM-001",
        "nombre": "Cámara PC",
        "ip": "127.0.0.1",
        "fuente": 0,  # <-- Forzar lectura de imagen fija  "fuente": 0, "fuente": "imagen_prueba.jpg" # 0 para webcam local, o URL RTSP / ruta de imagen
        "ubicacion": "Oficina Principal"
    }
]

# Cambiar a True solo si estás probando manualmente y quieres ver la ventana emergente
MOSTRAR_VENTANA = False 

def comprobar_y_enviar_camaras():
    for cam in CAMARAS_CONFIG:
        cam_id = cam["camara_id"]
        fuente = cam["fuente"]
        
        # Cargar imagen o webcam
        if isinstance(fuente, str) and fuente.endswith(('.jpg', '.png', '.jpeg')):
            frame = cv2.imread(fuente)
            ret = frame is not None
        else:
            cap = cv2.VideoCapture(fuente)
            ret, frame = cap.read()
            cap.release()

        if ret:
            # Solo mostrar ventana si está en modo desarrollo/pruebas
            if MOSTRAR_VENTANA:
                cv2.imshow(f"Vista en Vivo - {cam_id}", frame)
                cv2.waitKey(1)

            # Evaluar congelamiento
            esta_congelada, msg = evaluar_congelamiento(cam_id, frame)
            
            if esta_congelada:
                estado = "Con anomalía"
                detalle = f"Imagen congelada detectada. {msg}"
            else:
                estado = "Operativa"
                detalle = msg
        else:
            estado = "Sin conexión"
            detalle = f"No se pudo cargar la imagen/fuente: {fuente}"

        # Enviar reporte a la API
        datos = {
            "camara_id": cam_id,
            "nombre": cam["nombre"],
            "ip": cam["ip"],
            "ubicacion": cam["ubicacion"],
            "estado": estado,
            "detalle": detalle,
            "fecha": datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        }

        try:
            res = requests.post("http://localhost/titulo/api_camaras.php", json=datos, timeout=5)
            res.raise_for_status()
            print(f"[CÁMARA] {cam_id} -> Estado: {estado} | {detalle}")
        except Exception as e:
            print(f"Error enviando estado de cámara: {e}")