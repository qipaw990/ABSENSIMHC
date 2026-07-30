@echo off
chcp 65001 > nul
title Deploy - Sistem Absensi MHC

echo.
echo ╔══════════════════════════════════════════════════════╗
echo ║       DEPLOY SISTEM ABSENSI MHC ke CasaOS          ║
echo ╚══════════════════════════════════════════════════════╝
echo.

:: ─── KONFIGURASI ──────────────────────────────────────────
set CASAOS_IP=192.168.0.103
set CASAOS_USER=casaos
set CASAOS_PROJECT_PATH=/DATA/AppData/absensi_qrcode
set GIT_BRANCH=main
:: ──────────────────────────────────────────────────────────

:: Ambil pesan commit dari argumen atau input user
if "%~1"=="" (
    set /p COMMIT_MSG=Masukkan pesan commit: 
) else (
    set COMMIT_MSG=%~1
)

if "%COMMIT_MSG%"=="" set COMMIT_MSG=Update: %date% %time%

echo.
echo [1/4] Menambahkan semua perubahan ke Git...
git add .

echo.
echo [2/4] Commit dengan pesan: "%COMMIT_MSG%"
git commit -m "%COMMIT_MSG%"

echo.
echo [3/4] Push ke GitHub branch [%GIT_BRANCH%]...
git push origin %GIT_BRANCH%

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [ERROR] Gagal push ke GitHub! Periksa koneksi atau credentials Git.
    pause
    exit /b 1
)

echo.
echo [4/4] Trigger deploy di CasaOS via SSH...
echo       Menghubungi %CASAOS_USER%@%CASAOS_IP%...
echo.

ssh %CASAOS_USER%@%CASAOS_IP% "bash %CASAOS_PROJECT_PATH%/deploy-server.sh"

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [WARNING] SSH ke CasaOS gagal atau script tidak ditemukan.
    echo           Jalankan deploy-server.sh secara manual di CasaOS.
    echo.
    echo           Perintah manual:
    echo           ssh %CASAOS_USER%@%CASAOS_IP%
    echo           bash %CASAOS_PROJECT_PATH%/deploy-server.sh
) else (
    echo.
    echo ╔══════════════════════════════════════════════════════╗
    echo ║   DEPLOY BERHASIL!                                  ║
    echo ║   Akses aplikasi: http://%CASAOS_IP%:8585          ║
    echo ║   phpMyAdmin    : http://%CASAOS_IP%:8586          ║
    echo ╚══════════════════════════════════════════════════════╝
)

echo.
pause
