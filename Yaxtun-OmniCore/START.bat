@echo off
chcp 65001 >nul
color 0A
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║           🌟 YAXTUN-OmniCore - Sistema de Reparación de Dispositivos         ║
echo ║                    Iniciando ambiente completamente aislado                   ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

REM Verificar si Docker está disponible
echo [*] Verificando Docker...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker no encontrado
    echo.
    echo Descarga Docker Desktop desde: https://www.docker.com/products/docker-desktop
    echo Una vez instalado, ejecuta nuevamente este archivo.
    echo.
    pause
    exit /b 1
)

echo ✅ Docker disponible

REM Obtener ruta actual
set CURRENT_PATH=%~dp0
cd /d %CURRENT_PATH%

REM Verificar docker-compose.yml
if not exist docker-compose.yml (
    echo ❌ Error: docker-compose.yml no encontrado
    echo Asegúrate de estar en el directorio correcto
    pause
    exit /b 1
)

echo.
echo [*] Construyendo contenedor Docker...
echo Esto puede tomar unos minutos la primera vez...
echo.

docker-compose down >nul 2>&1
docker-compose up -d

if %errorlevel% neq 0 (
    echo ❌ Error iniciando el sistema
    echo.
    echo Intenta:
    echo  1. Asegúrate que Docker Desktop está ejecutándose
    echo  2. Verifica que los puertos 3000, 5000, 5037 estén disponibles
    echo  3. Intenta: docker-compose logs
    echo.
    pause
    exit /b 1
)

echo.
echo ✅ Sistema iniciado correctamente
echo.
echo Esperando que los servicios estén listos...
timeout /t 8 /nobreak
echo.
echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🎉 ¡YAXTUN-OmniCore INICIADO!                              ║
echo ╠══════════════════════════════════════════════════════════════════════════════╣
echo ║  🌐 PORTAL WEB:      http://localhost:3000                                    ║
echo ║  📡 API REST:        http://localhost:5000                                    ║
echo ║  🔌 ADB SERVER:      localhost:5037                                           ║
echo ║                                                                                ║
echo ║  ✨ El portal se abrirá automáticamente en tu navegador...                   ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.
echo Presiona cualquier tecla para continuar...
pause >nul

REM Abrir navegador
start http://localhost:3000

echo.
echo 💡 COMANDOS ÚTILES:
echo.
echo    Ver estado:          docker-compose ps
echo    Ver logs:            docker-compose logs -f
echo    Detener:             docker-compose down
echo    Reiniciar:           docker-compose restart
echo.
echo    Para conectar dispositivos via USB, asegúrate que Docker Desktop
echo    tenga acceso a los dispositivos USB.
echo.
pause
