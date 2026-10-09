import cv2
import numpy as np

HISTORIAL_FRAMES = {}
CONTADOR_CONGELAMIENTO = {}

# Configuración mejorada
UMBRAL_DIFERENCIA = 0.8   # Ajustado para tolerar variaciones mínimas de sensor
UMBRAL_REPETICIONES = 3   # Requiere 3 ciclos consecutivos sin cambios para alertar

def evaluar_congelamiento(camara_id, frame_actual):
    global HISTORIAL_FRAMES, CONTADOR_CONGELAMIENTO

    if frame_actual is None:
        return False, "Sin frame disponible"

    # 1. Escala de grises y desenfoque suave para eliminar ruido electrónico del sensor
    gris_actual = cv2.cvtColor(frame_actual, cv2.COLOR_BGR2GRAY)
    gris_actual = cv2.GaussianBlur(gris_actual, (5, 5), 0)

    # 2. Captura inicial de la cámara
    if camara_id not in HISTORIAL_FRAMES:
        HISTORIAL_FRAMES[camara_id] = gris_actual
        CONTADOR_CONGELAMIENTO[camara_id] = 0
        return False, "Captura inicial registrada"

    gris_referencia = HISTORIAL_FRAMES[camara_id]

    # 3. Calcular diferencia absoluta con la imagen de referencia fija
    diferencia = cv2.absdiff(gris_referencia, gris_actual)
    puntaje_diferencia = np.mean(diferencia)

    # 4. Evaluar cambio respecto al umbral
    if puntaje_diferencia < UMBRAL_DIFERENCIA:
        # No hubo cambio relevante: se incrementa el contador y NO se actualiza la referencia
        CONTADOR_CONGELAMIENTO[camara_id] += 1
    else:
        # Hubo movimiento real: reinicia contador y actualiza la imagen de referencia
        CONTADOR_CONGELAMIENTO[camara_id] = 0
        HISTORIAL_FRAMES[camara_id] = gris_actual

    # 5. Determinar estado según repeticiones acumuladas
    if CONTADOR_CONGELAMIENTO[camara_id] >= UMBRAL_REPETICIONES:
        return True, f"Imagen congelada (Diferencia acumulada: {puntaje_diferencia:.2f})"

    return False, f"Imagen dinámica (Diferencia: {puntaje_diferencia:.2f})"