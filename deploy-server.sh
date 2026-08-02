#!/bin/bash
# ================================================================
# deploy-server.sh — Script Deploy di CasaOS
# Sistem Absensi MHC - SMK MUTHIA HARAPAN CICALENGKA
# Jalankan: bash deploy-server.sh
# ================================================================

set -e  # Hentikan jika ada error

# ─── KONFIGURASI ─────────────────────────────────────────────────
PROJECT_DIR="/DATA/AppData/absensi_qrcode"
GIT_BRANCH="main"
APP_URL="http://192.168.0.103:8585"
PMA_URL="http://192.168.0.103:8586"
# ─────────────────────────────────────────────────────────────────

# Warna terminal
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo ""
echo -e "${CYAN}╔══════════════════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║       DEPLOY SISTEM ABSENSI MHC - CasaOS            ║${NC}"
echo -e "${CYAN}╚══════════════════════════════════════════════════════╝${NC}"
echo ""

# ─── STEP 1: Masuk ke direktori project ──────────────────────────
echo -e "${BLUE}[1/6]${NC} Masuk ke direktori project..."
cd "$PROJECT_DIR" || { echo -e "${RED}[ERROR] Direktori $PROJECT_DIR tidak ditemukan!${NC}"; exit 1; }
echo -e "      📁 $PROJECT_DIR"

# ─── STEP 2: Pull kode terbaru dari GitHub ────────────────────────
echo ""
echo -e "${BLUE}[2/6]${NC} Pull kode terbaru dari GitHub..."
git fetch origin
git reset --hard origin/$GIT_BRANCH
git pull origin $GIT_BRANCH
echo -e "      ${GREEN}✓ Kode berhasil diperbarui${NC}"

# ─── STEP 3: Stop container lama ─────────────────────────────────
echo ""
echo -e "${BLUE}[3/6]${NC} Menghentikan container lama..."
docker compose down
echo -e "      ${GREEN}✓ Container dihentikan${NC}"

# ─── STEP 4: Build ulang image Docker ────────────────────────────
echo ""
echo -e "${BLUE}[4/6]${NC} Build ulang Docker image (ini mungkin butuh beberapa menit)..."
docker compose build --no-cache app
echo -e "      ${GREEN}✓ Build selesai${NC}"

# ─── STEP 5: Jalankan semua container ────────────────────────────
echo ""
echo -e "${BLUE}[5/6]${NC} Menjalankan semua container..."
docker compose up -d
echo -e "      ${GREEN}✓ Container berjalan${NC}"

# ─── STEP 6: Jalankan artisan commands di dalam container ─────────
echo ""
echo -e "${BLUE}[6/6]${NC} Menjalankan artisan commands..."

# Tunggu container app siap
echo "      ⏳ Menunggu container siap (15 detik)..."
sleep 15

# Cek apakah container berjalan
if docker ps | grep -q "absensi_app"; then
    echo "      📦 Menjalankan migration..."
    docker exec absensi_app php artisan migrate --force || echo -e "      ${YELLOW}[SKIP] Migration tidak ada perubahan${NC}"

    echo "      🗑️  Clear cache & remove stale cache files..."
    docker exec absensi_app rm -f bootstrap/cache/routes-v7.php bootstrap/cache/config.php
    docker exec absensi_app php artisan config:clear
    docker exec absensi_app php artisan route:clear
    docker exec absensi_app php artisan view:clear
    docker exec absensi_app php artisan cache:clear

    echo -e "      ${GREEN}✓ Artisan commands selesai${NC}"
else
    echo -e "      ${YELLOW}[WARNING] Container belum siap, skip artisan commands${NC}"
    echo "      Jalankan manual: docker exec absensi_app php artisan migrate --force"
fi

# ─── SELESAI ──────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   ✓ DEPLOY BERHASIL!                                ║${NC}"
echo -e "${GREEN}║                                                      ║${NC}"
echo -e "${GREEN}║   Aplikasi  : $APP_URL      ║${NC}"
echo -e "${GREEN}║   phpMyAdmin: $PMA_URL      ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════╝${NC}"
echo ""

# Tampilkan status container
echo -e "${CYAN}Status Container:${NC}"
docker compose ps
echo ""
