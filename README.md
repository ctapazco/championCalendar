Champion Calendar

Este proyecto es una aplicación PHP que utiliza Twig para el sistema de plantillas y Firebase JWT para la autenticación basada en tokens. También incluye web scraping y una estructura modular con controladores, modelos y rutas.

Tecnologías utilizadas

PHP: Lenguaje de backend principal.

Twig: Motor de plantillas para la presentación.

Firebase JWT: Manejo de autenticación con JSON Web Tokens.

vlucas/phpdotenv: Gestión de variables de entorno.

Apache: Servidor web recomendado.

Bootstrap: Para estilos y diseño responsivo.

Chromedriver & Selenium: Para web scraping.

Estructura del proyecto

championCalendar-main/
│── public/               # Directorio público del proyecto
│   ├── imagenes/        # Recursos gráficos
│   ├── index.php        # Punto de entrada principal
│   ├── .htaccess        # Configuración de reglas de Apache
│── src/                 # Código fuente principal
│   ├── config/          # Configuración del proyecto
│   │   ├── database.php # Configuración de la base de datos
│   │   ├── view.php     # Configuración de vistas
│   ├── controllers/     # Controladores de la aplicación
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   ├── EncuentroController.php
│   │   ├── EquipoController.php
│   │   ├── JugadorController.php
│   │   ├── UsuarioController.php
│   │   ├── homecontroller.php
│   ├── models/          # Modelos de datos
│   │   ├── Encuentros.php
│   │   ├── Equipo.php
│   │   ├── Jugadores.php
│   │   ├── Usuarios.php
│   ├── routes/          # Definición de rutas
│   │   ├── router.php
│   │   ├── routes.php
│   ├── views/           # Plantillas Twig
│   │   ├── crearEquipo.twig
│   │   ├── detalleEquipo.twig
│   │   ├── editarEquipo.twig
│   │   ├── encuentros.twig
│   │   ├── equipos.twig
│   │   ├── index.twig
│   │   ├── jugadores.twig
│   │   ├── loginUsuario.twig
│   │   ├── panelControl.twig
│   │   ├── registrarUsuario.twig
│── scripts/             # Scripts para web scraping
│   ├── scraping.py      # Script de scraping en Python
│   ├── scrapingEquipo.py # Script de scraping para equipos
│── composer.json        # Dependencias del proyecto
│── .env.example         # Archivo de configuración de entorno de ejemplo
│── README.md            # Documentación del proyecto

Código relevante

Ejemplo de una ruta en routes.php

require_once '../src/controllers/EquipoController.php';

$router->get('/equipos', 'EquipoController@index');
$router->post('/equipos', 'EquipoController@store');
$router->get('/equipos/{id}', 'EquipoController@show');
$router->put('/equipos/{id}', 'EquipoController@update');
$router->delete('/equipos/{id}', 'EquipoController@delete');

Ejemplo de un controlador EquipoController.php

class EquipoController {
    public function index() {
        $equipos = Equipo::getAll();
        echo View::render('equipos.twig', ['equipos' => $equipos]);
    }
    
}


Ejemplo de modelo Equipo.php

class Equipo {
    public static function getAll() {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM equipos");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}

Configuración y ejecución

Configuración del entorno

Renombrar el archivo .env.example a .env y configurar las variables necesarias:

cp .env.example .env

Editar el archivo .env con los valores adecuados:

APP_ENV=local
APP_DEBUG=true
JWT_SECRET=your_secret_key_here

Instalación de dependencias

Ejecutar el siguiente comando para instalar las dependencias del proyecto:

composer install

Ejecución del proyecto

Si el servidor Apache está corriendo y el VirtualHost está configurado correctamente, puedes acceder al proyecto en tu navegador en:

http://www.champion-calendar.local

Si deseas usar un servidor PHP embebido para pruebas, usa:

php -S localhost:8000 -t public/

Ahora puedes empezar a trabajar con Champion Calendar.

