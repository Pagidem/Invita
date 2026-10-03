#!/bin/bash
# Script de post-despliegue para InfinityFree
# Ejecutar en el servidor después de subir los archivos (si tienes acceso SSH)
# Si no tienes SSH, ejecuta los comandos manualmente

echo "=== Iniciando post-despliegue en InfinityFree ==="

# Directorio base (ajustar según tu estructura)
BASE_DIR="/home/$(whoami)/laravel_app"
PUBLIC_DIR="/home/$(whoami)/public_html"

cd "$BASE_DIR" || exit 1

echo "1. Limpiando caché..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "2. Ejecutando migraciones..."
php artisan migrate --force

echo "3. Optimizando para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "4. Creando enlace simbólico de storage..."
# En hosting compartido, el symlink puede no funcionar.
# Alternativa: copiar archivos manualmente o configurar filesystem público
if php artisan storage:link 2>/dev/null; then
    echo "   Symlink creado exitosamente"
else
    echo "   ADVERTENCIA: No se pudo crear symlink (común en hosting compartido)"
    echo "   Copiando archivos de storage/app/public a public_html/storage..."
    mkdir -p "$PUBLIC_DIR/storage"
    cp -r "$BASE_DIR/storage/app/public/"* "$PUBLIC_DIR/storage/" 2>/dev/null || true
fi

echo "5. Configurando permisos..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod 644 .env

echo "=== Post-despliegue completado ==="
echo ""
echo "IMPORTANTE: Verifica que el archivo .env tenga los valores correctos:"
echo "  - APP_URL=https://invit.infinityfreeapp.com"
echo "  - DB_HOST=sql113.infinityfree.com"
echo "  - DB_DATABASE=if0_43004560_invita"
echo "  - DB_USERNAME=if0_43004560"
echo "  - DB_PASSWORD=dScyX684ebdbAEB"
echo "  - APP_DEBUG=false"
echo "  - APP_ENV=production"