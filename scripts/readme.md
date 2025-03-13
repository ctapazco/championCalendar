Champion Calendar
Champion Calendar es una plataforma web para gestionar equipos deportivos, incluyendo sus jugadores, encuentros y otros detalles. El proyecto permite a los usuarios gestionar equipos, crear nuevos equipos, ver detalles de equipos y administrar los encuentros asociados.

Descripción del Proyecto
Este proyecto permite a los usuarios:

Ver la lista de equipos.
Crear, editar y eliminar equipos (solo si están logueados).
Ver detalles de un equipo, incluidos los encuentros y jugadores.
Iniciar sesión y registrarse para acceder a funciones protegidas.
Tecnologías Utilizadas
El proyecto está desarrollado con las siguientes tecnologías:

Frontend:
HTML5: Estructura básica de las páginas.
CSS: Para estilizar el diseño, incluyendo la animación y el uso de gradientes.

Bootstrap 5: Framework CSS utilizado para el diseño responsivo y la estructura visual.

Backend:
PHP: Lenguaje del servidor utilizado para manejar las rutas, la lógica de negocio y la interacción con la base de datos.
MySQL: Base de datos utilizada para almacenar la información de los equipos, jugadores y encuentros.
Otros:
Twig: Motor de plantillas utilizado para renderizar las vistas en el servidor.
JWT (JSON Web Tokens): Para la autenticación de usuarios a través de tokens.

Instalación
Requisitos
PHP 7.4 o superior.
Composer (para la gestión de dependencias).
MySQL 

Pasos para ejecutar el proyecto
Clonar el repositorio:

bash
Copiar
git clone https://github.com/tu-usuario/champion-calendar.git
cd champion-calendar
Instalar las dependencias con Composer:

Si no tienes Composer instalado, puedes descargarlo aquí.

Ejecuta el siguiente comando para instalar las dependencias del proyecto:

bash
Copiar
composer install
Configurar la base de datos:

Crea una base de datos en MySQL llamada champion_calendar y configura el archivo .env para proporcionar los datos de acceso a la base de datos.

Un ejemplo de archivo .env:


Copiar
DB_HOST=localhost
DB_NAME=calendarioFut
DB_USER=ctapasco
DB_PASS=admin123.

Ejecutar el servidor:

Ejecuta el servidor PHP con el siguiente comando (si estás en desarrollo):

bash
Copiar
php -S localhost:8000 -t public
El proyecto estará disponible en http://localhost:8000.

Migraciones de Base de Datos:

Asegúrate de tener las tablas de la base de datos creadas. Si es necesario, puedes importar un archivo SQL para crear las tablas, o crear un script de migración para inicializar la base de datos.

Estructura del Proyecto
La estructura del proyecto es la siguiente:


bash
Copiar
.
├── config
│   ├── database.php           # Configuración de la base de datos.
│   └── view.php               # Configuración de Twig y renderizado de vistas.
├── controllers
│   ├── EquipoController.php   # Lógica para gestionar equipos.
│   ├── JugadorController.php  # Lógica para gestionar jugadores.
│   ├── EncuentroController.php# Lógica para gestionar encuentros.
│   └── UsuarioController.php  # Lógica para gestionar usuarios.
├── models
│   ├── Equipo.php             # Modelo para interactuar con la base de datos de equipos.
│   ├── Jugador.php            # Modelo para interactuar con la base de datos de jugadores.
│   └── Encuentro.php          # Modelo para interactuar con la base de datos de encuentros.
├── public
│   ├── index.php              # Punto de entrada para el servidor PHP.
│   └── assets                 # Archivos estáticos (CSS, JS, imágenes).
├── src
│   ├── routes                 # Rutas para el manejo de URLs.
│   └── templates              # Plantillas de Twig (vistas).
└── .env                        # Archivo de configuración para variables de entorno.



Descripción de los directorios y archivos:
config/database.php: Configuración de la base de datos, con los detalles de conexión.
controllers/: Contiene los controladores PHP que gestionan la lógica de cada entidad (equipos, jugadores, encuentros, usuarios).
models/: Contiene los modelos PHP que interactúan directamente con la base de datos.
public/: Contiene el punto de entrada para el servidor (index.php), así como los archivos estáticos.
src/routes/: Contiene las rutas para el manejo de URLs y los controladores.
src/templates/: Contiene las plantillas Twig para las vistas de la aplicación.
Funcionalidades Principales

Gestión de Equipos:
Ver Equipos: Permite a los usuarios ver una lista de equipos.
Crear Nuevo Equipo: Permite a los usuarios crear nuevos equipos.
Eliminar Equipo: Permite a los usuarios eliminar equipos existentes.
Editar Equipo: Permite a los usuarios editar detalles de los equipos existentes.
Gestión de Usuarios:
Iniciar sesión: Los usuarios pueden iniciar sesión con sus credenciales.
Registro: Los usuarios pueden registrarse si no tienen una cuenta.
Cerrar sesión: Los usuarios pueden cerrar sesión de forma segura.
Seguridad:
Autenticación: Se utiliza JWT para la autenticación de los usuarios, asegurando que solo los usuarios autenticados puedan acceder a ciertas rutas.
Contribuciones
Si deseas contribuir al proyecto, por favor sigue los siguientes pasos:

Haz un fork del repositorio.
Crea una rama para tus cambios (git checkout -b feature/nueva-funcionalidad).
Realiza tus cambios y haz commit (git commit -am 'Añadir nueva funcionalidad').
Push a la rama (git push origin feature/nueva-funcionalidad).
Crea un Pull Request.
Licencia
Este proyecto está bajo la licencia MIT. Consulta el archivo LICENSE para más detalles.

