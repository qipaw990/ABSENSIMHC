#!/bin/bash
# ==============================================================================
#  SCRIPT BACKUP OTOMATIS (DATABASE + DATA UPLOAD)
# ==============================================================================

set -e

BACKUP_DIR="./backups"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
FILENAME="backup_absensi_${TIMESTAMP}.sql"

mkdir -p ${BACKUP_DIR}

echo "📦 Memulai backup database..."
docker exec absensi_db mysqldump -u root -pRootMHC@2025 absensi_mhc > "${BACKUP_DIR}/${FILENAME}"

echo "📦 Mengompres file backup..."
tar -czf "${BACKUP_DIR}/backup_absensi_${TIMESTAMP}.tar.gz" "${BACKUP_DIR}/${FILENAME}"
rm "${BACKUP_DIR}/${FILENAME}"

echo "✅ Backup selesai! File tersimpan di: ${BACKUP_DIR}/backup_absensi_${TIMESTAMP}.tar.gz"
