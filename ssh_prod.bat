@echo off
cd /d "%~dp0"
echo ==========================================
echo  CONNECT TO PRODUCTION SERVER
echo  Host: gerindradiy.com
echo ==========================================
echo.
echo Connecting as root...
echo (Password: 4pp5.D!Y)
echo.

ssh root@gerindradiy.com

echo.
echo Connection closed.
pause
