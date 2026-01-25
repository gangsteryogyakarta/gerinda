@echo off
cd /d "%~dp0"
echo ==========================================
echo  SETUP VPS GERINDRA - IDCloudHost
echo ==========================================
echo.
echo [1/2] Uploading script...
echo (Masukkan Password VPS: 4pp5.D!Y saat diminta)
echo.
pscp -scp "scripts\provision.sh" root@27.112.78.114:/root/

if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Upload gagal! File script tidak ditemukan atau Password salah.
    echo Pastikan file ada di: %~dp0scripts\provision.sh
    pause
    exit /b
)

echo.
echo [2/2] Menjalankan instalasi...
echo (Masukkan Password lagi jika diminta)
echo.
plink -ssh -t root@27.112.78.114 "chmod +x /root/provision.sh && /root/provision.sh"

echo.
echo ==========================================
echo  SELESAI!
echo ==========================================
pause
