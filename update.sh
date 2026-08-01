#!/bin/bash
# ==============================================================================
#  SCRIPT UPDATE OTOMATIS — SISTEM ABSENSI QR CODE
# ==============================================================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${CYAN}====================================================${NC}"
echo -e "${GREEN}   🔄 MEMULAI PROSES UPDATE SISTEM ABSENSI           ${NC}"
echo -e "${CYAN}====================================================${NC}"

echo -e "${YELLOW}1. Mengunduh pembaruan dari repositori...${NC}"
git pull origin main

echo -e "${YELLOW}2. Membangun ulang container Docker...${NC}"
docker compose up -d --build

echo -e "${YELLOW}3. Menjalankan migrasi database...${NC}"
docker exec absensi_app php artisan migrate --force

echo -e "${YELLOW}4. Membersihkan cache sistem...${NC}"
docker exec absensi_app php artisan config:clear
docker exec absensi_app php artisan view:clear
docker exec absensi_app php artisan route:clear
docker exec absensi_app php artisan cache:clear

echo ""
echo -e "${GREEN}✅ UPDATE BERHASIL DITERAPKAN!${NC}"
echo -e "${CYAN}====================================================${NC}"
