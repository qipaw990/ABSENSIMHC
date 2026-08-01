#!/bin/bash
# ==============================================================================
#  SCRIPT INSTALASI OTOMATIS — SISTEM ABSENSI QR CODE & NOTIFIKASI WA
#  Versi: 2.0 (Commercial Edition)
# ==============================================================================

set -e

# Warna Terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

clear
echo -e "${CYAN}====================================================================${NC}"
echo -e "${GREEN}   🚀 SELAMAT DATANG DI INSTALLER SISTEM ABSENSI QR CODE & WA      ${NC}"
echo -e "${CYAN}====================================================================${NC}"
echo -e "${YELLOW} Script ini akan mengonfigurasi dan menginstall aplikasi secara otomatis.${NC}"
echo ""

# 1. Cek Docker & Docker Compose
echo -e "${BLUE}[1/6] Memeriksa kebutuhan sistem (Docker & Docker Compose)...${NC}"

if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Docker belum terinstall! Silakan install Docker terlebih dahulu.${NC}"
    echo "Instalasi Docker: curl -fsSL https://get.docker.com | sh"
    exit 1
fi

if ! docker compose version &> /dev/null && ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}❌ Docker Compose belum terinstall!${NC}"
    exit 1
fi

echo -e "${GREEN}  ✓ Docker & Docker Compose siap!${NC}"
echo ""

# 2. Input Konfigurasi Interaktif dari Pembeli
echo -e "${BLUE}[2/6] Konfigurasi Aplikasi (Tekan Enter untuk menggunakan default)...${NC}"
echo ""

read -p "📌 Nama Sekolah [SMK MUTHIA HARAPAN CICALENGKA]: " INPUT_NAMA_SEKOLAH
NAMA_SEKOLAH=${INPUT_NAMA_SEKOLAH:-"SMK MUTHIA HARAPAN CICALENGKA"}

read -p "🌐 URL / Domain / IP Aplikasi (misal: https://absensi.sekolah.sch.id atau http://192.168.1.100:8585) [http://localhost:8585]: " INPUT_APP_URL
APP_URL=${INPUT_APP_URL:-"http://localhost:8585"}

read -p "🔌 Port Web App [8585]: " INPUT_PORT_APP
PORT_APP=${INPUT_PORT_APP:-"8585"}

read -p "🔌 Port phpMyAdmin [8586]: " INPUT_PORT_PMA
PORT_PMA=${INPUT_PORT_PMA:-"8586"}

read -p "👤 Email Super Admin [admin@sekolah.com]: " INPUT_ADMIN_EMAIL
ADMIN_EMAIL=${INPUT_ADMIN_EMAIL:-"admin@sekolah.com"}

read -p "🔑 Password Super Admin [AdminAbsensi2025!]: " INPUT_ADMIN_PASS
ADMIN_PASS=${INPUT_ADMIN_PASS:-"AdminAbsensi2025!"}

echo ""
echo -e "${YELLOW}--- Ringkasan Konfigurasi ---${NC}"
echo -e "  Nama Sekolah : ${GREEN}${NAMA_SEKOLAH}${NC}"
echo -e "  URL Aplikasi : ${GREEN}${APP_URL}${NC}"
echo -e "  Port App     : ${GREEN}${PORT_APP}${NC}"
echo -e "  Email Admin  : ${GREEN}${ADMIN_EMAIL}${NC}"
echo -e "-----------------------------"
read -p "Lanjutkan instalasi? (y/n) [y]: " CONFIRM
CONFIRM=${CONFIRM:-"y"}

if [[ "$CONFIRM" != "y" && "$CONFIRM" != "Y" ]]; then
    echo -e "${RED}Instalasi dibatalkan.${NC}"
    exit 0
fi

echo ""
# 3. Generate File .env
echo -e "${BLUE}[3/6] Membuat konfigurasi environment (.env)...${NC}"

cat <<EOF > .env
APP_NAME="Sistem Absensi MHC"
APP_ENV=production
APP_KEY=base64:nLFw8vkkqzZtbYb+jb9NW7r3xM4PgW1TAEcgc2MFoEg=
APP_DEBUG=false
APP_URL="${APP_URL}"
NAMA_SEKOLAH="${NAMA_SEKOLAH}"

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=absensi_mhc
DB_USERNAME=absensi_user
DB_PASSWORD=AbsensiMHC@2025

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
CACHE_STORE=database
LOG_CHANNEL=stack
LOG_LEVEL=error

FORCE_HTTPS=false
EOF

echo -e "${GREEN}  ✓ File .env berhasil dibuat!${NC}"
echo ""

# 4. Generate / Update docker-compose.yml
echo -e "${BLUE}[4/6] Mengonfigurasi Docker Compose...${NC}"

cat <<EOF > docker-compose.yml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: absensi_app
    restart: unless-stopped
    ports:
      - "${PORT_APP}:80"
    environment:
      APP_NAME: "Sistem Absensi MHC"
      APP_ENV: production
      APP_DEBUG: "false"
      APP_KEY: "base64:nLFw8vkkqzZtbYb+jb9NW7r3xM4PgW1TAEcgc2MFoEg="
      APP_URL: "${APP_URL}"
      APP_TIMEZONE: Asia/Jakarta
      APP_LOCALE: id
      APP_FALLBACK_LOCALE: id
      NAMA_SEKOLAH: "${NAMA_SEKOLAH}"

      DB_CONNECTION: mysql
      DB_HOST: db
      DB_PORT: 3306
      DB_DATABASE: absensi_mhc
      DB_USERNAME: absensi_user
      DB_PASSWORD: "AbsensiMHC@2025"

      SESSION_DRIVER: database
      CACHE_STORE: database
      QUEUE_CONNECTION: sync

      LOG_CHANNEL: stack
      LOG_LEVEL: error
      FILESYSTEM_DISK: local
    volumes:
      - app_storage:/var/www/html/storage
      - app_logs:/var/www/html/storage/logs
    depends_on:
      db:
        condition: service_healthy
    networks:
      - absensi_net

  db:
    image: mysql:8.0
    container_name: absensi_db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: absensi_mhc
      MYSQL_USER: absensi_user
      MYSQL_PASSWORD: "AbsensiMHC@2025"
      MYSQL_ROOT_PASSWORD: "RootMHC@2025"
    volumes:
      - db_data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-pRootMHC@2025"]
      interval: 10s
      timeout: 5s
      retries: 5
    networks:
      - absensi_net

  phpmyadmin:
    image: phpmyadmin:latest
    container_name: absensi_phpmyadmin
    restart: unless-stopped
    ports:
      - "${PORT_PMA}:80"
    environment:
      PMA_HOST: db
      PMA_USER: root
      PMA_PASSWORD: "RootMHC@2025"
      UPLOAD_LIMIT: 50M
    depends_on:
      - db
    networks:
      - absensi_net

volumes:
  db_data:
    driver: local
  app_storage:
    driver: local
  app_logs:
    driver: local

networks:
  absensi_net:
    driver: bridge
EOF

echo -e "${GREEN}  ✓ File docker-compose.yml berhasil dibuat!${NC}"
echo ""

# 5. Jalankan Docker Container
echo -e "${BLUE}[5/6] Membangun dan menjalankan container (proses ini butuh beberapa menit)...${NC}"
docker compose down --remove-orphans 2>/dev/null || true
docker compose up -d --build

echo "Menunggu database MySQL siap (15 detik)..."
sleep 15

# 6. Inisialisasi Database & Seeder
echo -e "${BLUE}[6/6] Menyiapkan database dan akun admin...${NC}"
docker exec absensi_app php artisan key:generate --force
docker exec absensi_app php artisan migrate:fresh --seed --force
docker exec absensi_app php artisan storage:link --force 2>/dev/null || true

# Buat / Update Akun Admin Kustom
docker exec absensi_app php artisan tinker --execute="
\$user = \App\Models\User::firstOrCreate(['email' => '${ADMIN_EMAIL}'], [
    'name' => 'Super Admin',
    'password' => \Illuminate\Support\Facades\Hash::make('${ADMIN_PASS}'),
]);
\$user->update(['password' => \Illuminate\Support\Facades\Hash::make('${ADMIN_PASS}')]);
if (!\$user->hasRole('super_admin')) {
    \$user->assignRole('super_admin');
}
"

# Clear Cache
docker exec absensi_app php artisan config:clear
docker exec absensi_app php artisan view:clear
docker exec absensi_app php artisan route:clear

echo ""
echo -e "${CYAN}====================================================================${NC}"
echo -e "${GREEN}   🎉 INSTALASI BERHASIL DISLESAIKAN!                              ${NC}"
echo -e "${CYAN}====================================================================${NC}"
echo -e " 🌐 URL Aplikasi   : ${YELLOW}${APP_URL}${NC}"
echo -e " 🔑 Email Admin    : ${YELLOW}${ADMIN_EMAIL}${NC}"
echo -e " 🔒 Password Admin : ${YELLOW}${ADMIN_PASS}${NC}"
echo -e " 🗄️ phpMyAdmin     : ${YELLOW}http://IP_SERVER:${PORT_PMA}${NC}"
echo -e "${CYAN}====================================================================${NC}"
echo -e "${GREEN} Silakan buka URL di atas pada browser untuk mulai menggunakan!${NC}"
echo ""
