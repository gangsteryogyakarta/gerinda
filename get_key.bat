@echo off
cd /d "%~dp0"
echo ==========================================
echo  AMBIL PUBLIC KEY SERVER
echo ==========================================
echo.
echo Sedang menghubungkan ke server...
echo (Masukkan Password: 4pp5GERINDRA jika diminta)
echo.

plink -ssh -t root@gerindradiy.com "if [ ! -f /home/deploy/.ssh/id_ed25519 ]; then ssh-keygen -t ed25519 -N '' -f /home/deploy/.ssh/id_ed25519; fi && chown deploy:deploy /home/deploy/.ssh/id_ed25519* && echo '--------------------------' && cat /home/deploy/.ssh/id_ed25519.pub && echo '--------------------------'"

echo.
echo Silakan COPY kode di antara garis putus-putus di atas.
echo Itu adalah PUBLIC KEY server Anda.
echo.
pause
