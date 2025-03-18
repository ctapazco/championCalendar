# Champion Calendar

Este proyecto es una aplicación PHP que utiliza Twig para el sistema de plantillas y Firebase JWT para la autenticación basada en tokens. También incluye web scraping y una estructura modular con controladores, modelos y rutas.

## Tecnologías utilizadas
- **PHP**: Lenguaje de backend principal.
- **Twig**: Motor de plantillas para la presentación.
- **Firebase JWT**: Manejo de autenticación con JSON Web Tokens.
- **vlucas/phpdotenv**: Gestión de variables de entorno.
- **Apache**: Servidor web recomendado.
- **Bootstrap**: Para estilos y diseño responsivo.
- **Chromedriver & Selenium**: Para web scraping.

## Estructura del proyecto

```
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
```

## Instalación y ejecución

### Configuración del entorno
Renombrar el archivo `.env.example` a `.env` y configurar las variables necesarias:
```sh
cp .env.example .env
```

Editar el archivo `.env` con los valores adecuados:
```
APP_ENV=local
APP_DEBUG=true
JWT_SECRET=your_secret_key_here
```

### Instalación de dependencias

Ejecutar el siguiente comando para instalar las dependencias del proyecto:
```sh
composer install
```

### Ejecución del proyecto

Si el servidor Apache está corriendo y el VirtualHost está configurado correctamente, puedes acceder al proyecto en tu navegador en:
```
http://www.championcalendar.local
```

Si deseas usar un servidor PHP embebido para pruebas, usa:
```sh
php -S localhost:8000 -t public/
```

Ahora puedes empezar a trabajar con Champion Calendar.

## Código relevante

### Creación de los controladores
```php
$equipoController = new EquipoController($pdo);
$jugadorController = new JugadorController($pdo);
$encuentroController = new EncuentroController($pdo);
$usuarioController = new UsuarioController($pdo);
```
Aquí se crean instancias de los controladores, pasándoles un objeto `$pdo`, que representa la conexión a la base de datos.

### Funciones de autenticación
#### Verificación de sesión (para acceso web)
```php
function verificarSesion() {
    if (!isset($_SESSION['usuario'])) {
        header("Location: /usuarios/login");
        exit;
    }
}
```
Si el usuario no ha iniciado sesión, lo redirige a la página de login.

#### Verificación de Token JWT (para acceso API)
```php
function verificarToken() {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

    if (!$token || !str_starts_with($token, "Bearer ")) {
        http_response_code(401);
        echo json_encode(["error" => "Acceso no autorizado"]);
        exit;
    }

    $token = str_replace("Bearer ", "", $token);

    try {
        $decoded = JWT::decode($token, new Key("Ctapasco290692", 'HS256'));
        $_SESSION['usuario'] = (array) $decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Token inválido o expirado"]);
        exit;
    }
}
```

### Rutas de la API
#### Equipos
```php
route('GET', '/api/equipos', fn() => $equipoController->listarEquiposApi());
route('GET', '/api/equipos/([0-9]+)', fn($id) => $equipoController->obtenerEquipoApi($id));
route('POST', '/api/equipos', fn() => verificarToken() && $equipoController->crearEquipoApi());
route('PUT', '/api/equipos/([0-9]+)', fn($id) => verificarToken() && $equipoController->editarEquipoApi($id));
route('DELETE', '/api/equipos/([0-9]+)', fn($id) => verificarToken() && $equipoController->eliminarEquipoApi($id));
```
- **GET /api/equipos:** Devuelve una lista de equipos en JSON.
- **POST /api/equipos:** Crea un equipo (requiere autenticación con JWT).
- **PUT /api/equipos/{id}:** Modifica un equipo (requiere JWT).
- **DELETE /api/equipos/{id}:** Elimina un equipo (requiere JWT).

### Rutas de equipos en la web
```php
route('GET', '/equipos', fn() => $equipoController->listarEquiposWeb());
route('GET', '/equipos/([0-9]+)', fn($id) => $equipoController->obtenerEquipo($id));
route('GET', '/equipos/crear', fn() => $equipoController->mostrarFormularioCrearEquipo());
route('POST', '/equipos/crear', fn() => $equipoController->crearEquipo());
route('GET', '/equipos/([0-9]+)/editar', fn($id) => $equipoController->editarEquipo($id));
route('POST', '/equipos/([0-9]+)/editar', fn($id) => $equipoController->editarEquipo($id));
route('GET', '/equipos/([0-9]+)/eliminar', fn($id) => $equipoController->eliminarEquipo($id));
```
- **listarEquiposWeb():** Lista los equipos en la web.
- **obtenerEquipo($id):** Muestra los detalles de un equipo.
- **mostrarFormularioCrearEquipo():** Muestra el formulario de creación.
- **crearEquipo():** Procesa el formulario para crear un equipo.
- **editarEquipo($id):** Permite modificar un equipo.
- **eliminarEquipo($id):** Elimina un equipo.


## Relación entre `jugadores` y `equipos`

### 🔗 Tipo de Relación: **Muchos a Uno (N:1)**

### 📖 Explicación:
- Cada jugador pertenece a **un solo equipo**. Esto se establece mediante la clave foránea `equipo_id` en la tabla `jugadores`, que referencia la columna `id` en la tabla `equipos`.
- Un equipo, en cambio, puede tener **muchos jugadores** asociados.

### 🔢 **Cardinalidad:**
- **Un equipo** puede tener **muchos jugadores** (**1:N**).
- **Un jugador** pertenece a **un solo equipo** (**N:1**).

### ⚙️ **Implementación en SQL:**
```sql
CREATE TABLE `jugadores` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `edad` INT NOT NULL,
  `dorsal` INT NOT NULL,
  `minutos_jugados` INT DEFAULT '0',
  `goles` INT DEFAULT '0',
  `asistencias` INT DEFAULT '0',
  `tarjetas_amarillas` INT DEFAULT '0',
  `tarjetas_rojas` INT DEFAULT '0',
  `equipo_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_dorsal` (`dorsal`,`equipo_id`),
  KEY `equipo_id` (`equipo_id`),
  CONSTRAINT `jugadores_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
```

## Relación entre `usuarios` y otras tablas

### 📖 Explicación:
Actualmente, la tabla `usuarios` no está relacionada directamente con `jugadores` o `equipos`. Sin embargo, podría implementarse una relación para:
- Asignar un usuario como administrador de un equipo.
- Permitir que un usuario sea un jugador registrado en la plataforma.

### 📌 **Posibles mejoras en la base de datos**
1. **Relacionar `usuarios` con `equipos`**:
   - Agregar una tabla intermedia `usuarios_equipos` para asociar usuarios con equipos.
2. **Permitir que los usuarios sean jugadores**:
   - Agregar una clave foránea `usuario_id` en `jugadores` para vincular jugadores a usuarios registrados.
3. **Registro de estadísticas históricas**:
   - Crear una tabla `estadisticas_jugadores` para almacenar datos de cada temporada.
4. **Sistema de roles**:
   - Agregar un campo `rol` en `usuarios` para definir permisos (admin, jugador, espectador, etc.).

Con estas mejoras, la base de datos sería más flexible y escalable para futuras funcionalidades. 🚀


