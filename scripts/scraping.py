import time
import mysql.connector
from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from datetime import datetime

# Configuración del WebDriver
driver_path = '/home/ctapasco/chromedriver/chromedriver-linux64/chromedriver'
service = Service(driver_path)
driver = webdriver.Chrome(service=service)

# URL de la liga y equipos
liga_url = 'https://www.flashscore.com/football/spain/laliga/results/'
driver.get(liga_url)

time.sleep(6)  # Esperar que la página cargue

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

# Obtener todos los partidos
events = driver.find_elements(By.CLASS_NAME, 'event__match')

for event in events:
    try:
        team_home = event.find_element(By.CLASS_NAME, 'event__homeParticipant').text.strip()
        team_away = event.find_element(By.CLASS_NAME, 'event__awayParticipant').text.strip()

        cursor.execute("SELECT id FROM equipos WHERE nombre = %s", (team_home,))
        home_team_id = cursor.fetchone()
        if not home_team_id:
            print(f"Equipo local no encontrado en BD: {team_home}")
            continue
        home_team_id = home_team_id[0]

        cursor.execute("SELECT id FROM equipos WHERE nombre = %s", (team_away,))
        away_team_id = cursor.fetchone()
        if not away_team_id:
            print(f"Equipo visitante no encontrado en BD: {team_away}")
            continue
        away_team_id = away_team_id[0]

        score_home = event.find_element(By.CLASS_NAME, 'event__score--home').text.strip()
        score_away = event.find_element(By.CLASS_NAME, 'event__score--away').text.strip()
        match_time = event.find_element(By.CLASS_NAME, 'event__time').text.strip()
        match_date_raw, match_hour = match_time.split()
        match_date_raw = match_date_raw.split("..")[0]
        match_date_int = int(match_date_raw.replace('.', ''))

        try:
            match_hour = time.strptime(match_hour, "%H:%M")
            match_hour = time.strftime("%H:%M:%S", match_hour)
        except ValueError as e:
            print(f"Error al convertir la hora '{match_hour}': {e}")
            continue

        insert_query = """
            INSERT INTO encuentros (equipo_local_id, equipo_visitante_id, fecha, hora, resultado_local, resultado_visitante)
            VALUES (%s, %s, %s, %s, %s, %s)
        """
        cursor.execute(insert_query, (home_team_id, away_team_id, match_date_int, match_hour, score_home, score_away))
        db_connection.commit()
        print("Partido guardado correctamente en la base de datos.")
    except Exception as e:
        print(f"Error al procesar un partido: {e}")

# Obtener todos los equipos y sus URLs
cursor.execute("SELECT id, nombre, url_perfil FROM equipos")
equipos = cursor.fetchall()

for equipo_id, equipo_nombre, equipo_url in equipos:
    try:
        print(f"Procesando equipo: {equipo_nombre}")
        driver.get(equipo_url)
        time.sleep(5)
        
        jugadores = driver.find_elements(By.CLASS_NAME, 'player-name-class')  # Ajustar el selector
        for jugador in jugadores:
            try:
                nombre = jugador.text.strip()
                edad = int(jugador.find_element(By.CLASS_NAME, 'player-age-class').text.strip())
                dorsal = int(jugador.find_element(By.CLASS_NAME, 'player-dorsal-class').text.strip())
                minutos = int(jugador.find_element(By.CLASS_NAME, 'player-minutes-class').text.strip())
                goles = int(jugador.find_element(By.CLASS_NAME, 'player-goals-class').text.strip())
                asistencias = int(jugador.find_element(By.CLASS_NAME, 'player-assists-class').text.strip())
                tarjetas_amarillas = int(jugador.find_element(By.CLASS_NAME, 'player-yellow-cards-class').text.strip())
                tarjetas_rojas = int(jugador.find_element(By.CLASS_NAME, 'player-red-cards-class').text.strip())
                
                cursor.execute("""
                    INSERT INTO jugadores (nombre, edad, dorsal, minutos_jugados, goles, asistencias, tarjetas_amarillas, tarjetas_rojas, equipo_id)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
                    ON DUPLICATE KEY UPDATE 
                        edad=VALUES(edad), dorsal=VALUES(dorsal), minutos_jugados=VALUES(minutos_jugados), 
                        goles=VALUES(goles), asistencias=VALUES(asistencias), tarjetas_amarillas=VALUES(tarjetas_amarillas), tarjetas_rojas=VALUES(tarjetas_rojas)
                """, (nombre, edad, dorsal, minutos, goles, asistencias, tarjetas_amarillas, tarjetas_rojas, equipo_id))
                
                db_connection.commit()
                print(f"Jugador guardado: {nombre}")
            except Exception as e:
                print(f"Error al procesar jugador: {e}")
    except Exception as e:
        print(f"Error al procesar equipo {equipo_nombre}: {e}")

cursor.close()
db_connection.close()
driver.quit()
print("Proceso completado y conexión cerrada.")
