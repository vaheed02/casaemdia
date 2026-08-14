@echo off
title ServiJa - CI4 Dev Server
cd /d "%~dp0"
echo.
echo  ServiJa rodando em:
echo  http://localhost:8080/login
echo.
echo  Demo: cliente@demo.com / prestador@demo.com / admin@demo.com
echo  Senha: demo123
echo.
c:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe spark serve --host localhost --port 8080
pause
