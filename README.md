# Login-expresiif
# Plataforma de autenticación en PHP

Este proyecto migra la antigua API de Express a un stack completamente basado en PHP.
Incluye autenticación con sesiones, gestión de usuarios y roles, control de banners y
un tablero de noticias generadas de manera simulada.

## Requisitos

- PHP 8.2 o superior con las extensiones `pdo_mysql`, `openssl` y `json` habilitadas.
- Servidor MySQL 5.7+ o MariaDB 10.2+ (necesario para columnas JSON) accesible con una base de datos vacía para la aplicación.

## Configuración y puesta en marcha

1. Define las variables de entorno antes de iniciar el servidor (puedes exportarlas o añadirlas a tu `.bashrc`):

   ```bash
   export MYSQL_HOST=127.0.0.1
   export MYSQL_PORT=3306
   export MYSQL_DATABASE=login_expresiif
   export MYSQL_USER=usuario
   export MYSQL_PASSWORD=contraseña-segura
   export APP_URL="http://localhost:8000"
   export MAIL_FROM_ADDRESS="no-reply@example.com"
   export MAIL_FROM_NAME="Calidad de Software"
   ```

2. Crea la base de datos configurada en `MYSQL_DATABASE` si aún no existe.

3. Ejecuta el servidor embebido de PHP:

   ```bash
   php -S localhost:8000 -t public
   ```

Al primer arranque se ejecutan las migraciones en MySQL y se crea un usuario administrador verificado por defecto:

- **Correo:** `admin@example.com`
- **Contraseña:** `Admin123!`

## Características principales

- Registro e inicio de sesión con protección CSRF.
- Confirmación de correo electrónico obligatoria antes de acceder.
- Captcha aritmético ligero y limitación de intentos para evitar ataques automatizados en el login.
- Panel de perfil para actualizar nombre de usuario y solicitar cambio de contraseña.
- Cambios de contraseña protegidos mediante confirmación vía correo electrónico.
- Administración de roles de usuario (Usuario, Moderador, Administrador).
- Gestión de banners con carga de imágenes y activación/desactivación.
- Dashboard de noticias con generación de contenido simulado.
- Reporte de visitas por página.

## Estructura del proyecto

La aplicación actual está organizada en torno a un núcleo sencillo ubicado en `app/` y
un punto de entrada público servido por PHP embebido.

```
app/
  Controllers/            # Controladores HTTP agrupados por responsabilidad
  Models/                 # Repositorios PDO que encapsulan consultas MySQL
  Support/
    Captcha.php           # Utilidades de generación y validación de captcha
    FlashBag.php          # Gestión de mensajes flash en sesión
    Mailer.php            # Registro de correos en disco
    PasswordHelper.php    # Validación de contraseñas y hashing
    helpers.php           # Funciones auxiliares compartidas
  Views/
    layouts/              # Plantillas base reutilizables
    auth/                 # Formularios de autenticación y confirmación
    pages/                # Vistas para dashboard, reportes y perfil
  Database.php            # Inicialización de PDO y migraciones
  Router.php              # Enrutador minimalista basado en rutas HTTP
  bootstrap.php           # Arranque de sesión, configuración y contenedores
  config.php              # Lectura de variables de entorno y valores por defecto
public/
  assets/
    css/                  # Hojas de estilo de la interfaz
  index.php               # Punto de entrada que delega en el router
  uploads/                # Destino público para archivos cargados
storage/
  mail/                   # Copias de correos enviados (texto plano)
  uploads/                # Zona privada para validaciones previas al mover archivos
node_modules/             # Dependencias heredadas de la versión Express (no usadas)
```

> 💡 Puedes eliminar `node_modules/` si no necesitas mantener el historial de la
> antigua implementación en Node.js.

### Estructura recomendada a futuro

Para seguir escalando el proyecto conviene adoptar una distribución por capas que
facilite las pruebas automatizadas y la reutilización de componentes. Una opción
compatible con el código actual sería:

```
app/
  Http/
    Controllers/          # Controladores HTTP
    Middleware/           # Validaciones previas/posteriores al controlador
    Requests/             # Objetos de validación de formularios
  Domain/
    Models/               # Entidades de dominio (User, Banner, News, Visit)
    Services/             # Casos de uso (AutenticarUsuario, GenerarReporteVisitas)
    Repositories/         # Interfaces de persistencia
  Infrastructure/
    Persistence/
      Pdo/
        Repositories/     # Implementaciones concretas contra MySQL
    Mail/
      FileSystemMailer.php
    Captcha/
      ArithmeticCaptcha.php
  Support/
    Config.php            # Gestión centralizada de configuración
    Container.php         # Inyección de dependencias sencilla
bootstrap/
  app.php                 # Bootstrap del framework casero
config/
  database.php            # Conexiones por entorno
  mail.php
database/
  migrations/             # Scripts versionados
  seeders/                # Datos de arranque
public/
  index.php
resources/
  views/                  # Plantillas renderizables
storage/
  mail/
  uploads/
tests/
  Feature/
  Unit/
```

Esta propuesta separa claramente la capa HTTP del dominio y de la infraestructura,
permitiendo sustituir MySQL por otra base de datos o el captcha aritmético por una
API externa sin modificar los controladores. También prepara el terreno para añadir
pruebas unitarias y de integración al contar con servicios desacoplados y
configuración aislada.

## Notas

- Los correos de verificación y confirmación de contraseña se almacenan en `storage/mail` para facilitar las pruebas.
- Las imágenes subidas se almacenan en `public/uploads`.
- Asegúrate de que el usuario del servidor web tenga permisos de escritura sobre `storage/` y `public/uploads/`.
- Para restablecer la información inicial borra las tablas en la base de datos MySQL y reinicia la aplicación.
- El inicio de sesión bloquea direcciones IP y correos durante 10 minutos tras 5 intentos fallidos consecutivos