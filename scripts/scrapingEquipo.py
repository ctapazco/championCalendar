from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from urllib.parse import urlparse
import time
import mysql.connector
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

# Configurar Selenium y abrir la URL
driver_path = '/home/ctapasco/chromedriver/chromedriver-linux64/chromedriver'
service = Service(driver_path)
driver = webdriver.Chrome(service=service)

# Conectar a la base de datos MySQL
try:
    db_connection = mysql.connector.connect(
        host="localhost",
        user="ctapasco",  
        password="admin123.", 
        database="calendarioFut"
    )
    cursor = db_connection.cursor()
except mysql.connector.Error as e:
    print(f"Error al conectar a la base de datos: {e}")
    driver.quit()
    exit()

# Obtener las URLs de los equipos desde la base de datos
cursor.execute("SELECT id, url_perfil FROM equipos")
equipos = cursor.fetchall()

# Función para obtener el Flashscore ID desde la URL y añadir "/squad/"
def obtener_flashscore_id(url):
    path = urlparse(url).path  # Extrae el path de la URL
    partes = path.split("/")  # Divide la URL por "/"
    if len(partes) > 3:  
        flashscore_id = partes[3]  # Extrae el identificador de equipo
        return flashscore_id + "/squad/"  # Añadir "/squad/" al final
    return None

# Función para verificar si la edad es un número válido
def es_numero_valido(valor):
    try:
        # Verificamos si el valor es un número entero
        if valor is not None:
            return int(valor) > 0  # Si el valor puede convertirse en un entero positivo
        return False
    except ValueError:
        return False

# Iterar sobre cada equipo y extraer los datos
for equipo in equipos:
    equipo_id, url = equipo
    print(f"Procesando equipo con URL: {url}")

    # Extraer el Flashscore ID y añadir "/squad/"
    flashscore_id = obtener_flashscore_id(url)

    # Abrir la URL del equipo
    driver.get(url)
    time.sleep(5)  # Espera para asegurarse de que la página cargue completamente

    # Obtener la URL para la plantilla de jugadores
    squad_url = url + 'squad/'  # Añadir /squad/
    print(f"Scrapeando plantilla de jugadores: {squad_url}")
    driver.get(squad_url)
    time.sleep(5)

    # Esperar explícitamente a que la tabla de jugadores esté visible
    WebDriverWait(driver, 20).until(
        EC.presence_of_element_located((By.CSS_SELECTOR, '.lineupTable'))
    )

    # Buscar todas las filas de jugadores en la tabla
    jugadores = driver.find_elements(By.CLASS_NAME, 'lineupTable__row')
    jugadores_encontrados = False

    for jugador in jugadores:
        try:
            # Verificar si el jugador tiene el texto "Coach" o si la celda es vacía
            nombre_element = jugador.find_element(By.CLASS_NAME, 'lineupTable__cell--name')
            nombre = nombre_element.text.strip() if nombre_element.text.strip() else None
            
            if nombre == "Coach" or nombre is None: 
                continue  # Ignorar las filas que corresponden a entrenadores

            jugadores_encontrados = True  # Si encontramos jugadores, marcamos como encontrado

            # Extraer los otros datos del jugador (edad, dorsal, etc.)
            dorsal_element = jugador.find_element(By.CLASS_NAME, 'lineupTable__cell--jersey')
            dorsal = dorsal_element.text.strip() if dorsal_element.text.strip() else None

            edad_element = jugador.find_element(By.CLASS_NAME, 'lineupTable__cell--age')
            edad = edad_element.text.strip() if edad_element.text.strip() else None

            # Verificar si la edad es válida
            if not es_numero_valido(edad):
                edad = None  # Asignar None si la edad no es válida

            partidos = jugador.find_element(By.CLASS_NAME, 'lineupTable__cell--matchesPlayed').text.strip()
            minutos = jugador.find_element(By.CLASS_NAME, 'lineupTable__cell--minutesPlayed').text.strip()
            goles = jugador.find_element(By.CLASS_NAME, 'lineupTable__cell--goal').text.strip()
            asistencias = jugador.find_element(By.CLASS_NAME, 'lineupTable__cell--assist').text.strip()
            tarjetas_amarillas = jugador.find_element(By.CLASS_NAME, 'lineupTable__cell--yellowCard').text.strip()
            tarjetas_rojas = jugador.find_element(By.CLASS_NAME, 'lineupTable__cell--redCard').text.strip()

            # Guardar los datos en la base de datos
            cursor.execute("""
                INSERT INTO jugadores (nombre, edad, dorsal, minutos_jugados, goles, asistencias, tarjetas_amarillas, tarjetas_rojas, equipo_id)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
                ON DUPLICATE KEY UPDATE 
                    edad=VALUES(edad), minutos_jugados=VALUES(minutos_jugados), 
                    goles=VALUES(goles), asistencias=VALUES(asistencias), tarjetas_amarillas=VALUES(tarjetas_amarillas), tarjetas_rojas=VALUES(tarjetas_rojas)
            """, (nombre, edad, dorsal, minutos, goles, asistencias, tarjetas_amarillas, tarjetas_rojas, equipo_id))
            
            db_connection.commit()
            print(f"Jugador guardado: {nombre} (Dorsal: {dorsal})")
        except Exception as e:
            print(f"Error al procesar jugador: {e}")

    if jugadores_encontrados:
        # Solo extraer los datos del estadio si hemos encontrado jugadores
        try:
            # Intentar obtener el nombre del estadio desde la estructura correcta
            estadio_element = driver.find_element(By.XPATH, "//div[@class='heading__info']/div[1]")
            estadio_texto = estadio_element.text.strip()

            # Extraer solo el nombre del estadio (antes del paréntesis si existe)
            estadio = estadio_texto.replace("Stadium:", "").strip()
            if "(" in estadio:
                estadio = estadio.split("(")[0].strip()
        except:
            estadio = "Desconocido"  # Si no se encuentra, asignar "Desconocido"

        # Definir el país manualmente (España)
        pais = "España"

        # Actualizar la base de datos con los valores obtenidos
        update_query = """
            UPDATE equipos
            SET estadio = %s, pais = %s, flashscore_id = %s
            WHERE id = %s
        """
        cursor.execute(update_query, (estadio, pais, flashscore_id, equipo_id))
        db_connection.commit()

        print(f"Estadio: {estadio}, País: {pais}, Flashscore ID: {flashscore_id}")
    else:
        print(f"No se encontraron jugadores para el equipo con URL: {url}")

# Cerrar la conexión a la base de datos y el navegador
cursor.close()
db_connection.close()
driver.quit()
print("Conexión cerrada y navegador cerrado.")
