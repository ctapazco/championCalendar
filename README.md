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

