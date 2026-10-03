# Instrucciones de Despliegue en InfinityFree

## 1. Configuración del archivo .env

Crea un archivo `.env` en la raíz del proyecto con el siguiente contenido:

```env
APP_NAME="Sistema Invita"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://invit.infinityfreeapp.com

APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_ES

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=sql113.infinityfree.com
DB_PORT=3306
DB_DATABASE=if0_43004560_invita
DB_USERNAME=if0_43004560
DB_PASSWORD=dScyX684ebdbAEB

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
VITE_APP_URL="${APP_URL}"
```

## 2. Pasos para el despliegue

### En tu máquina local:

1. **Instalar dependencias de producción:**
   ```bash
   composer install --optimize-autoloader --no-dev
   npm ci
   npm run build
   ```

2. **Generar la clave de la aplicación:**
   ```bash
   php artisan key:generate
   ```

3. **Ejecutar migraciones (localmente para crear el schema):**
   ```bash
   php artisan migrate
   ```

4. **Crear archivo .env con la configuración de producción** (ver arriba)

5. **Limpiar y optimizar caché:**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan cache:clear
   
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### En InfinityFree:

1. **Subir archivos por FTP:**
   - Sube todo el proyecto EXCEPTO:
     - `node_modules/`
     - `.git/`
     - `.env.example`
     - `tests/`
     - `storage/logs/*.log`
     - `.editorconfig`
     - `phpunit.xml`
     - `vite.config.js`
     - `package*.json`
     - `composer.json`
     - `composer.lock`
     - `README.md`
     - `DEPLOY_INSTRUCTIONS.md`

2. **Estructura en el servidor:**
   - El contenido de la carpeta `public/` debe ir en `public_html/` (o `htdocs/`)
   - El resto del proyecto va en una carpeta fuera de `public_html/` (ej: `laravel_app/`)
   - **IMPORTANTE:** Modifica `public_html/index.php` para que apunte a la carpeta correcta:
     ```php
     require __DIR__.'/../laravel_app/vendor/autoload.php';
     $app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
     ```

3. **Configurar base de datos:**
   - En el panel de InfinityFree, crea la base de datos `if0_43004560_invita`
   - Ejecuta las migraciones (puedes usar un archivo SQL exportado localmente o usar la consola SSH si está disponible)

4. **Permisos:**
   - `storage/` y `bootstrap/cache/` deben tener permisos de escritura (755 o 777)

5. **Configurar SSL:**
   - InfinityFree ofrece SSL gratuito. Actívalo en el panel de control.

## 3. Archivos importantes a revisar

### `public/.htaccess` - Ya configurado correctamente para Laravel

### `config/app.php` - Verificar:
- `'env' => env('APP_ENV', 'production'),`
- `'debug' => (bool) env('APP_DEBUG', false),`
- `'url' => env('APP_URL', 'https://invit.infinityfreeapp.com'),`

### `config/database.php` - Ya configurado para MySQL

### `config/filesystems.php` - Para producción, considerar usar `local` o configurar `s3` si necesitas almacenamiento persistente

## 4. Comandos post-despliegue (ejecutar en el servidor si tienes SSH)

```bash
cd laravel_app
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

## 5. Consideraciones importantes

1. **APP_KEY:** Debe ser la misma en local y producción para que funcionen sesiones, cookies encriptadas, etc.

2. **Storage:** En hosting compartido, `storage:link` puede no funcionar. Alternativa: crear symlink manualmente o configurar `filesystems.php` para usar carpeta pública directamente.

3. **Queue workers:** En hosting compartido no tendrás acceso a supervisor. Usa `QUEUE_CONNECTION=database` y ejecuta `php artisan queue:work` via cron job cada minuto.

4. **Programador de tareas (Scheduler):** Configura un cron job que ejecute cada minuto:
   ```
   * * * * * cd /home/tuusuario/laravel_app && php artisan schedule:run >> /dev/null 2>&1
   ```

5. **Vite/Asset building:** Los assets ya vienen compilados en `public/build/`. No necesitas Node.js en el servidor.

## 6. Verificación post-despliegue

- [ ] Sitio carga correctamente en https://invit.infinityfreeapp.com
- [ ] Base de datos conecta correctamente
- [ ] Login/registro funciona
- [ ] Assets (CSS/JS) cargan sin errores 404
- [ ] Formularios envían datos correctamente
- [ ] Logs se escriben en `storage/logs/laravel.log`

## 7. Seguridad adicional

- Asegúrate de que `.env` NO sea accesible vía web
- Deshabilita `APP_DEBUG` en producción
- Usa HTTPS siempre
- Considera añadir headers de seguridad en `.htaccess`