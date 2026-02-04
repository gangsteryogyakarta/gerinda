@echo off
cd /d "%~dp0"
echo ==========================================
echo  GERINDRA EMS - PRODUCTION DEPLOY
echo  Server: gerindradiy.com
echo  User: root
echo ==========================================
echo.
echo [INFO] Script ini akan menghubungkan Anda ke server dan menjalankan perintah deploy.
echo [INFO] Jika diminta password, masukkan: 4pp5GERINDRA
echo.
echo Menghubungkan...
echo.

ssh -t root@gerindradiy.com "sed -i 's|APP_URL=https://ems.gerindra.or.id|APP_URL=https://gerindradiy.com|g' /var/www/gerindra/shared/.env && chown -R deploy:deploy /var/www/gerindra && sudo -u deploy bash -c 'cd /var/www/gerindra && ./deploy.sh main'"

echo.
echo ==========================================
echo  Proses Selesai. Silakan cek pesan sukses di atas.
echo ==========================================
pause
