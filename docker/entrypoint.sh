#!/bin/sh

echo "========================================="
echo " Sistem Absensi MHC — Starting Up"
echo "========================================="

cd /var/www/html

# Buat direktori framework & storage yang dibutuhkan Laravel
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

# Perbaiki permission storage & bootstrap/cache
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

# Tunggu MySQL siap
echo "⏳ Menunggu database MySQL..."
while ! nc -z db 3306 2>/dev/null; do
    echo "   MySQL belum siap, tunggu 3 detik..."
    sleep 3
done
sleep 3
echo "✅ Database MySQL sudah siap!"

# Clear cache lama
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Buat symlink storage
echo "📁 Membuat storage link..."
php artisan storage:link --force 2>/dev/null || true

# Jalankan migrasi (--force untuk production)
echo "🗄️  Menjalankan migrasi..."
php artisan migrate --force || true

# Jalankan seeder
echo "🌱 Menjalankan seeder..."
php artisan db:seed --force || true

# Optimasi cache baru
echo "⚡ Mengoptimalkan aplikasi..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Buat direktori log supervisor
mkdir -p /var/log/supervisor /var/log/nginx

echo "✅ Aplikasi siap diakses!"
echo "========================================="

# Jalankan supervisord (PHP-FPM + Nginx)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
